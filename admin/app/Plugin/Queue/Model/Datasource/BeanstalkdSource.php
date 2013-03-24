<?php
/**
 * Beanstalkd Source File
 *
 * Copyright (c) 2009 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 2.x
 *
 * @package    queue
 * @subpackage queue.models.datasources
 * @copyright  2009 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/queue
 */
App::uses('Datasource', 'Model/Datasource');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

App::import('Vendor', 'Queue.Vendor/pheanstalk_init', true, null, 'pheanstalk'.DS.'pheanstalk_init.php');

/**
 * Beanstalkd Source Class
 *
 * @package    queue
 * @subpackage queue.models.datasources
 */
class BeanstalkdSource extends DataSource {
    public $startQuote;
    public $endQuote;
    
/**
 * Holds ID of last inserted job
 *
 * Works analog to {@see Model::__insertID}.
 *
 * @var mixed
 * @access private
 */
  private $__insertID;
  private $_hashes = array();
  private $_hashMap = array();
  private $_watch = array();
  private $_currentTube = null;
  private $_use = null;
  private $_oplogError = null;
  private $_formats = array('msgpack', 'json', 'php');
  
/**
 * The default configuration of a specific DataSource
 *
 * @var array
 * @access protected
 */
  protected $_baseConfig = array(
    'servers' => array(
      array('host' => 'localhost', 'port' => 11300, 'timeout' => 1),
    ),
    'ttr' => 120,
    'kickBound' => 100,
    'format' => 'json',
  );
  
  protected $_baseServer = array('host' => 'localhost', 'port' => 11300, 'timeout' => 1);

  function __construct( $config = array() ) {
    parent::__construct();
    
    foreach($config['options']['servers'] as $key=>$server){
      $config['options']['servers'][$key] = array_merge($this->_baseServer, $server);
    }
    
    if(empty($config['options']['oplog_location'])){
      $config['options']['oplog_location'] = TMP . 'bs_ops' . DS;
    }
    
    
    $this->setConfig( $config );
    $this->fullDebug = Configure::read('debug') > 1;
    
    if ( empty( $config['options']['servers'] ) )
    {
      $servers = array($this->_baseServer);
    }
    else 
    {
      $servers = $config['options']['servers'];
    }
    
    $this->_serverCount = count($servers);
    $this->_connections = array();
    
    foreach ($servers as $server)
    {
      $this->_connections[] = new Pheanstalk($server['host'], $server['port'], $server['timeout']);
      $this->_hashMap[] = array();
    }
    
    $this->connected = false;//the actual connection will not initialize until the first command is executed.
  }
  
  protected function _toPheanstalk_Job(array $job){
    return new Pheanstalk_Job($job['Job']['id'], $this->_encode($job['Job']));
  }
  
  protected function _hash($key){
    $hash = crc32($key) % $this->_serverCount;
    
    if(empty($this->_hashes) || !in_array($hash, $this->_hashes)){
      $this->_hashes[] = $hash;
    }
    
    if(!in_array($key, $this->_hashMap[$hash])){
      $this->_hashMap[$hash][] = $key;
    }
    
    return $hash;
  }

  /**
   * In order for beanstalk to support operation logs, commands must be able to assume that the connection
   * state (including which tubes are being watched and what is being used) is exactly the same as when the command 
   * was originally run. This method will take the desired state as a parameter and ensure that the internal state matches.
   * 
   * @param $state the driver state you wish to be in before executing a command.
   */
  function checkState($state){
    if(!empty($state['_use']) && $this->_use != $state['_use']){
      $this->choose($this, $state['_use']);
    }
    
    if(!empty($state['_watch'])){
      foreach($state['_watch'] as $tube){
        $this->watch($this, $tube);
      }
    }
  }
  
  /**
   * 
   * Command executes an action on a beanstalk connection specified with the $server index. 
   * The action is defined by $command, eg: 'put', and all the arguments needed to execute this
   * command should follow this, comma separated.
   * 
   */
  
