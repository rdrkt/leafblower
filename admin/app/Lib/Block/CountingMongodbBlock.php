<?php
class CountingMongodbBlock extends BaseBlock {
	public static function display($options){
        $db = new MongoClient("mongodb://{$options['host']}");
		$db = $db->selectDb($options['db']);
		
		$collections = $db->listCollections();
		
		$ret = array();
		foreach($collections as $collection){
			$name = $collection->getName();
			$ret[] = array('_id' => $name, 'count' => $collection->count(), 'indexes' => $collection->getIndexInfo());
		}
				
		return $ret;
	}
}