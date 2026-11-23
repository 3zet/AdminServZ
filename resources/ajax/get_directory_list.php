<?php
	// JSON endpoint: PHP notices must never reach the response body (they still go to the error log)
	ini_set('display_errors', '0');
	// INCLUDES
	session_start();
	if( !isset($_SESSION['adminserv']['sid']) ){ exit; }
	$configPath = '../../'.$_SESSION['adminserv']['path'].'config/';
	require_once $configPath.'adminlevel.cfg.php';
	require_once $configPath.'adminserv.cfg.php';
	require_once $configPath.'extension.cfg.php';
	require_once $configPath.'servers.cfg.php';
	require_once '../core/adminserv.php';
	AdminServConfig::$PATH_RESOURCES = '../';
	AdminServ::getClass();
	
	// DATA
	$out = [];
	if( AdminServ::initialize() ){
		$path = AdminServ::getMapsDirectoryPath();
		$out = Folder::getArborescence($path, AdminServConfig::$MAPS_HIDDEN_FOLDERS, substr_count($path, '/'));
	}
	
	// OUT
	$client->Terminate();
	echo json_encode($out);
?>