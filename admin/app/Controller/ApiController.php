<?php

class ApiController extends AppController {
	public $uses = array('Block', 'Users', 'Profiles', 'Logs');
	
	public function block(){
		$this->autoRender = false;
		
		#debug( $this->Block->find() );
		
		return "callback(" . json_encode(
			array(
				'counting' => array(
					array(
						'_id' => "countingMongodb",
						'type'=>"counting",
						'title'=>"Mongodb Collection Counting",
						'description'=>"Block for visualizing the size of collections and their indexes",
						'ttl'=>1000,
						'options'=>array( 
							'value1'=>'default',
							'value2'=>'default2',
						)
					),	
					array(
						'_id' => "countingBeanstalkd",
						'type'=>"counting",
						'title'=>"Beanstalkd Tube Counting",
						'description'=>"Block for visualizing the size of tubes and the workers that are watching them",
						'ttl'=>1000,
						'options'=>array(
								'value1'=>'default',
								'value2'=>'default2',
						)
					),
				),
			)
		) . ")";
	}	
}
