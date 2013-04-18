<?php
class MongoSlowQueryBlock extends BaseBlock {
	public static function display($options){
		$db = new MongoClient("mongodb://{$options['host']}");
		$db = $db->selectDb($options['db']);
		
		$res = $db->system->profile->find(array('op'=>'query'))->sort(array('ts'=>-1))->limit(1);
		
		foreach($res as $r){
			return array_merge($r, array('host'=>$options['host']));
		}
		
		return array();
	}
}