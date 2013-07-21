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
			$jsonData = $this->getData('user', 'authenticate', array('username'=>$user,'password'=>sha1($password)));
			
			return $jsonData;
		}
		
		public function isAuthenticated() {
			//needs sorting.
			return true;
		}
		
		public function logout() {
			unset($_SESSION['authCode']);
			return true;
		}
		
	}


?>