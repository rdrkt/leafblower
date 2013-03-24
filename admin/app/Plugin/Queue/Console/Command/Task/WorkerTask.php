<?php
App::uses('Set', 'Utility');

abstract class WorkerTask extends QueueShell
{
  public $tubes = array('default');
  public $uses = array('Queue.Job', 'Campaign', 'UserIdentity');
  protected $_maxRequestsPerWorker = 10;
  protected $_verbose = false;
  protected $_maxAttempts = 20;
  protected $_requireMongoConnected = true;
  
  public function initialize(){
    parent::initialize();
    $this->_addShards(true);
  }
  
  public function execute()
  {
    if(count($this->args) > 0){
      $loops = (int) $this->args[0];
    } else {
      $loops = 100;
    }
    
    if(count($this->args) == 2){
      $ppid = (int) $this->args[1];
    } else {
      $ppid = null;
    }

    $this->_maxAttempts = Configure::read('WorkerMaxAttempts');
    $this->tubes[] = getmypid() . '.' . $this->_getHostname();
    
    $this->_verbose = isset($this->params['verbose']);
    $this->_maxRequestsPerWorker = isset($this->params['max-requests']) ? $this->params['max-requests'] : $this->_maxRequestsPerWorker;
    
    $this->out($this->name, null, true);
    $this->hr(null, true);
    
    $this->out("This worker will process {$loops} jobs and then respawn.");
    
    $count = 0;
    for($i=0; $i < $loops; $i++){
      try {
        if($this->_requireMongoConnected && !ConnectionManager::getDatasource('default')->isConnected()){
          $this->out('No connection to Mongo.');

          exit(97);
        }
      } 
      catch(Exception $e){
        //do nothing;
      }
      $this->hr();
      $this->out('Waiting for a job... STRG+C to abort');
      
      $job = $this->Job->reserve(array('tube' => $this->tubes));
      $wait = 1;
      
      $attempts = 0;
      
      while(empty($job)){
        $attempts++;
        print ".";
        
        if($wait < 1000000000 && $attempts > count($this->tubes)){
          $wait *= 2;
        }
        
        if($wait > 1000000000){
          $this->_addShards(); //essentially idling, so keep checking for new tube shards
        }
        
        usleep($wait / 1000);
        $job = $this->Job->reserve(array('tube' => $this->tubes));
      }
      
      $this->out('');
      
      if (empty($job) || empty($job))
      {
        $this->hr();
        $this->out("Corrupted Job!\n" . print_r($job, true));
        $this->hr();
        continue;
      }
      
      if (!empty($job['Job']['attempts']) && $job['Job']['attempts'] >= $this->_maxAttempts)
      {
        $this->hr();
        $this->out("Job #{$job['Job']['id']} with {$job['Job']['attempts']} attempts found.");
        $this->hr();
        $this->_tooManyAttempts($job);
        continue;
      }
      
      if (!$this->_doManageWorkerJob($job))
      {
        continue;
      }
      
      $requests = isset($job['Job']['requests']) ? $job['Job']['requests'] : 1;
      $job['Job']['requests'] = $requests;
      
      $priority = isset($job['Job']['priority']) ? $job['Job']['priority'] : 5000;
      $job['Job']['priority'] = $priority;
      
      if ($requests && $requests > $this->_maxRequestsPerWorker)
      {
        $this->_splitJob($job, $requests);
      }
      else
      {
      	$this->hr();
        $this->out("Job: #{$job['Job']['id']}\nTube:{$job['Job']['tube']}\nServer hash:{$job['Job']['server_hash']}\n");
        
        if($job['Job']['requests'] > 1){
          $this->out("Requests: {$job['Job']['requests']}\n");
        }
        
        if ($this->_validJob($job))
        {
          $total = $this->_runJob($job, $ppid);
          $this->out("$total request" . $this->_plural($total) . " generated for Job #{$job['Job']['id']}");
          
          if ($total == $requests)
          {
            //we've done all the requests!
            $this->out("All requests from Job #{$job['Job']['id']} completed.");
          }
          elseif ($total < $requests)
          {
            //missing some requests
            $missingRequests = $requests - $total;
            $requestsPlural = 'request' . $this->_plural($missingRequests);
            if ($this->_cloneJob($job, $missingRequests))
            {
              $this->out("Created new job to handle $missingRequests missing $requestsPlural from Job #{$job['Job']['id']} (Priority: {$job['Job']['priority']})");
            }
            else
            {
              $this->out("Could not create new job to handle $missingRequests missing $requestsPlural from Job #{$job['Job']['id']}");
            }
          }
          
          if (!$this->_deleteJob($job, true))
          {
            if (!$this->_buryJob($job))
            {
              $this->_wait(300);
              if (!$this->_deleteJob($job, true))
              {
                $this->_wait(300);
                if (!$this->_buryJob($job))
                {
                  $date = date('r');
                  $class = get_class($this);
                  error_log("[{$date}] Worker Error ({$class}): Could not delete job nor bury it, even after two 300ms delays. Job #{$job['Job']['id']}");
                }
              }
            }
          }
        }
        else
        {
          $this->hr();
          $this->out("Invalid Job #{$job['Job']['id']}. Got data:\n" . print_r($job, true));
          $this->hr();
          
          $this->_buryJob($job);
        }
      }

    }
    
    $this->hr();

    $this->out("{$loops} cycles completed. Exiting.");
    exit(98);
    
  }
  
