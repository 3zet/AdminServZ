<?php

/**
* Class for AdminServ log processing
*/
class AdminServLogs {
	/**
	* Globals
	*/
	public static $LOGS_PATH = './logs/';
	
	/**
	* Initialize the logs (permission check, file creation)
	* @return bool
	*/
	public static function initialize(){
		$out = false;
		$error = null;
		
		if( in_array(true, AdminServConfig::$LOGS) ){
			if( file_exists(self::$LOGS_PATH) ){
				if( is_writable(self::$LOGS_PATH) ){
					$out = true;
				}
				else{
					$error = Utils::t('The folder "logs" is not writable.');
				}
			}
			else{
				$error = Utils::t('The folder "logs" does not exist.');
			}
		}
		
		if($out){
			if( count(AdminServConfig::$LOGS) > 0 ){
				foreach(AdminServConfig::$LOGS as $file => $activate){
					$path = self::$LOGS_PATH.$file.'.log';
					if($activate && !file_exists($path) ){
						if( File::save($path) !== true ){
							$error = Utils::t('Unable to create log file:').' '.$file.'.';
							$out = false;
							break;
						}
					}
				}
			}
		}
		
		if($error){
			if($_SESSION['error'] != null){
				$_SESSION['error'] .= '<br />'.$error;
			}
			else{
				$_SESSION['error'] = $error;
			}
		}
		
		return $out;
	}
	
	
	/**
	* Add a log to the corresponding file
	*
	* @param string $type -> Log type: access, action, etc
	* @param string $str  -> Log line to write
	* @return bool
	*/
	public static function add($type, $str){
		$out = false;
		$type = strtolower($type);
		if( defined('USER_PAGE') ){ $userPage = USER_PAGE; }else{ $userPage = 'index'; }
		if( defined('SERVER_NAME') ){ $serverName = '['.mb_convert_encoding(SERVER_NAME, 'ISO-8859-1').'] '; }else{ $serverName = null; }
		$str = '['.date('d/m/Y H:i:s').'] ['.$_SERVER['REMOTE_ADDR'].'] : '.$serverName.'['.$userPage.'] '.mb_convert_encoding($str, 'ISO-8859-1')."\n";
		$path = self::$LOGS_PATH.$type.'.log';
		
		if( file_exists($path) ){
			if( File::save($path, mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1') ) !== true ){
				$error = Utils::t('Unable to add log in file:').' '.$type.'.';
				if($_SESSION['error'] != null){
					$_SESSION['error'] .= '<br />'.$error;
				}
				else{
					$_SESSION['error'] = $error;
				}
			}
			else{
				$out = true;
			}
		}
		
		return $out;
	}
}
?>
