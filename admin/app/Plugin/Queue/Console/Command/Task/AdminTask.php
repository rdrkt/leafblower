<?php

/**
 * Admin Task File
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
 * Admin Task Class
 *
 * @package    queue
 * @subpackage queue.shells.tasks
 */
App::uses('QueueShell', 'Queue.Console/Command');

class AdminTask extends QueueShell
{

  var $uses = array('Queue.Job');
  var $tubes = array('default');

  function execute()
  {
    $this->verbose = isset($this->params['verbose']);

    $this->out('[K]ick a certain number of jobs back into the ready queue.');
    $this->out('[P]urge *all* jobs from a certain queue *instantly*.');
    $action = $this->in('What would you like to do?', array('K', 'P'));

    $this->tubes = $this->_tubes();

    switch (strtoupper($action))
    {
      case 'K':
        foreach ($this->tubes as $tube)
        {
          $this->out("Will kick in jobs in tube `{$tube}`.");

          $result = $this->Job->kick(array(
              'bound' => $this->in('Number of jobs:', null, 100),
                  ) + compact('tube'));
        }
        $this->out($result ? 'OK' : 'FAILED');
        break;
      case 'P':
        $type = $this->in(
                'Which type of jobs should be purged?', array('ready', 'buried', 'delayed'), 'buried'
        );

        foreach ($this->tubes as $tube)
        {
          $this->out("Purging {$type} jobs for tube `{$tube}`...");
          $this->Job->choose($tube);

          if ($this->Job->purge($tube))
          {
            $this->out(" deleted.");
          }
          else
          {
            $this->err("Failed to delete $tube.");
          }
        }
    }
    break;
  }

}

?>