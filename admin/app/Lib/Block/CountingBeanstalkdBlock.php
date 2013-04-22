<?php
class CountingBeanstalkdBlock extends BaseBlock {
	public static function display($options){
		$queue = ClassRegistry::init('Queue.Job');
	
		$tubes = $queue->listTubes();
	
		$ret = array();
		foreach($tubes as $tube){
			$stats = $queue->statsTube($tube);
			$count = $stats['current-jobs-urgent'];
				
			$ret[] = array('_id' => $tube, 'count' => $count, 'tubestats' => $stats);
		}
	
		return $ret;
	}
}