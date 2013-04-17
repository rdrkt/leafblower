<?php

class ApiController extends AppController {
	public $uses = array('Block', 'User', 'Profile', 'Log');
	
	public function beforeFilter(){
		Configure::write('debug', 2);
		$this->autoRender = false;
		$this->response->header('Access-Control-Allow-Origin: *');
	}
	
	protected function _toJson($array){
		return json_encode($array);		
	}	
	
	public function profile( $action = '', $id = "" ){
		if($action == 'list'){
			//id is empty so list all profiles
		
			$ret = array();
			$profiles = $this->Profile->find('all');
		
			foreach($profiles as $profile){
				$ret[] = $profile['Profile'];
			}

			return $this->_toJson(array('success'=>true, 'data'=>$ret));
		}
		
		if($action == 'get'){
			$profile = $this->Profile->findById($id);
			
			if(!empty($profile)){
				return(array('success'=>true, 'data'=>$profile['Profile']));
			} 
			
			return $this->_toJson(array('success'=>false, 'message'=>'Unable to find profile.'));
		}
		
		if($action == 'save'){
			//get any existing data about the profile
			
			$data = $this->request->data;
			
			if(empty($data['_id']) && empty($data['name'])){
				return $this->toJason(false);
			}
			
			if(empty($data['_id'])){
				$id = $data['_id'] = Inflector::variable(Inflector::slug($data['name']));
			}
			
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
				return $this->_toJson(array('success'=>true, 'data'=>$data['Profile']));
			}
			
			return $this->_toJson(array('success'=>false, 'message'=>'Unable to save data.'));
		}
		
		if($action == 'delete'){
			$id = $data['_id'];
			
			
			
			$return = $this->Profile->delete($id);
			
			return $this->_toJson(array('success'=>false, 'message'=>'Invalid or no action specified.'));
		}

		return $this->_toJson(false);
	}
	
	public function user( $action = '', $id = "" ){
		if($action == "list"){
			//id is empty so list all users
				
			$ret = array();
			$users = $this->User->find('all');
				
			foreach($users as $user){
				$user = $user['User'];
				unset($user['password']);//never transmit (hashed) passwords over an open api
					
				$ret[] = $user;
			}
			
			return $this->_toJson(array('success'=>true, 'data'=>$ret));
		}
		
		if($action == "get"){			
			$user = $this->User->findById($id);
				
			if(!empty($user)){
				$this->_toJson(array('success' => 'false', 'data'=>$user['User']));
			}
				
			return $this->_toJson(array('success' => 'false', 'message'=>'Unable to locate user.'));
		}
	
		if(false || $this->request->isPost()){
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
	
		return $this->_toJson(array('success'=>false, 'message'=>'Invalid or no action specified.'));
	}
	
	public function block( $action = '',  $id = '' ){ 
		if($action == 'list'){
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
			
			return $this->_toJson(array('success'=>true, 'data'=>$blocks));
		}

		return $this->_toJson(array('success'=>false, 'message'=>'Invalid or no action specified.'));
	}
	
	public function live( $action = '', $profileId = '', $blockId = '' ){
		if($action == 'get'){
			if(empty($profileId) || empty($blockId)){
				return $this->_toJson(false);
			}		
			
			$profile = $this->Profile->findById($profileId);
			
			if(empty($profile)){
				return $this->_toJson(false);
			}
			
			$profile = $profile['Profile'];
			
			$block = $this->Block->findById($blockId);
			
			if(empty($block)){
				return $this->_toJson(false);
			}
			
			$block = $block['Block'];			
			foreach($profile['blocks'] as $userBlock){
				if($userBlock['_id'] == $blockId){
					$block = Hash::merge($block, $userBlock);
				}
			}
			
			
			
			$data = $this->Block->live($block);
			
			if(!empty($data)){			
				return $this->_toJson($data);
			}
		}

		return $this->_toJson(array('success'=>false, 'message'=>'Invalid or no action specified.'));
	}
}
