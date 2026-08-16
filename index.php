<?php
    ob_start();
    // FIX FOR MAPBROWSER BY TOFFE
    function adminserv_strip_dotdot(&$value){
        if( is_array($value) ){
            foreach($value as $k => &$v){ adminserv_strip_dotdot($v); }
            unset($v);
        }
        elseif( is_string($value) && stristr($value, "../") ){
            $value = str_replace("../", "", $value);
        }
    }
    foreach ($_GET as $key => &$value) { adminserv_strip_dotdot($value); }
    unset($value);
    foreach ($_POST as $key => &$value) { adminserv_strip_dotdot($value); }
    unset($value);
	// INCLUDES
	session_start();
	require_once 'config/adminserv.cfg.php';
	require_once 'config/servers.cfg.php';
	require_once 'config/extension.cfg.php';
	require_once 'config/adminlevel.cfg.php';
	require_once AdminServConfig::$PATH_RESOURCES .'core/adminserv.php';
	
	// LOAD TIMER
	if(ADMINSERV_TIMER){
		AdminServ::startTimer();
	}
	
	// INITIALIZE
	AdminServ::checkPHPVersion('5.3.0');
	define('PATH_ROOT', basename(__DIR__).'/');
	$_SESSION['adminserv']['path'] = (AdminServConfig::MULTI_ADMINSERV) ? PATH_ROOT : null;
	AdminServ::getClass();
	
	// GLOBALS
	AdminServEvent::getArgs();
	
	// THEME
	define('USER_THEME', AdminServUI::theme($args['theme']));
	// DARK MODE body class
	$GLOBALS['body_class'] = (str_ends_with(USER_THEME, '-dark') ? 'dark' : '');
	
	// LANG
	define('USER_LANG', AdminServUI::lang($args['lang']));
	
	// PERMISSION CHECK
	$checkRightsList = ['./config/adminserv.cfg.php' => 666, './config/servers.cfg.php' => 666, './config/adminlevel.cfg.php' => 666];
	if( in_array(true, AdminServConfig::$LOGS) ){
		if (!Utils::isWinServer()) {
			$checkRightsList['./logs/'] = 777;
		}
	}
	AdminServ::checkRights($checkRightsList);
	
	// LOGOUT
	AdminServEvent::logout();
	
	// LOGS
	AdminServLogs::initialize();
	
	// PLUGINS
	define('USER_PLUGIN', AdminServPlugin::getCurrent());
	
	// INDEX
	unset($args['theme'], $args['lang']);
	if( AdminServEvent::isLoggedIn() ){
		
		// SWITCH SERVER
		AdminServEvent::switchServer();
		
		// SERVER CONNECTION
		if (AdminServ::initialize()) {
			// PAGES BACKOFFICE
			AdminServUI::initBackPage();
		}
	}
	else{
		// PAGES FRONTOFFICE
		AdminServUI::initFrontPage();
	}
?>
