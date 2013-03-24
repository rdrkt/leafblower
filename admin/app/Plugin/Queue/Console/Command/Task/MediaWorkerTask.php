<?php
/**
 * Media Worker Task File
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
 * Media Worker Task Class
 *
 * @package    queue
 * @subpackage queue.shells.tasks
 */
App::uses('QueueShell', 'Queue.Console/Command');
class MediaWorkerTask extends QueueShell {

	var $description = 'Media Worker';

	var $uses = array('Queue.Job');

	var $tubes;

	var $verbose = false;

	var $model;

	function execute() {
		$this->verbose = isset($this->params['verbose']);

		if (isset($this->params['description'])) {
			$this->description = $this->params['description'];
		}
		if (isset($this->params['model'])) {
			$this->model = $this->params['model'];
		} else {
			$this->model = $this->in('Model', null, 'Media.Attachment');
		}
		$this->_Model = ClassRegistry::init($this->model);

		if (!isset($this->_Model->Behaviors->Generator)) {
			$this->error("Model `{$this->model}` has the `Generator` behavior not attached to it.");
		}
		$this->tubes = $this->_tubes();

		$tubesDisplay = implode(', ', $this->tubes);
		$this->log("Starting up watching tubes {$tubesDisplay}.", 'debug');
		$this->out("{$this->description} is watching tubes {$tubesDisplay}.");
		$this->hr();

		while (true) {
			$this->hr();
			$this->out("Waiting for job... STRG+C to abort.");

			$job = $this->Job->reserve(array('tube' => $this->tubes));
			$this->out();

			if (!$job) {
				$message = 'Got invalid job; burying.';
				$this->log($message, 'error');
				$this->error($message);

				$this->Job->bury();
				continue;
			}
			$message = "Got job `{$this->Job->id}`; deriving media.";
			$this->log($message, 'debug');
			$this->out($message);

			if ($this->verbose) {
				$this->out(var_export($job, true));
				$this->out();
			}
			$this->_process($job);
		}
		$this->log('Exiting.', 'debug');
	}

	function _process($job) {
		extract($job['Job'], EXTR_OVERWRITE);
		$exception = null;

		$start = time();

		if (!file_exists($file)) {
			$message = "File `{$file}` has gone since job creation.";
			$this->log($message, 'debug');
			$this->out($message);

			$message = "Deleting job `{$this->Job->id}`.";
			$this->log($message, 'debug');
			$this->out($message);

			$this->out('SKIP');

			$this->Job->delete();
			return true;
		}

		try {
			$result = $this->_Model->makeVersion($file, $process + array('delegate' => false));
		} catch (Exception $E) {
			$exception = $E->getMessage();
			$result = false;
		}
		$took = time() - $start;

		if ($result) {
			$message = "Version `{$process['version']}` of file `{$file}` made; took {$took} s.";
			$this->log($message, 'debug');
			$this->out($message);

			$message = "Deleting job `{$this->Job->id}`.";
			$this->log($message, 'debug');
			$this->out($message);

			$this->out('OK');

			$this->Job->delete();
			return true;
		}
		$message  = "Failed making version `{$process['version']}` of file `{$file}`; ";
		$message .= "took {$took} s.";

		if ($exception) {
			$message .= " Exception message was: `{$exception}`.";
		}
		$this->log($message, 'error');
		$this->err($message);

		$message = "Burying job `{$this->Job->id}`.";
		$this->log($message, 'debug');
		$this->out($message);

		$this->out('FAILED');

		$this->Job->bury();
		return false;
	}

	function log($message, $type = 'debug') {
		$message = "{$this->description} - {$message}";
		return parent::log($message, $type);
	}
}

?>