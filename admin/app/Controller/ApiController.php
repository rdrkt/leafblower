<?php

class ApiController extends AppController {
	public $uses = array('Block', 'User', 'Profile', 'Log');
	
	public function beforeFilter(){
		Configure::write('debug', 0);
		$this->autoRender = false;
		$this->response->header('Access-Control-Allow-Origin: *');
	}
	
	protected function _toJson($array){
		return json_encode($array);		
	}	
	
	public function profile( $id = "" ){
		if($this->request->isGet()){
			if(empty($id)){
				//id is empty so list all profiles
				
				$ret = array();
				$profiles = $this->Profile->find('all');

				foreach($profiles as $profile){
					$ret[] = $profile['Profile'];
				}
				
				return $this->_toJson($ret);
			}
			
			$profile = $this->Profile->findById($id);
			
			$ret = false;
			if(!empty($profile)){
				$ret = $profile['Profile'];
			} 
			
			return $this->_toJson($ret);
		}
		
		if($this->request->isPost()){
			//get any existing data about the profile
			
			$data = $this->request->data;			
			//$data = current($data);
						
			$id = $data['_id'];
			
			$profile = $this->Profile->findById($id);
			if(!empty($profile)){
				$profile = $profile['Profile'];
			} else {
				$profile = array();
			}
			
			$data = array_merge($profile, $data);
			
			$data = $this->Profile->save($data);
			
			if(!empty($data)){
				return $this->_toJson($data['Profile']);
			}
			
			return $this->_toJson(false);
		}		

		return $this->_toJson(false);
	}
	
	public function user( $id = "" ){
		if($this->request->isGet()){
			if(empty($id)){
				//id is empty so list all users
			
				$ret = array();
				$users = $this->User->find('all');
			
				foreach($users as $user){
					$user = $user['User'];
					unset($user['password']);//never transmit (hashed) passwords over an open api
			
					$ret[] = $user;
				}
			
				return $this->_toJson($ret);
			}
			
			$user = $this->User->findById($id);
				
			$ret = false;
			if(!empty($user)){
				$ret = $user['User'];
			}
				
			return $this->_toJson($ret);
		}
	
		if($this->request->isPost()){
			//get any existing data about the user
			
			$data = $this->request->data;			
			//$data = current($data);
						
			$id = $data['_id'];
			
			$user = $this->User->findById($id);
			if(!empty($user)){
				$user = $user['User'];
			} else {
				$user = array();
			}
			
			$data = array_merge($user, $data);
			
			$data = $this->User->save($data);
			
			if(!empty($data)){
				return $this->_toJson($data['User']);
			}
			
			return $this->_toJson(false);
		}
	
		return $this->_toJson(false);
	}
	
	public function block( $id = '' ){ 
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