  function command(/* $server, $command, [$arg1,] [$arg2,] ... [$argn] */){
    $params = func_get_args();
    
    $args = $params;
    
    $server = array_shift($params);
    
    $connection = $this->_connections[$server];
    $command = array_shift($params);
    
    try {
      //throw new Pheanstalk_Exception_SocketException;
      $return = call_user_func_array(array($connection, $command), $params);
    } catch (Pheanstalk_Exception $e) {
      if($command == 'put'){
        //There was a problem trying to write the 'put' command to beanstalks, so save it to the operation log.
        
        $oplog_location = $this->config['options']['oplog_location'];
        $oplog_prefix = $this->config['options']['oplog_prefix'];
        
        $dir = new Folder($oplog_location, true, 0777);
        $file = new File($oplog_location . $oplog_prefix . '.' . microtime(true) . '.' . getmypid() . '.' . rand(), true, 0666);
        
        $data = array(
          'command' => $args,
          'state' => array('_use' => $this->_use),
        );
        
        $data = $this->_encode($data);
        $file->write($data);
      }
      
      return false;
    }
    
    return $return;
  }
  
  function replayOplog($Model){
    $this->_oplogError = null;
    
    $oplog_location = $this->config['options']['oplog_location'];
    $oplog_prefix = $this->config['options']['oplog_prefix'];
    
    $dir = new Folder( $oplog_location, true);
    
    $files = $dir->find($oplog_prefix . '.*', true);
    
    if(empty($files)){
      return false;
    }
    
    $filename = array_shift($files);
    $file = new File( $this->config['options']['oplog_location'] . $filename );
    
    if($content = $file->read()){
      $file->delete();
      $job = $this->_decode($content);
      
      if(!empty($job)){
          $state = $job['state'];
          $command = $job['command'];
          
          $this->checkState($state);
          
          $result = call_user_func_array(array($this, 'command'), $command);
          
          if(!empty($result)){
            return $filename;
          }
          
          $this->_oplogError = 'Unable to replay log. Rewritten to new log for later processing. - ' . $filename;
          return false;
      }
      
      $this->_oplogError = 'Unable to decode job - ' . $filename;
       return false;
    } else {
      $file->delete(); //empty files should get deleted.
      
      $this->_oplogError = 'Unable to read log or log is empty - ' . $filename;
      return false;
    }
    
    $file->close();
    $file->delete();
    return false;
  }
  
  function close() {
    foreach($this->_connections as $key=>$connection){
      unset($this->_connections[$key]);
    }
    
    return true;
  }

  function connect() {
    return $this->isConnected(); //isConnected will force the connection by checking tubes;
  }

  function disconnect() {
    return $this->close();
  }
  
  function isConnected() {
    foreach($this->_connections as $connection){
      try{
        if(count($connection->listTubes()) == 0){
          return $this->connected = false;
        }
      } catch(Exception $e){
        return $this->connected = false;
      }
    }
    
    return $this->connected = true;
  }

  function put(&$Model, $data, $options = array()) {
    unset($Model->data[$Model->alias]);
    $Model->set($data);
    $body = $Model->data[$Model->alias];

    $priority = 0;
    $delay = 0;
    $ttr = $this->config['ttr'];
    $tube = 'default';
    extract($options, EXTR_OVERWRITE);
    
    $this->choose($Model, $tube);
    
    $server = $this->_hash($tube);
    
    $metadata = array(
      'server_hash' => $server,
      'tube' => $tube,
    );
    
    $body = array_merge($metadata, $body);
    
    $id = $this->command($server, 'put', $this->_encode($body), $priority, $delay, $ttr);
    
    if ($id !== false) {
      $Model->setInsertId($id);
      return $this->__insertID = $Model->id = $id;
    }
    return false;
  }

  function choose(&$Model, $tube) {
    $server = $this->_hash($tube);
    
    $this->_use = $tube;
    $this->command($server, 'useTube', $tube);
    
    return true;
  }

