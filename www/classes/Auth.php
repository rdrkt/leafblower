<?php

	class Auth extends Api {
				
		function __construct() {
			
			if (isset($_POST['txtUsername']) && isset($_POST['txtPassword'])) {
				
				$authCode = $this->checkAuth(mysql_real_escape_string($_POST['txtUsername']), mysql_real_escape_string($_POST['txtUsername']));
				
				if ($authCode) {
					session_start();
					$_SESSION['authCode'] = $authCode;
					header("Location: /");
				} else {
					$this->authError = 'Sorry, nope.';
				}
				
			}
				
		}
		
		private function checkAuth($user, $password) {
			
			//api call not written yet, return true.
			return 'sdkfjhchfh9ryco8q274ro862tnox87r2';
			
			$jsonData = $this->getData('user', 'authenticate', array('username'=>$user,'password'=>sha1($password)));
			
			
		}
		
		public function isAuthenticated() {
			//needs sorting.
			return true;
		}
		
	}


?>