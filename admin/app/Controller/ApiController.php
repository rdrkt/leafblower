<?php

class ApiController extends AppController {
	public $uses = array('Block', 'User', 'Profile', 'Log');
	
	public function beforeFilter(){
		$this->autoRender = false;
	}
	
	protected function _toJson($array){
		return json_encode($array);		
	}	
	
	public function profile( $id = "" ){
		if($this->request->isGet()){
			$profile = $this->Profile->findById($id);
			
			$ret = false;
			if(!empty($profile)){
				$ret = $this->_toJson($profile['Profile']);
			} 
			
			return $this->_toJson($ret);
		}
		
		if($this->request->isPost()){
			$ret = false;				
			return $this->_toJson($ret);
		}		

		return $this->_toJson(false);
	}
	
	public function block(){
		if($this->request->isGet()){
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
		
		if($this->request->isPost()){
			$ret = false;
			return $this->_toJson($ret);
		}
		
		return $this->_toJson(false);
	}
}