  function reserve(&$Model, $options = array()) {
    $timeout = 0;
    $tube = array();
    $type = 'ready';
    extract($options, EXTR_OVERWRITE);
    
    $this->watch($Model, $tube);
    
    if($this->_currentTube === null){
      $this->_currentTube = array_shift($this->_watch);
      array_push($this->_watch, $this->_currentTube);
    }
    
    $tube = $this->_currentTube;
    $server = $this->_hash($tube);
    
    $this->command($server, 'watch', $tube);
    if ($result = $this->command($server, 'reserve', $timeout)) {
        $Model->job = $result;
        
        $data = $result->getData();
        $id = $result->getId();
        $data = $this->_decode($data);
        $data['id'] = $id;
        
        //make sure the data reflects reality
        $data['tube'] = $tube;
        $data['server_hash'] = $server;
        
        $this->command($server, 'ignore', $tube);
        
        return $Model->set(array($Model->alias => $data));
    }
    
    $this->command($server, 'ignore', $tube);
    
    //We couldn't find a job on this tube, so it's time to try another one.
    
    $this->shuffleTube();
    
    return false;
  }

  public function shuffleTube(){
    $this->_currentTube = array_shift($this->_watch);
    array_push($this->_watch, $this->_currentTube);
  }

  function watch(&$Model, $tube) {
    foreach ((array)$tube as $t) {
      if(!in_array($t, $this->_watch)){
        $this->_watch[] = $t;
        shuffle($this->_watch);
        reset($this->_watch);
      }
    }
    
    return true;
  }
  
  function ignore(&$Model, $tubes) {
    
    foreach ((array)$tubes as $tube) {
      $index = array_index($tube, $this->_watch);
      unset($this->_watch[$index]);
    }
    
    $this->_watch = array_values($this->_watch);
    
    return true;
  }
  
  function release(&$Model, $options = array()) {
    $priority = 0;
    $delay = 0;
    extract($options, EXTR_OVERWRITE);

    if ($job === null) {
      $job = $Model->job;
    } else {
      $job = $this->_toPheanstalk_Job($job);
    }
    
    $data = $this->_decode($job->getData());
    $server = $data['server_hash'];
    
    $this->command($server, 'release', $job, $priority, $delay);
    
    return true;
  }

  function touch(&$Model, Job $job = null, $options = array()) {
    extract($options, EXTR_OVERWRITE);

    if ($job === null) {
      $job = $Model->job;
    } else {
      $job = $this->_toPheanstalk_Job($job);
    }
    
    $data = $this->_decode($job->getData());
    $server = $data['server_hash'];
    
    $this->command($server, 'touch', $job);
    return true;
  }

  function bury(&$Model, array $job = null, $options = array()) {
    $priority = 0;
    extract($options, EXTR_OVERWRITE);
    
    if ($job === null) {
      $job = $Model->job;
    } else {
      $job = $this->_toPheanstalk_Job($job);
    }
    
    $data = $this->_decode($job->getData());
    $server = $data['server_hash'];
    
    $this->command($server, 'bury', $job, $priority);
    
    return true;
  }

  function kick(&$Model, $options = array()) {
    if (!is_array($options)) {
      $options = array('bound' => $options);
    }
    $bound = $this->config['kickBound'];
    $tube = null;
    extract($options, EXTR_OVERWRITE);
    
    $this->choose($Model, $tube);
    $server = $this->_hash($tube);
    $this->command($server, 'kick', $bound);
    
    return true;
  }
  
  function purge($Model, $options = array()){
    if (!is_array($options)) {
      $options = array('bound' => $options);
    }
    
    $tube = null;
    $type = 'buried';
    extract($options, EXTR_OVERWRITE);
    
    $count = 0;
    while ($job = $this->next($Model, array('type'=>$type, 'tube'=>$tube))) {
      if ($this->delete($Model, $job)) {
        $count++;
      } else {
        return false;
      }
    }
    
    return $count;
  }