  /**
   * Automatically add tubes of the same type that aren't currently in the watch list
   * 
   * @param boolean $sweep When true: ignore tubes which already have at least 1 worker watching them.
   *
   */
  protected function _addShards($sweep = false){
    $seed = $this->tubes[0];
    
    $count = 0;
    foreach( ClassRegistry::init('Queue.Job')->listTubes() as $tube){
      if(strpos($tube, $seed) === 0 && !in_array($tube, $this->tubes)){
        if($sweep){
          //ignore tubes with reserved jobs. ie: 'sweep' through all 
          //the tubes that aren't being worked on before focusing on 
          //the big clogged up ones
          $stats = ClassRegistry::init('Queue.Job')->statsTube($tube);
          
          if($stats && $stats['current-jobs-reserved'] == 0){
            $this->tubes[] = $tube;
            $count++;
          }
        } else {
          $this->tubes[] = $tube;
          $count++;
        }
      }
    }
    
    if($count > 0){
      $this->out("\nAutomatically added {$count} additional tube shard(s) to watchlist.", 1, true);
    }
  }
  
  protected function _doManageWorkerJob($job)
  {
    if (!empty($job['Job']['command']))
    {
      $command = $job['Job']['command'];
      switch ($command)
      {
        case 'exit':
          $this->out("Exit command received from Job #{$job['Job']['id']}");
          if (!$this->_deleteJob($job))
          {
            if (!$this->_buryJob($job))
            {
              return false;
            }
          }
          else
          {
            exit(99);
            return true;
          }
          break;
        default:
          break;
      }
    }
    return true;
  }
  
  protected function _wait($ms)
  {
    $this->out("Waiting for $ms milliseconds");
    usleep($ms * 1000);
  }
  
  protected function _tooManyAttempts($job)
  {
    $job['Job']['attempts'] = 0;
    return $this->_deleteJob($job);
  }
  
  protected function _validJob($job)
  {
     if(!is_array($job)){
     	return false;
     }
     
     return true;
  }
  
  protected function _cloneJob($job, $requests = 1, $incAttempts = true)
  {
    $data = $job['Job'];
    unset($data['id']);
    $data['requests'] = $requests;
    
    if ($incAttempts)
    {
      $data['priority']++;
      $data['attempts'] = isset($job['Job']['attempts']) ? $job['Job']['attempts'] + 1 : 1;
    }
    
    $tube = $job['Job']['tube'];
    
    return ClassRegistry::init('Queue.Job')->put($data, array('tube' => $tube, 'priority' => $data['priority'], 'delay' => 60));
  }
  
