<?php
	session_start();
	require_once './adminserv.cfg.php';
	if( file_exists('./servers.cfg.php') ){
		require_once './servers.cfg.php';
	}
	AdminServConfig::$PATH_RESOURCES = '../'.AdminServConfig::$PATH_RESOURCES;
	require_once AdminServConfig::$PATH_RESOURCES .'core/adminserv.php';
	AdminServ::getClass();
	AdminServUI::lang();
	
	// Check that a config exists
	if( class_exists('ServerConfig') ){
		// If online configuration is allowed
		if( OnlineConfig::ACTIVATE === true ){
			// If a server is already configured
			if( AdminServServerConfig::hasServer() ){
				$allowRedirect = false;
				if( OnlineConfig::PASSWORD == null ){
					$allowRedirect = true;
				}
				else{
					// Test adress IP
					if(OnlineConfig::ADDRESS != null){
						if(OnlineConfig::ADDRESS === 'localhost'){
							if( Utils::isLocalhostIP() ){
								$allowRedirect = true;
							}
							else{
								$allowRedirect = false;
							}
						}
						else{
							if( $_SERVER['REMOTE_ADDR'] === OnlineConfig::ADDRESS ){
								$allowRedirect = true;
							}
							else{
								$allowRedirect = false;
							}
						}
					}
					// Test password
					if( isset($_POST['configcheckpassword']) ){
							if(md5((string) $_POST['checkPassword']) === OnlineConfig::PASSWORD){
								$allowRedirect = true;
							}
							else{
								$allowRedirect = false;
							}
						}
						else{
							// Remove existing sessions and start a new one
							session_unset();
							session_destroy();
							session_start();
							
							// Demande de password
							$_SESSION['adminserv']['check_password'] = true;
							Utils::redirection(false, '..');
						}
				}
			
				// Redirect to the pages
				if($allowRedirect){
					session_unset();
					session_destroy();
					session_start();
					$_SESSION['adminserv']['allow_config_servers'] = true;
					
					if( AdminServServerConfig::hasServer() === false ){
						Utils::redirection(false, '../?p=config-addserver');
					}
					else{
						Utils::redirection(false, '../?p=config-servers');
					}
				}
				else{
					AdminServ::error( Utils::t('You are not allowed to configure the servers') );
					Utils::redirection(false, '..');
				}
			}
			// Otherwise create the password
			else{
				if( isset($_POST['configsavepassword']) ){
					$password = md5((string) $_POST['savePassword']);
					if( ($result = AdminServServerConfig::savePasswordConfig('./adminserv.cfg.php', $password)) !== true ){
						AdminServ::error( Utils::t('Unable to save password.').' ('.$result.')');
						Utils::redirection(false, '..');
					}
					else{
						session_unset();
						session_destroy();
						session_start();
						$_SESSION['adminserv']['allow_config_servers'] = true;
						Utils::redirection(false, '../?p=config-addserver');
					}
				}
				else{
					// Create the password
					$_SESSION['adminserv']['get_password'] = true;
					Utils::redirection(false, '..');
				}
			}
		}
		else{
			AdminServ::info( Utils::t('The online configuration is disabled. Use "./config/servers.cfg.php" file.') );
			Utils::redirection(false, '..');
		}
	}
	else{
		Utils::redirection(false, '../?error='.urlencode( (string) Utils::t('The servers configuration file isn\'t recognized by AdminServ.') ) );
	}
?>