  function peek(&$Model, $id = null, $server = null) {
    if($server !== null){
      return $this->command($server, 'peek', $id !== null ? $id : $Model->id);
    }
    
    $results = array();
    
    foreach($this->_hashes as $server){
      $results = $this->command($server, 'peek', $id !== null ? $id : $Model->id);
      
      if($results){
        break;
      }
    }
    
    return $results;
  }

  function next(&$Model, $options = array()) {
    $options += array(
      'type'  => 'ready',
      'tube' => 'default',
    );
    
    $type = 'ready';
    $tube = 'default';
    extract($options, EXTR_OVERWRITE);
    
    $this->choose($Model, $tube);
    $method = 'peek' . ucfirst($type);
    
    $server = $this->_hash($tube);
    
    try {
      $result = $this->command($server, $method);
      
      if(empty($result)){
        return false;
      }
    } catch (Exception $e){
      return false;
    }
    
    $Model->job = $result;
    
    $data = $result->getData();
    $id = $result->getId();
    $data = $this->_decode($data);
    $data['id'] = $id;
    
    //make sure the data reflects reality
    $data['tube'] = $tube;
    $data['server_hash'] = $server;
    
    $this->command($server, 'ignore', $tube);
    
    return $Model->set(array($Model->alias => $data));
  }

  function statistics(&$Model, $type = null, $key = null) {
    $out = array();
    
    foreach($this->_connections[$server] as $connection){
      if (!$type) {
        $out = array_merge($out, $this->connection->stats());
      } elseif ($type == 'job') {
        $key = $key !== null ? $key : $Model->id;
        $out = array_merge($out, $this->connection->statsJob($key));
      } elseif ($type == 'tube') {
        $key = $key !== null ? $key : $this->connection->listTubeChosen();
        $out = array_merge($out, $this->connection->statsTube($key));
      }
    }
    
    return $out;
  }
  
  function statsTube(&$Model, $tube) {
    $tubes = explode(',', $tube);
    
    $statsTube = array();
    
    foreach($tubes as $tube){
      trim($tube);
      
      $server = $this->_hash($tube);
      
      try {
        $stats = (array) $this->_connections[$server]->statsTube($tube);
      } catch (Exception $e){
        $stats = array(); //the tube does not exist
      }
      
      $statsTube = array_merge($statsTube, $stats);
    }
    
    return $statsTube;
  }
  
  function listTubes(&$Model, $extended = false) 
  {
    $servers = $this->config['options']['servers'];
    $result = array();
    
    for ($i=0; $i < $this->_serverCount; $i++)
    {
      $tubes = $this->_connections[$i]->listTubes();
      foreach($tubes as $tube){
        if($extended){
          $tube = "{$tube} [{$servers[$i]['host']}:{$servers[$i]['port']}]";
        }
        
        if(!in_array($tube, $result)){
          $result[] = $tube;
        }
      }
    }
    
    sort($result);
    
    return $result;
  }

  function _encode($data) {
    switch ($this->config['format']) {
      case 'msgpack':
        return msgpack_pack($data);
      case 'json':
        return json_encode($data);
      case 'php':
      default:
        return serialize($data);
    }
  }

