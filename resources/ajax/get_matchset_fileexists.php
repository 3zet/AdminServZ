<?php
	// JSON endpoint: PHP notices must never reach the response body (they still go to the error log)
	ini_set('display_errors', '0');
	// INCLUDES
	session_start();
	$configPath = '../../'.$_SESSION['adminserv']['path'].'config/';
	require_once $configPath.'adminserv.cfg.php';
	require_once '../core/adminserv.php';
	AdminServConfig::$PATH_RESOURCES = '../';
	AdminServ::getClass();
	
	// ISSET
	if( isset($_GET['path']) ){ $path = $_GET['path']; }else{ $path = null; }
	if( isset($_GET['name']) ){ $name = $_GET['name']; }else{ $name = null; }
	
	// CHECKING
	$out = false;
	if($path && $name){
		$struct = Folder::read($path);
		if( !strstr((string) $name, '.txt') ){
			$name = $name.'.txt';
		}
		
		if( count($struct['files']) > 0 ){
			foreach($struct['files'] as $fileName => $fileValues){
				if($name == $fileName){
					$out = true;
					break;
				}
			}
		}
	}
	
	// OUT
	echo json_encode($out);
?>