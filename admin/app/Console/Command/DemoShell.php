<?php
Configure::write('debug', 0);
error_reporting(0);
 
class DemoShell extends AppShell {
	public $uses = array('Block');
	
	private function _queue(){		
		$tubes = array("orange", "green", "yellow", "beige", "rainbow", "black", "red", "lavendar", "almond");
			
		$queue = ClassRegistry::init('Queue.Job');
		
		$tube = array_rand($tubes);
		$tube = $tubes[$tube];
		
		$number = rand(1, 10);
		

		echo "Adding {$number} to {$tube}.\n\n";
		
		
		for($i=0; $i < $number; $i++){
			$queue->put(array('data'=>1, 'Job'=>array()), array('tube' => $tube));
		}
	}
	
	private function _mongodb(){
		$db = new Mongo("mongodb://localhost");
		$db = $db->selectDb('demo');
		
		$animals = array('lions', 'tigers', 'llamas', 'monkeys', 'bears', 'giraffes', 'sheep', 'pumas');
		
		$animal = array_rand($animals);
		$animal = $animals[$animal];
		
		$number = rand(1, 10);
		
		echo "Adding {$number} to {$animal}.\n\n";
		
		for($i=0; $i < $number; $i++){			
			$db->$animal->save(array("a"=>1));
		}
	}
	
	public function main(){
		$functions = array('_queue', '_mongodb');
		
		while(1){
			$function = array_rand($functions);
			
			$function=$functions[$function];
			
			$this->$function();
			
			$sleep = rand(1, 1000);
			
			echo "Sleep for " . $sleep / 1000 . " seconds\n";
			
			usleep($sleep * 1000);
		}	
		
	}
}
