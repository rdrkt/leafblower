<?php
	
	//global define
	define('LEAFBLOWER', true);
	
	//classes
	require_once('classes/Config.php');
	require_once('classes/Api.php');
	require_once('classes/Auth.php');	
	require_once('classes/TemplateManager.php');
	
	//get auth manager and template manager.
	$templateManager = new TemplateManager;
	
	//output template
	echo $templateManager->loadTemplate();
?>