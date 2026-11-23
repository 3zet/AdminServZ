<?php
/*
* General utility class
*/
abstract class Utils {
	
	/**
	* Redirection
	*
	* @param bool   $auto  -> Automatic mode, redirects to the current page
	* @param string $page  -> Page to redirect to
	* @param int    $sleep -> Time in seconds to wait before running the redirect
	*/
	public static function redirection($auto = true, $page = null, $sleep = 0): void{
		if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != null){ $protocol = 'https'; }
		else{ $protocol = 'http'; }
		$host = $_SERVER['HTTP_HOST'];
		$uri  = rtrim(dirname((string) $_SERVER['PHP_SELF']), '/\\');
		if($auto){
			$page = basename((string) $_SERVER['PHP_SELF']);
			if($page == 'index.php'){
				$page = null;
			}
		}
		
		// Redirection
		if($sleep !== 0){
			header("Refresh: $sleep; URL=$protocol://$host$uri/$page");
		}
		else{
			header("Location: $protocol://$host$uri/$page");
		}
		exit;
	}
	
	
	/**
	* Replace a text URL with a clickable URL
	*
	* @param string $str -> The string containing one or more URLs
	* @param bool $bbcode -> Converts to HTML by default, true for BBcode
	* @return the string with URLs replaced
	*/
	public static function replaceTextURL($str, $bbcode = false){
		$regex = '$(?:https?|ftp)://(?:www\.|ssl\.)?[a-z0-9._%-]+(?:/[a-z0-9._/%-]*(?:\?[a-z0-9._/%-]+=[a-z0-9._/%+-]+(?:&(?:amp;)?[a-z0-9._/%-]+=[a-z0-9._/%+-]+)*)?)?(?:#[a-z0-9._-]*)?$i';
		
		if($bbcode){
			$code = '[url]$0[/url]';
		}
		else{
			$code = '<a href="$0">$0</a>';
		}
		
		return preg_replace($regex, $code, $str);
	}
	
	
	/**
	* Check if the email address is in a valid format
	*
	* @param string $email -> The email address to test
	* @return bool
	*/
	public static function isValidEmail($email){
		$out = false;
		$regex = '#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,5}$#';
		
		if( preg_match($regex, $email) ){
			$out = true;
		}
		
		return $out;
	}
	
	
	/**
	* Check if the phone number is in a valid format
	*
	* @param string $tel -> The phone number to test
	* @return bool
	*/
	public static function isValidTel($tel){
		$out = false;
		$regex = '#^0[0-9]([ .-]?[0-9]{2}){4}$#';
		
		if( preg_match($regex, $tel) ){
			$out = true;
		}
		
		return $out;
	}
	
	
	/**
	* Return the Captcha image
	*
	* @require showcaptcha.php in a folder "./includes/captcha/"
	*/
	public static function showCaptcha(){
		// Character set used to build the code
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
		$code = null;
		for($i = 0; $i < 4; $i++) {
			$code .= $chars[mt_rand(0, 35)];
		}
		// Store the code in the session
		$_SESSION['captcha'] = $code;
		session_write_close();
		return '<img src="./includes/captcha/showcaptcha.php" height="40" width="145" alt="Impossible d\'afficher le code !" />';
	}
	
	
	/**
	* Read data from a cookie
	*
	* @param string $cookie_name -> The cookie name
	* @param int    $data_pos    -> Position of the data to return, null for all data
	* @return string if a position is chosen, otherwise returns an array of all the data
	*/
	public static function readCookieData($cookie_name, $data_pos = null){
		$separator = '|';
		
		if( isset($_COOKIE[$cookie_name]) ){
			$out =  explode($separator, (string) $_COOKIE[$cookie_name]);
			
			if( is_numeric($data_pos) ){
				if( isset($out[$data_pos]) && $out[$data_pos] != null){
					return $out[$data_pos];
				}
			}
			else{
				if($out[0] != null){
					return $out;
				}
			}
		}else{
			return null;
		}
	}
	
	
	/**
	* Add data to a cookie
	*
	* @param string $cookieName   -> The cookie name
	* @param array  $data         -> The cookie data
	* @param int    $cookieExpire -> The number of days before the cookie expires
	* @return true if the cookie was written successfully, otherwise false
	*/
	public static function addCookieData($cookieName, array $data, $cookieExpire = 15){
		$separator = '|';
		
		$newCookieData = null;
		for($i = 0; $i < count($data); $i++){
			if($i == count($data)-1){
				$newCookieData .= $data[$i];
			}
			else{
				$newCookieData .= $data[$i].$separator;
			}
		}
		
		if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != null){ $isHttps = true; }
		else{ $isHttps = false; }
		if( setcookie($cookieName, (string) $newCookieData, ['expires' => time()+60*60*24*$cookieExpire, 'path' => '/', 'secure' => $isHttps, 'samesite' => 'Lax']) ){
			return true;
		}
		else{
			return false;
		}
	}
	
	
	/**
	* Get the visitor's browser language
	*
	* @param string $forceLang -> Force use of the language
	* @return $_SESSION['lang']
	*/
	public static function getLang($forceLang = null){
		if($forceLang){
			$_SESSION['lang'] = $forceLang;
		}
		else{
			if( !isset($_SESSION['lang']) ){
				$_SESSION['lang'] = substr((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en'), 0, 2);
			}
		}
		
		return $_SESSION['lang'];
	}
	
	
	/**
	* Return the term's value
	*
	* @param array $args -> Arguments in the key. Ex: t('key word n°!numero', array('!numero' => $numero) );
	*/
	public static function t($key, $args = [] ){
		global $translate;
		$out = $key;
		
		if( isset($_SESSION['lang']) && $_SESSION['lang'] !== 'en' ){
			if( isset($translate[$key]) ){
				$out = $translate[$key];
			}
		}
		
		if( count($args) > 0 ){
			$out = str_replace( array_keys($args), array_values($args), $out);
		}
		
		return $out;
	}
	
	
	/**
	* Get the visitor's internet browser
	*/
	public static function getBrowser(){
		$out = null;
		$http_user_agent = $_SERVER['HTTP_USER_AGENT'];
		
		if( strstr((string) $http_user_agent, 'Firefox') ){ $out = 'Firefox'; }
		else if( strstr((string) $http_user_agent, 'Chrome') ){ $out = 'Chrome'; }
		else if( strstr((string) $http_user_agent, 'Opera') ){ $out = 'Opera'; }
		else if( strstr((string) $http_user_agent, 'MSIE') ){ $out = 'IE'; }
		else if( strstr((string) $http_user_agent, 'Safari') ){ $out = 'Safari'; }
		else if( strstr((string) $http_user_agent, 'Konqueror') ){ $out = 'Konqueror'; }
		else if( strstr((string) $http_user_agent, 'Netscape') ){ $out = 'Netscape'; }
		else{ $out = 'Others'; }
		
		return $out;
	}
	
	
	/**
	* Get the visitor's operating system
	*/
	public static function getOperatingSystem(){
		$out = null;
		$http_user_agent = $_SERVER['HTTP_USER_AGENT'];
		
		if( strstr((string) $http_user_agent, 'Win') ){ $out = 'Windows'; }
		else if( (strstr((string) $http_user_agent, 'Mac')) || (strstr('PPC', (string) $http_user_agent)) ){ $out = 'Mac'; }
		else if( strstr((string) $http_user_agent, 'Linux') ){ $out = 'Linux'; }
		else if( strstr((string) $http_user_agent, 'FreeBSD') ){ $out = 'FreeBSD'; }
		else if( strstr((string) $http_user_agent, 'SunOS') ){ $out = 'SunOS'; }
		else if( strstr((string) $http_user_agent, 'IRIX') ){ $out = 'IRIX'; }
		else if( strstr((string) $http_user_agent, 'BeOS') ){ $out = 'BeOS'; }
		else if( strstr((string) $http_user_agent, 'OS/2') ){ $out = 'OS/2'; }
		else if( strstr((string) $http_user_agent, 'AIX') ){ $out = 'AIX'; }
		else{ $out = 'Others'; }
		
		return $out;
	}
	
	
	/**
	* Determine if the server is running Windows, defaults to Unix
	*
	* @return bool
	*/
	public static function isWinServer(){
		if( stripos(PHP_OS, 'WIN') === 0 ){
			return true;
		}
		else{
			return false;
		}
	}
	
	
	/**
	* Determine if the visitor's IP address is in the local network
	*
	* @param string $addr -> The IP address to use
	* @return bool
	*/
	public static function isLocalhostIP($addr = null){
		if($addr){
			$userIPEx = explode('.', $addr);
		}
		else{
			$userIPEx = explode('.', (string) $_SERVER['REMOTE_ADDR']);
		}
		
		return true;
	}
}
?>