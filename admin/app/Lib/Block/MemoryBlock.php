<?php
class MemoryBlock extends BaseBlock {
	public static function display($options){
		$output = exec("top -b | head | grep Mem");
	
		if(empty($output)){
			return array();
		}
	
		$output = str_replace(array("\n", "\r", "Mem:"), "", $output);
				
	
		$output = explode(",", $output);
	
		$tasks = array();
		foreach($output as $task){
			$task = trim($task);
			$task = explode(" ", $task);
				
			$name = $task[1];
			$value = $task[0];
				
			$tasks[$name] = $value;
		}
	
		return $tasks;
	}
}