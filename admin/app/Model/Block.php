<?php
class Block extends AppModel {
	protected function _countingMongodb($options){
		$db = new Mongo();
		$db = $db->selectDb('demo');
		
		$collections = $db->listCollections();
		
		$ret = array();
		foreach($collections as $collection){
			$name = $collection->getName();
			$ret[$name] = array('count' => $collection->count());
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