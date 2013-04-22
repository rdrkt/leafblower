<?php
class MongoServerStatsBlock extends BaseBlock {
	public static function display($options){
		$db = new MongoClient("mongodb://{$options['host']}");
		$db = $db->selectDb($options['db']);
	
		$stats = $db->execute('db.serverStatus()');
		$stats = $stats['retval'];
		
		extract($stats);
		$ret = compact(array('host', 'uptime', 'connections', 'backgroundFlushing'));

		return $ret;
	}
}