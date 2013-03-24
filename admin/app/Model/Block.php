<?php
App::uses('Queue', 'Job');

class Block extends AppModel {
	protected function _countingMongodb($options){
		$db = new MongoClient("mongodb://{$options['host']}");
		$db = $db->selectDb($options['db']);
		
		$collections = $db->listCollections();
		
		$ret = array();
		foreach($collections as $collection){
			$name = $collection->getName();
			$ret[] = array('_id' => $name, 'count' => $collection->count(), 'indexes' => $collection->getIndexInfo());
		}
				
		return $ret;
	} 

	protected function _countingBeanstalkd($options){
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
	
	public function live($block){
		if(!empty($block)){
			$method = "_" . $block["_id"];
			
			if(method_exists($this, $method)){
				return call_user_func(array($this, $method), $block['options']);
			}
		}
		
		return false;
	}
}