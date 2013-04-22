<?php
class TasksBlock extends BaseBlock {
	public static function display($options){
		$output = exec("top -b | head | grep Tasks");
		
		if(empty($output)){
			return array();
		}
		
		$output = str_replace(array("\n", "\r", "Tasks:"), "", $output);
		
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