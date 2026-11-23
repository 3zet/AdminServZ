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
	
	// ISSET
	if( isset($_POST['cmd']) ){ $cmd = addslashes( htmlspecialchars((string) $_POST['cmd']) ); }else{ $cmd = null; }
	
	// SPEED ADMIN
	$out = false;
	if($cmd != null){
		if( AdminServ::initialize() ){
			$out = AdminServ::speedAdmin($cmd);
		}
	}
	
	// OUT
	$client->Terminate();
	echo json_encode($out);
?>