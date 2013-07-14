<?php

	require_once('classes/Api.php');
	require_once('classes/Auth.php');	
	$Auth = new Auth();
	
	$tplName = 'default';

	if (!isset($_GET['url']) || !file_exists('templates/'.$tplName.'/'.$_GET['url'].'.phtml')) {
		$filename = 'index';
	} else {
		$filename = $_GET['url'];
	}

	require('templates/'.$tplName.'/'.$filename.'.phtml');
	
?>