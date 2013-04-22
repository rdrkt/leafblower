<?php
App::uses('Queue', 'Job');
App::uses('BaseBlock', 'Block');

class Block extends AppModel {
	public function live($block){
		if(!empty($block)){
			$class = ucfirst($block["_id"]) . 'Block';
			
			App::uses($class, 'Block');
			
			if(class_exists($class)){
				$data = $class::display($block['options']);
				return $data;
			}
		}
		
		return false;
	}
}