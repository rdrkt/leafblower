<?php

	abstract class Api {
		
		private $baseUrl = 'http://admin.leafblower.rdrkt.com/api/';
		
		protected function getData($request, $action, Array $params = []) {
			
			$ch = curl_init();
			
			//make sure data is returns not output
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			//set url.
			curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $request . '/' . $action);
			
			//if there's params, post to API
			if (!empty($params)) {
				curl_setopt($ch, CURLOPT_POST, count($params));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			}
			
			//get json feedback
			$json = curl_exec($curl);
			
			//close curl handler
			curl_close($ch); 
			
			//return retrieved json
			return $json;
		}
		
	}

?>