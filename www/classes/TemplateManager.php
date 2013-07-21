<?php

	class TemplateManager extends Auth {
		
		private function loadBlockResources() {
			
			$resource = '';
			
			if (isset($_GET['profileId']) && is_string($_GET['profileId'])) {
				
				$data = $this->getData('profile', 'list');
				$obj = json_decode($data);
				
				if (count($obj->data) > 0) {
					
					
					
					foreach ($obj->data as $profile) {
						
						if ($profile->_id == $_GET['profileId']) {
							
							foreach($profile->blocks as $block) {
								
								$fileContents = file_get_contents('js/blocks/'.$block->_id.'.js');
								
								$resource .= "\r\n".\JShrink\Minifier::minify($fileContents);
									
							}
						}
					}
				}
			}
			
			return '<script type="text/javascript" id="block-scripts-'.$_GET['profileId'].'">'.$resource.'</script>';
			
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
		
		public function gotoTemplate($tpl) {
			header("Location: $tpl");
		}
		
	}

?>