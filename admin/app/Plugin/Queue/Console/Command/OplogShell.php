<?php
/**
 * Oplog Shell File
 *
 * Copyright (c) 2009 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    queue
 * @subpackage queue.shells
 * @copyright  2009 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/queue
 */

/**
 * Manage Shell Class
 *
 * @package    queue
 * @subpackage queue.shells
 */
class OplogShell extends Shell {

/**
 * Tasks to load. Additional tasks are also loaded dynamically.
 *
 * @see main()
 * @var string
 */

  public $tasks = array();
  public $uses = array('Queue.Job');
  protected $_lockFp;
  protected $_hasLock = false;
  
  public function initialize()
  {
    $this->_hasLock = $this->checkLock($this->params);
    
    var_dump($this->params);
    
    parent::initialize();
  }
  
  public function __destruct()
  {
    if ($this->_hasLock)
    {
      flock($this->_lockFp, LOCK_UN);
      $this->_hasLock = false;
    }
  }
  
  public function checkLock($params)
  {
    array_push($params, 'ssm');
    $lockFile = sys_get_temp_dir() . DS . implode('-', $params) . '.lock';
    
    $this->_lockFp = fopen($lockFile, 'w+');
    
    if (flock($this->_lockFp, LOCK_EX | LOCK_NB))
    {
      return true;
    }
    else
    {
      throw new RuntimeException('Lockfile in place. Aborting.');
      return false;
    }
  }
/**
 * _welcome
 *
 * @return void
 */
  function startup() {
    $this->out(__d('queue', 'Operational Log Processing Shell'));
    $this->hr();
  }

/**
 * main
 *
 * @return void
 */
  function main() {

    Configure::write('Cache.disable', 1);
    
    $count = 0;
    
    while($job = $this->Job->replayOplog()){
      $this->out('Log ' . $job . '.');
      $count++;
    }
    
    if($error = $this->Job->lastOplogError()){
      $this->out('There was an error: ' . $error);
    } 
    
    $this->out($count . ' log(s) replayed.');
  }



/**
 * Displays help contents
 *
 * @return void
 */
  function help() {
    // 63 chars ===============================================================

    $this->out('');
    $this->hr();
    $this->out('Usage: Console/cake Queue.Oplog');
  }
}