<?php

class ApiController extends AppController {
	public $uses = array('Block', 'Users', 'Profiles', 'Logs');
	
	protected function _toJson($array){
		return json_encode($array);		
	}	
	
	public function block(){
		$this->autoRender = false;
		
		$blocks = $this->Block->find('all');
		
		$results = array();
		
		foreach ($blocks as $block){
			$block = $block['Block'];			
			$results[$block['type']][] = $block;
		}

		$blocks = array();
		foreach($results as $type => $result){
			$blocks[] = array(
					'name' => Inflector::humanize($type),
					'type' => $type,
					'blocks' => $result,
			);
		}
		
		return $this->_toJson($blocks);
	}
}