  function _decode($data) {
  	$ret = false;
  	
    switch ($this->config['format']) {
      case 'msgpack':
        $ret = @msgpack_unpack($data);
      break;
      case 'json':
        $ret = @json_decode($data);
      break;
      case 'php':
      default:
        $ret = @unserialize($data);
      break;
    }
    
    if($ret){
    	return $ret;
    } else {
    	//before giving up, try all formats, just in case
    	foreach($this->_formats as $format){
    		$ret = false;
    		
    		switch ($format) {
    			case 'msgpack':
    				$ret = @msgpack_unpack($data);
    				break;
    			case 'json':
    				$ret = @json_decode($data, true);
    				break;
    			case 'php':
    			default:
    				$ret = @unserialize($data);
    				break;
    		}
    		
    		if($ret){
    			return $ret;
    		}
    	}
    }
  }

/**
 * All calls to methods on the model are routed through this method
 *
 * @param mixed $method
 * @param mixed $params
 * @param mixed $Model
 * @access public
 * @return void
 */
  function query($method, $params, &$Model) {
    array_unshift($params, $Model);

    $startQuery = microtime(true);

    switch ($method) {
      case 'lastOplogError':
        return $this->_oplogError;

      case 'shuffleTube':
      case 'replayOplog':
      case 'put':
      case 'choose':
      case 'reserve':
      case 'watch':
      case 'release':
      case 'delete':
      case 'touch':
      case 'bury':
      case 'kick':
      case 'purge':
      case 'peek':
      case 'next':
      case 'listTubes':
      case 'statsTube':
      case 'statistics':
        $result = $this->dispatchMethod($method, $params);
        $this->took = microtime(true) - $startQuery;
        $this->error = $this->lastError();
        $this->logQuery($method, $params);
        return $result;
      default:
        trigger_error("BeanstalkdSource::query - Unkown method {$method}.", E_USER_WARNING);
        return false;
    }
  }

  function create(&$Model, $fields = null, $values = null) {
    return false;
  }

  function read(&$Model, $queryData = array()) {
    if ($queryData['fields'] == 'count') {
      if ($this->peek($Model, $queryData['conditions']['Job.id']['id'], $queryData['conditions']['Job.id']['server_hash'])) {
        return array(0 => array(0 => array('count' => 1)));
      }
    }
    return false;
  }

  function update(&$Model, $fields = null, $values = null) {
    return false;
  }

/**
 * Deletes a job
 *
 * @param Model $Model
 * @param mixed $id
 */
  function delete(&$Model, $queryData) {
    $job = null;
    
    if(is_object($queryData) && get_class($queryData) == 'Job'){
      $job = $queryData;
    }
    
    if (!is_array($job)) {
      $job = $Model->job;
    } else {
      $job = $this->_toPheanstalk_Job($job);
    }
    
    $data = $this->_decode($job->getData());
    
    $server = $this->_hash($data['tube']);
    
    try {
      $this->_connections[$server]->delete($job);
    } catch (Exception $e){
      trigger_error("Job not found on #{$server}.", E_USER_NOTICE);
      //it wasn't on the server, so try all of them.
      foreach($this->_connections as $id=>$connection){
        try{
          $connection->delete($job);
          trigger_error( "Job found on beanstalkd #{$id}, not #{$server}.", E_USER_NOTICE);
          break;
        } catch(Exception $e){
          //do nothing
        }
      }
    }
    
    return true;
  }

/**
 * Returns a data source specific expression
 *
 * @see Model::delete, Model::exists, Model::_findCount
 * @param mixed $model
 * @param mixed $function I.e. `'count'`
 * @param array $params
 * @access public
 * @return void
 */
  function calculate(&$Model, $function, $params = array()) {
    return $function;
  }

/**
 * Returns available sources
 *
 * @see Mode::useTable
 * @return array
 */
  function listSources($data = null) {
    return array('jobs');
  }

  function describe($model) {
  }

  function logQuery($method, $params) {
    $this->_queriesCnt++;
    $this->_queriesTime += $this->took;
    $this->_queriesLog[] = array(
      'query' => $method,
      'error' => $this->error,
      'took' => $this->took,
      'affected' => 0,
      'numRows' => 0
    );
  }

  function lastError() {
    return null;
  }

/**
 * Returns the ID generated from the previous INSERT operation.
 *
 * Neeed as as workaround for beanstalkd's missing last insert id support.
 *          
 * @param unknown_type $source
 * @return integer
 */
  function lastInsertId($source = null) {
    return $this->__insertID;
  }
}