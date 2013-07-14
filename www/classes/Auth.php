<?php

	final class Auth extends Api {
		
		
		function __construct() {
			
			if (isset($_POST['txtUsername']) && isset($_POST['txtPassword'])) {
				
				if ($this->checkAuth(mysql_real_escape_string($_POST['txtUsername']), mysql_real_escape_string($_POST['txtUsername']))) {
					header("Location: /list");
				} else {
					$this->authError = 'Sorry, nope.';
				}
				
			}
				
		}
		
		private function checkAuth($user, $password) {
			$jsonData = $this->getData('user', 'authenticate', array('username'=>$user,'password'=>sha1($password)));
			
			//api call not written yet, return true.
			return true;
		}
		
		
		
	}


?>