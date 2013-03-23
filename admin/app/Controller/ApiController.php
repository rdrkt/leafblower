<?php

class ApiController extends AppController {
	public $uses = array('Block', 'Users', 'Profiles', 'Logs');
	
	protected function _toJson($array){
		return json_encode($array);		
	}	
	
	public function block(){
		$this->autoRender = false;
		
		$blocks = $this->Block->find('all');
		
		$result = array();
		
		foreach ($blocks as $block){
			$block = $block['Block'];
			
			$result[$block['type']][] = $block;
		}
		
		return $this->_toJsonP($result);
	}
}
