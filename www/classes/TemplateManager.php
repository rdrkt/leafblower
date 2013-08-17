<?php

	class TemplateManager extends Auth {
		
		private $errorOutput = '';
		private $cssString = '';

		private function loadBlockResources() {
			
			$resource = '';
			
			if (isset($_GET['profileId']) && is_string($_GET['profileId'])) {
				
				$data = $this->getData('profile', 'list');
				$obj = json_decode($data);
				
				if (isset($obj->data) && count($obj->data) > 0) {
					
					foreach ($obj->data as $profile) {
						
						if ($profile->_id == $_GET['profileId']) {
							
							foreach($profile->blocks as $block) {
								
								$fileContents = file_get_contents('js/blocks/'.$block->_id.'.js');
								
								$resource .= "\r\n".\JShrink\Minifier::minify($fileContents);
								
							}
						}
					}
				} else {
					$this->errorOutput .= '<li>Sorry, the profile you\'re viewing appears to be currently unavailable. Please check your system administrator.</li>';
				}
			}
			
			return '<script type="text/javascript" id="block-scripts-'.$_GET['profileId'].'">'.$resource.'</script>';
			
		}

        private function minifyCSS($str) {

                $re1 = <<<'EOS'
                    (?sx)("(?:[^"\\]++|\\.)*+"| '(?:[^'\\]++|\\.)*+')|/\* (?> .*? \*/ )
EOS;

                $re2 = <<<'EOS'
                    (?six)("(?:[^"\\]++|\\.)*+"| '(?:[^'\\]++|\\.)*+')|\s*+ ; \s*+ ( } ) \s*+|\s*+ ( [*$~^|]?+= | [{};,>~+-] | !important\b ) \s*+|( [[(:] ) \s++|\s++ ( [])] )|\s++ ( : ) \s*+(?!(?>[^{}"']++| "(?:[^"\\]++|\\.)*+"| '(?:[^'\\]++|\\.)*+')*+{)|^ \s++ | \s++ \z|(\s)\s+
EOS;

                $str = preg_replace("%$re1%", '$1', $str);
                return preg_replace("%$re2%", '$1$2$3$4$5$6$7', $str);

        }

        private function addCSS($cssFile) {
            if (file_exists(__DIR__.'/../'.$cssFile)) {
                $this->cssString .= $this->minifyCSS(file_get_contents(__DIR__.'/../'.$cssFile)) . "/r/n";
            } else {
                die('Invalid CSS entered, cannot be found. :: '.__DIR__.'/../'.$cssFile);
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

            $markup = str_ireplace('</head>', '<style>'.$this->cssString.'</style></head>', $markup);

			return $markup;
		}
		
		public function gotoTemplate($tpl) {
			header("Location: $tpl");
		}
		
	}

?>