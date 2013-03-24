<?php
/**
 * Debug Worker Task File
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
Configure::write('debug', 2);

/**
 * Debug Worker Task Class
 *
 * @package    queue
 * @subpackage queue.shells.tasks
 */

class DebugWorkerTask extends QueueShell {

	var $uses = array('Queue.Job');
	var $tubes = array('default');

	function execute() {
		$this->out('Debug Worker');
		$this->hr();

		$tubes = 'default';

		if ($this->args) {
			$tubes = array_shift($this->args);
			$this->interactive = false;
		}
		$this->tubes = $this->_tubes();

		while (true) {
			$this->hr();
			$this->out('Waiting for a job... STRG+C to abort');
			$job = $this->Job->reserve(array('tube' => $this->tubes));
			$this->out('');
			$this->out('Got:');
			$this->out(var_export($job, true));
			$this->out('');
			$this->out('[D]elete');
			$this->out('[B]ury');
			$this->out('[R]elease');
			$this->out('[T]ouch');

			$action = strtoupper($this->in(__d('queue', 'What would you like to do?'), array('D', 'B', 'R', 'T'), 'D'));
			switch ($action) {
				case 'D':
					$result = $this->Job->delete();
					break;
				case 'B':
					$result = $this->Job->bury();
					break;
				case 'R':
					$result = $this->Job->release();
					break;
				case 'T':
					$result = $this->Job->touch();
					break;
			}
			$this->out($result ? 'OK' : 'FAILED');

			if (strtolower($this->in('Continue?', array('y', 'n'), 'y')) == 'n') {
				$this->_stop();
			}
		}
	}
}
?>