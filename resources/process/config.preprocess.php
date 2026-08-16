<?php
	// SESSION
	if( !isset($_SESSION['adminserv']['allow_config_servers']) ){
		AdminServ::error( Utils::t('You are not allowed to configure the servers') );
		Utils::redirection();
	}
	
	// VERIFICATION
	if( class_exists('ServerConfig') ){
		// If online configuration isn't allowed
		if( OnlineConfig::ACTIVATE !== true ){
			AdminServ::info( Utils::t('No server available. To add one, configure "config/servers.cfg.php" file.') );
			Utils::redirection();
		}
		elseif (USER_PAGE != 'config-addserver') {
			if( OnlineConfig::ADD_ONLY === true ){
				Utils::redirection(false, './?p=addserver');
			}
		}
	}
	else{
		AdminServ::error( Utils::t('The servers configuration file isn\'t recognized by AdminServ.') );
		Utils::redirection();
	}
?>