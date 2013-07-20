<?php

	class TemplateManager extends Auth {
		
		private function loadBlockResources() {
			//die($_GET['profileId']);
			if (isset($_GET['profileId']) && is_string($_GET['profileId'])) {
				$data = $this->getData('profile', 'list', ['id',mysql_real_escape_string($_GET['profileId'])]);
				var_dump($data);
				exit;
			}
			
		}
		
		public function loadTemplate() {
			
			//if the template is invalid or not specified, goto index
			if (!$this->isAuthenticated()) {
				$filename = 'auth';
			} elseif (!isset($_GET['url'])) {
				$filename = 'index';	
			} elseif (!file_exists('templates/'.$this->templateName.'/'.$_GET['url'].'.phtml')) {
				$filename = '404';	
			} else {				
				$filename = $_GET['url'];				
			}
			
			//load template.
			ob_start();
			require('templates/'.$this->templateName.'/'.$filename.'.phtml');
			$markup = ob_get_contents();
			ob_end_clean();
			
			return $markup;
		}
		
	}

?>