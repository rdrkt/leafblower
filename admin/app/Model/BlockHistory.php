<?php
class BlockHistory extends AppModel {

	public function store($block, $data){
		$type = $block["_id"];
		
		$data['time'] = time();
		
		$this->save(array($type, $data));
	}
	
	public function history($block){
		$type = $block["_id"];
	
		$rows = $this->find('all', array('conditions' => array('type'=>$type), 'limit'=>10, 'order'=>'created'));
	
		if(empty($rows)){
			return array();
		}
	
		$history = array();
		foreach($rows as $row){
			$row = current($row);
	
			$history[] = $data;
		}
	
		return $history;
	}
}