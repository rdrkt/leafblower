<?php
/**
 * Statistics Task File
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
 * @subpackage queue.shells.tasks
 * @copyright  2009 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/queue
 */

/**
 * Statistics Task Class
 *
 * @package    queue
 * @subpackage queue.shells.tasks
 */
class TubeTask extends QueueShell {

	var $uses = array('Queue.Job');

  function listTubes() {
    $this->out('Tubes');
    $this->hr();
    
    $this->out(var_export($this->Job->listTubes(true), true));
  }
  
	function statistics() {
    
    $tube = $this->in('Tube: ', null, 'default');
    
		$this->out('Tube Statistics: ' . $tube);
		$this->hr();

		if (isset($this->params['once'])) {
			$this->_displayStats($this->Job->statsTube($tube));
			return true;
		}
		$this->out('Updating every 5 seconds');
		$this->out('Press STRG+C to abort');

		while (true) {
			$this->out('Got:');
			$this->_displayStats($this->Job->statsTube($tube));
			
			sleep(5);
			$this->hr();
		}
	}
  
  protected function _displayStats($stats)
  {
    if ($stats)
    {
      foreach ($stats as $key => $value)
      {
        $this->out("{$key}: {$value}");
      }
    }
    else
    {
      $this->out('Error retrieving stats');
    }
  }
}

?>