  protected function _buryJob($job)
  {
    $jobId = $job['Job']['id'];
    $result = $this->Job->bury($job);
    
    if ($result)
    {
      $this->out("Job #{$jobId} buried.");
    }
    else
    {
      $this->out("Job #{$jobId} could not be buried!");
    }
    return $result;
  }
  
  protected function _deleteJob($job, $messages = false)
  {
    $jobId = $job['Job']['id'];
    $result = $this->Job->delete($job);
    
    if (!$messages)
    {
      return $result;
    }
    
    if ($result)
    {
      $this->out("Job #{$jobId} deleted.");
    }
    else
    {
      $this->out("Job #{$jobId} could not be deleted!");
    }
    return $result;
  }
  
  protected function _releaseJob($job)
  {
    $jobId = $job['Job']['id'];
    $result = $this->Job->release($job);
    if ($result)
    {
      $this->out("Job #{$jobId} released to be worked on again.");
    }
    else
    {
      $this->out("Job #{$jobId} could not be released!");
      //this should never happen!!!
    }
    return $result;
  }
  
  protected function _splitJob($job, $requests)
  {
    $this->hr();
    $this->out("Found job #{$job['Job']['id']} with $requests requests, splitting into smaller jobs..");
    
    $newJobRequests = self::_smallerJobs($requests, $this->_maxRequestsPerWorker);
    
    $this->out("Splitting job #{$job['Job']['id']} into " . count($newJobRequests) . " jobs with at most {$this->_maxRequestsPerWorker} requests per job.");
    
    if ($this->_deleteJob($job))
    {
      $this->out("Job #{$job['Job']['id']} deleted.");
      
      $tube = $job['Job']['tube'];
      $this->out("Going to put new smaller jobs into the tube '$tube'");
      
      $created = $notCreated = $missingRequests = 0;
      foreach ($newJobRequests as $requests)
      {
        if ($this->_cloneJob($job, $requests))
        {
          $created++;
        }
        else
        {
          $notCreated++;
          $missingRequests += $requests;
        }
      }
      
      $this->out("    Created Jobs: $created");
      $this->out("Not Created Jobs: $notCreated");
      $this->out("Missing Requests: $missingRequests");
      
      if ($missingRequests)
      {
        $this->out("There were $missingRequests missing requests, attempting to create a job containing all of them. This may be split up again if it is also too large.");
        
        if ($this->_cloneJob($job, $missingRequests))
        {
          $this->out("New Job created to contain $missingRequests requests.");
        }
        else
        {
          $this->out("A new Job could not be created for the missing requests ($missingRequests)!");
          //this should never happen!!!
        }
      }
    }
    else
    {
      $this->out("Could not delete large Job #{$job['Job']['id']}, attempting to release");
      $this->_releaseJob($job);
    }
  }
  
  /**
  * Splits a request gen job into smaller jobs
  * 
  * @param mixed $total how many we have total to work with
  * @param mixed $min the smallest chunk a job should become
  * @return array contains multiple indexes with how many requests should be performed in each job
  */
  protected static function _smallerJobs($total, $min = 10)
  {
    if ($min <= 0)
    {
      $min = 10;
    }
    
    $count = floor($total / $min);
    $remainder = $total % $min;
    
    $jobs = array_fill(0, $count, $min);
    
    if ($remainder)
    {
      $jobs[] = floor($remainder);
    }
    
    return $jobs;
  }
  
  protected function _plural($num)
  {
    return ($num != 1) ? 's' : '';
  }
  
  protected function _getHostname()
  {
    $hostname = gethostname();
    if (empty($hostname))
    {
      $hostname = php_uname('n');
    }
    return $hostname;
  }
  
  public function hr($newlines = 0, $force = false)
  {
    if (is_null($newlines))
    {
      $newlines = 0;
    }
    
    $this->out(null, $newlines, $force);
    $this->out('---------------------------------------------------------------', null, $force);
    $this->out(null, $newlines, $force);
  }
  
  public function out($message, $newlines = 1, $force = false)
  {
    if (is_null($newlines))
    {
      $newlines = 1;
    }
    
    if ($this->_verbose || $force)
    {
      return parent::out($message, $newlines);
    }
  }
}