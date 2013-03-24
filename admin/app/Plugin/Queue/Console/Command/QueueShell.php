<?php
/**
 * Queue Shell File
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
class QueueShell extends Shell {

/**
 * Tasks to load. Additional tasks are also loaded dynamically.
 *
 * @see main()
 * @var string
 */

	var $tasks = array('Queue.Statistics', 'Queue.Tube', 'Queue.Admin');

/**
 * _welcome
 *
 * @return void
 */
	function _welcome() {
		$this->out(__d('queue', 'Queue Plugin Shell'));
		$this->hr();
	}

/**
 * main
 *
 * @return void
 */
	function main() {

		Configure::write('Cache.disable', 1);

		if ($this->args) {
			$worker = strpos($this->args[0], 'Worker') !== false;
			$producer = strpos($this->args[0], 'Producer') !== false;

			if ($worker || $producer) {
				return $this->_executeTask(array_shift($this->args));
			}
		}

		$this->out(__d('queue', '[P]roducer'));
		$this->out(__d('queue', '[W]orker'));
		$this->out(__d('queue', '[A]dmin'));
    $this->out(__d('queue', '[S]tatistics'));
    $this->out(__d('queue', '[L]ist Tubes'));
		$this->out(__d('queue', '[T]ube Statistics'));
		$this->out(__d('queue', '[H]elp'));
		$this->out(__d('queue', '[Q]uit'));

		$action = strtoupper($this->in(__d('queue', 'What would you like to do?'), array('W', 'P', 'S', 'L', 'T', 'A', 'H', 'Q'),'Q'));

		switch($action) {
			case 'W':
			case 'P':
				$prompt = sprintf('Please enter the name of the %s:',
					$action == 'W' ? 'worker' : 'producer'
				);
				$name = $this->in($prompt, null, 'debug');
				$this->_executeTask($name . ($action == 'W' ? 'Worker' : 'Producer'));
				break;
      case 'S':
        $this->Statistics->execute();
        break;
      case 'L':
        $this->Tube->listTubes();
        break;
			case 'T':
				$this->Tube->statistics();
				break;
			case 'H':
				$this->help();
				break;
			case 'A':
				$this->Admin->execute();
				break;
			case 'Q':
				$this->_stop(99);
		}
		$this->main();
	}

	function _executeTask($name) {
		$name = Inflector::camelize($name);

		if (!isset($this->{$name})) {
			//$this->tasks[] = 'Queue.' . $name;//including Queue. prefix appears to limit location of tasks to Queue plugin directory
			$this->tasks[] = $name;
			$this->loadTasks();
		}
		return $this->{$name}->execute();
	}

/**
 * Helper method to get selected tubes.
 *
 * @return array The selected tubes.
 */
	function _tubes() {
		if (isset($this->params['tube'])) {
			return array($this->params['tube']);
		} elseif (isset($this->params['tubes'])) {
			return explode(',', $this->params['tubes']);
		}
		return explode(',', $this->in('Tubes to watch (separate with comma)', null, 'default'));
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
		$this->out('Usage: cake <params> queue <command> <args>');
		$this->hr();
		$this->out('Parameters:');
		$this->out("\t-verbose");
		$this->out("\t-quiet");
		$this->out('');
		$this->out('Commands:');
		$this->out("\n\thelp\n\t\tShows this help message.");
		$this->out("\n\debug_producer <tube>\n\t\tStart debug producer.");
		$this->out("\n\debug_worker <tubes>\n\t\tStart debug worker.");
		$this->out("\n\media_worker <tubes>\n\t\tStart media worker.");
		$this->out("\n\tstatistics\n\t\tPrint statistics.");
		$this->out('');
		$this->out('Arguments:');
		$this->out("\t<tube>\n\t\tTubes to use.");
		$this->out("\t<tubes>\n\t\tComma separated list of tubes to watch.");
		$this->out('');
	}
}