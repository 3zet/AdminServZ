<?php

/**
* Class for event handling
*/
class AdminServEvent {
	
	/**
	* Return AdminServ's arguments
	*/
	public static function getArgs(): void{
		global $args;
		
		if( isset($_GET['p']) ){
			define('USER_PAGE', htmlspecialchars((string) $_GET['p']) );
		}
		else{
			if( isset($_SESSION['adminserv']['check_password']) || isset($_SESSION['adminserv']['get_password']) ){
				define('USER_PAGE', 'servers-online-config');
			}
			else{
				define('USER_PAGE', 'index');
			}
		}
		
		if( isset($_GET['c']) ){ $args['category'] = addslashes( htmlspecialchars((string) $_GET['c']) ); }else{ $args['category'] = null; }
		if( isset($_GET['view']) ){ $args['view'] = addslashes( htmlspecialchars((string) $_GET['view']) ); }else{ $args['view'] = null; }
		if( isset($_GET['i']) ){ $args['index'] = intval($_GET['i']); }else{ $args['index'] = -1; }
		if( isset($_GET['id']) ){ $args['id'] = intval($_GET['id']); }else{ $args['id'] = -1; }
		if( isset($_GET['d']) ){ $args['directory'] = addslashes( urldecode((string) $_GET['d']) ); }else{ $args['directory'] = null; }
		if( isset($_GET['th']) ){ $args['theme'] = addslashes((string) $_GET['th']); }else{ $args['theme'] = null; }
		if( isset($_GET['lg']) ){ $args['lang'] = addslashes((string) $_GET['lg']); }else{ $args['lang'] = null; }
	}
	
	
	/**
	* Allows switching servers
	*/
	public static function switchServer(): void{
		if( isset($_GET['switch']) && $_GET['switch'] != null ){
			$_SESSION['adminserv']['sid'] = AdminServServerConfig::getServerId($_GET['switch']);
			$_SESSION['adminserv']['name'] = $_GET['switch'];
			unset($_SESSION['adminserv']['teaminfo']);
			Utils::addCookieData('adminserv', [$_SESSION['adminserv']['sid'], Utils::readCookieData('adminserv', 1)], AdminServConfig::COOKIE_EXPIRE);
			
			if(USER_PAGE && USER_PAGE != 'index'){
				Utils::redirection(false, '?p='.USER_PAGE);
			}
			else{
				Utils::redirection();
			}
		}
	}
	
	
	/**
	* Check if we're connected to the server
	*/
	public static function isLoggedIn(){
		$out = false;
		
		if( isset($_SESSION['adminserv']['sid']) && isset($_SESSION['adminserv']['password']) && isset($_SESSION['adminserv']['adminlevel']) && !isset($_GET['error']) ){
			$out = true;
		}
		
		return $out;
	}
	
	
	/**
	* Disconnect
	*/
	public static function logout(): void{
		if( isset($_GET['error']) || isset($_GET['logout']) ){
			$adminservPath = $_SESSION['adminserv']['path'];
			session_unset();
			session_destroy();
			session_start();
			$_SESSION['adminserv']['path'] = $adminservPath;
			if( isset($_GET['logout']) ){
				Utils::redirection(false);
			}
		}
	}
}
?>