<?php

/**
* Class for admin level management
*/
class AdminServAdminLevel {
	
	/**
	* Constants
	*/
	private static array $DEFAULT_TYPE = ['SuperAdmin' => ['SuperAdmin', 'Admin', 'User'], 'Admin' => ['Admin', 'User'], 'User' => ['User']];
	private static array $DEFAULT_ACCESS = ['srvopts' => 'server_options', 'gameinfos' => 'game_infos', 'chat' => 'chat', 'maps-list' => 'maps_list', 'maps-local' => 'maps_local', 'maps-upload' => 'maps_upload', 'maps-order' => 'maps_order', 'maps-matchset' => 'maps_matchsettings', 'maps-creatematchset' => 'maps_create_matchsettings', 'link_access_server', 'plugins-list' => 'plugins_list', 'guestban' => 'guest_ban'];
	private static array $DEFAULT_PERMISSION = ['speed_admin', 'switch_server', 'player_kick', 'player_ban', 'player_guest', 'player_ignore', 'player_forcetospectator', 'player_forcetoplayer', 'player_changeteam', 'force_scores', 'cancel_vote', 'srvopts_general_name', 'srvopts_general_comment', 'srvopts_general_playerpassword', 'srvopts_general_spectatorpassword', 'srvopts_general_nbplayers', 'srvopts_general_nbspectators', 'srvopts_advanced', 'srvopts_adminlevelpassword', 'srvopts_importexport', 'gameinfos_general_gamemode', 'gameinfos_general_options', 'gameinfos_general_warmup', 'gameinfos_teams_options', 'gameinfos_gamemode_options', 'chat_sendmessage', 'maps_list_moveaftercurrent', 'maps_list_removetolist', 'maps_local_add', 'maps_local_insert', 'maps_local_download', 'maps_local_rename', 'maps_local_move', 'maps_local_delete', 'maps_upload_add', 'maps_upload_insert', 'maps_upload_folder', 'maps_matchsettings_save', 'maps_matchsettings_load', 'maps_matchsettings_add', 'maps_matchsettings_insert', 'maps_matchsettings_edit', 'maps_matchsettings_delete', 'folder_new', 'folder_rename', 'folder_move', 'folder_delete', 'guestban_addplayer', 'guestban_removeplayer', 'guestban_playlist_new', 'guestban_playlist_save', 'guestban_playlist_load', 'guestban_playlist_delete'];
	private static string $CONFIG_PATH = './config/';
	private static string $CONFIG_FILENAME = 'adminlevel.cfg.php';
	private static string $CONFIG_START_TEMPLATE = "<?php\nclass AdminLevelConfig {\n\tpublic static \$ADMINLEVELS = array(\n\t\t/********************* ADMINLEVELS CONFIGURATION *********************/\n\t\t\n";
	private static string $CONFIG_END_TEMPLATE =  "\t);\n}\n?>";
	
	
	/**
	* Check if there's an admin levels config
	*
	* @param string $levelName -> Test if the admin level exists
	* @return bool
	*/
	public static function hasLevel($levelName = null) {
		$out = false;
		
		if (class_exists('AdminLevelConfig')) {
			$adminLevelList = AdminLevelConfig::$ADMINLEVELS;
			
			if (isset($adminLevelList) && !empty($adminLevelList)) {
				if ($levelName) {
					foreach ($adminLevelList as $adminLevelName => $adminLevelData) {
						if ($adminLevelName === $levelName) {
							$out = true;
							break;
						}
					}
				}
				else {
					$out = true;
				}
			}
		}
		
		return $out;
	}
	
	
	/**
	* Check if the admin level has access to the page
	*
	* @param string $access    -> Access name
	* @param string $levelName -> Admin level name
	* @return bool
	*/
	public static function hasAccess($access, $levelName = null) {
		$out = false;
		
		// If the requested access is a page name
		$defaultAccess = self::getDefaultAccess();
		if (array_key_exists($access, $defaultAccess)) {
			foreach ($defaultAccess as $pageName => $accessName) {
				if ($access == $pageName) {
					$access = $accessName;
					break;
				}
			}
		}
		if ($levelName === null && defined('USER_ADMINLEVEL')) {
			$levelName = USER_ADMINLEVEL;
		}
		$levelData = self::getData($levelName, 'access');
		if (!empty($levelData) && in_array($access, $levelData)) {
			$out = true;
		}
		
		return $out;
	}
	
	
	/**
	* Check if the admin level has permission to use a feature
	*
	* @param mixed  $permission -> Permission name or array of multiple permissions
	* @param string $levelName  -> Admin level name
	* @param string $levelType  -> Minimum level type to allow
	* @return bool
	*/
	public static function hasPermission($permission, $levelName = null, $levelType = null) {
		$out = false;
		if ($levelName === null && defined('USER_ADMINLEVEL')) {
			$levelName = USER_ADMINLEVEL;
		}
		
		if ($levelName) {
			if ($levelType !== null) {
				$levelData = self::getData($levelName, 'adminlevel');
				$levelTypeAuthorized = self::getDefaultType($levelData['type']);
				if (!in_array($levelType, $levelTypeAuthorized)) {
					return false;
				}
			}
			$levelData = self::getData($levelName, 'permission');
			
			if (!empty($levelData)) {
				if (is_array($permission)) {
					$result = [];
					foreach ($permission as $perm) {
						if (in_array($perm, $levelData)) {
							$result[] = true;
						}
						else {
							$result[] = false;
						}
					}
					if (in_array(true, $result)) {
						$out = true;
					}
				}
				else {
					if (in_array($permission, $levelData)) {
						$out = true;
					}
				}
			}
		}
		
		return $out;
	}
	
	
	/**
	* Get the default levels or return the allowed levels depending on the type
	*
	* @param string $type -> Level type
	* @return array
	*/
	public static function getDefaultType($levelType = null){
		$out = [];
		
		if ($levelType === null) {
			$out = array_keys(self::$DEFAULT_TYPE);
		}
		else {
			foreach (self::$DEFAULT_TYPE as $defaultTypeLevel => $defaultAuthorizedLevel) {
				if ($levelType === $defaultTypeLevel) {
					$out = $defaultAuthorizedLevel;
					break;
				}
			}
		}
		
		return $out;
	}
	public static function getDefaultAccess(){
		return self::$DEFAULT_ACCESS;
	}
	public static function getDefaultPermission(){
		return self::$DEFAULT_PERMISSION;
	}
	
	
	/**
	* Get the config data for an admin level
	*
	* @param string $levelName -> Admin level name
	* @param string $fieldName -> Name of the data field to return
	* @return array
	*/
	public static function getData($levelName = null, $fieldName = 'all') {
		$out = [];
		if ($levelName === null && defined('USER_ADMINLEVEL')) {
			$levelName = USER_ADMINLEVEL;
		}
		
		if (self::hasLevel($levelName)) {
			$levelData = AdminLevelConfig::$ADMINLEVELS[$levelName];
			
			if ($fieldName != 'all') {
				if (isset($levelData[$fieldName])) {
					$out = $levelData[$fieldName];
				}
			}
			else {
				$out = $levelData;
			}
		}
		
		return $out;
	}
	
	
	/**
     * Get the position of the admin level in the config file
     *
     * @param string $levelName -> Admin level name
     */
    public static function getId($levelName = null): int {
		$id = 0;
		$levelList = self::getStaticList();
		if ($levelName === null && defined('USER_ADMINLEVEL')) {
			$levelName = USER_ADMINLEVEL;
		}
		
		// Look for the level's position from its name
		if (!empty($levelList)) {
			foreach ($levelList as $levelListName) {
				if ($levelListName == $levelName) {
					break;
				}
				else {
					$id++;
				}
			}
		}
		
		// If id = total number of levels -> not found
		return ($id == count($levelList)) ? -1 : $id;
	}
	
	
	/**
	* Get the type from the name
	*
	* @param string $levelName -> Admin level name
	* @return string
	*/
	public static function getType($levelName = null) {
		$out = null;
		if ($levelName === null && defined('USER_ADMINLEVEL')) {
			$levelName = USER_ADMINLEVEL;
		}
		
		$levelData = self::getData($levelName, 'adminlevel');
		if (isset($levelData['type']) && $levelData['type'] != null) {
			$out = $levelData['type'];
		}
		
		return $out;
	}
	
	
	/**
	* Get the name from the type
	*
	* @param int $levelId -> Position number in the config
	* @return string
	*/
	public static function getName($levelId) {
		$out = null;
		$levelList = self::getStaticList();
		
		if (!empty($levelList)) {
			$i = 0;
			foreach ($levelList as $levelListName) {
				if ($i == $levelId) {
					$out = $levelListName;
					break;
				}
				$i++;
			}
		}
		
		return $out;
	}
	
	
	/**
	* Determine if the user level is at least of type $levelType
	*
	* @param string $levelType -> Minimum admin level type
	* @return bool
	*/
	public static function isType($levelType, $levelName = null) {
		$out = false;
		if ($levelName === null && defined('USER_ADMINLEVEL')) {
			$levelName = USER_ADMINLEVEL;
		}
		
		$levelData = self::getData($levelName, 'adminlevel');
		$levelTypeAuthorized = self::getDefaultType($levelData['type']);
		if (in_array($levelType, $levelTypeAuthorized)) {
			$out = true;
		}
		
		return $out;
	}
	
	
	/**
     * Get the list of admin levels configured for the current server
     *
     * @param string $server -> Server name
     */
    public static function getServerList($serverName = null): array {
		$out = [];
		if ($serverName === null && defined('SERVER_NAME')) {
			$serverName = SERVER_NAME;
		}
		
		if (AdminServServerConfig::hasServer($serverName)) {
			$levels = self::getStaticList();
			if (!empty($levels)) {
				foreach ($levels as $levelName) {
					$levelType = self::getType($levelName);
					foreach (ServerConfig::$SERVERS[$serverName]['adminlevel'] as $serverLevelType => $serverLevelTypeAccess) {
						if ($levelType === $serverLevelType) {
							if ($serverLevelType != null && $serverLevelTypeAccess != 'none') {
								if (self::userAllowed($levelName, $serverName)) {
									$out['levels'][] = $levelName;
								}
							}
						}
					}
				}
			}
		}
		
		$out['last'] = Utils::readCookieData('adminserv', 1);
		
		return $out;
	}
	
	
	/**
	* Get the list of configured admin levels
	*/
	public static function getStaticList(){
		$out = [];
		
		if (self::hasLevel()) {
			foreach (AdminLevelConfig::$ADMINLEVELS as $levelName => $levelData) {
				$out[] = $levelName;
			}
		}
		
		return $out;
	}
	
	
	/**
	* Check if the user's IP is allowed in the admin level
	*
	* @param string $level  -> Admin level name
	* @param string $server -> Server name
	* @return bool
	*/
	public static function userAllowed($levelName, $serverName = null) {
		$out = false;
		if ($serverName === null && defined('SERVER_NAME')) {
			$serverName = SERVER_NAME;
		}
		
		if (AdminServServerConfig::hasServer($serverName)){
			$levelType = self::getType($levelName);
			if ($levelType) {
				$serverLevelTypeAccess = ServerConfig::$SERVERS[$serverName]['adminlevel'][$levelType];
				
				if (is_array($serverLevelTypeAccess)) {
					if (in_array($_SERVER['REMOTE_ADDR'], $serverLevelTypeAccess)) {
						$out = true;
					}
				}
				else {
					if ($serverLevelTypeAccess === 'all') {
						$out = true;
					}
					elseif ($serverLevelTypeAccess === 'none') {
						$out = false;
					}
					else {
						$out = Utils::isLocalhostIP();
					}
				}
			}
		}
		
		return $out;
	}
	
	
	/**
	* Create the admin level template
	*
	* @param array $levelData -> assoc array(adminlevel => array(type), access, permission)
	*/
	public static function getTemplate($levelData): string{
		$out = "\t\t'".$levelData['name']."' => array(\n"
			."\t\t\t'adminlevel' => array(\n";
				foreach ($levelData['adminlevel'] as $adminlevelName => $adminlevelData) {
					$out .= "\t\t\t\t'$adminlevelName' => '$adminlevelData',\n";
				}
			$out .= "\t\t\t),\n"
			."\t\t\t'access' => array(\n";
				if (!empty($levelData['access'])) {
					foreach ($levelData['access'] as $accessName) {
						$out .= "\t\t\t\t'$accessName',\n";
					}
				}
			$out .= "\t\t\t),\n"
			."\t\t\t'permission' => array(\n";
				if (!empty($levelData['permission'])) {
					foreach ($levelData['permission'] as $permissionName) {
						$out .= "\t\t\t\t'$permissionName',\n";
					}
				}
		
		return $out . ("\t\t\t),\n" . "\t\t),\n");
	}
	
	
	/**
	* Save the admin levels config file
	*
	* @param array $levelData -> assoc array(adminlevel => array(type), access, permission)
	* @param int   $editLevel -> Id of the level to edit
	* @param array $levelList -> List of levels from the config
	* @return bool or string error
	*/
	public static function saveConfig($levelData = [], $editLevel = -1, $levelList = []) {
		// List of levels
		$levels = (isset($levelList) && !empty($levelList)) ? $levelList : AdminLevelConfig::$ADMINLEVELS;
		
		$fileTemplate = self::$CONFIG_START_TEMPLATE;
		$i = 0;
		foreach ($levels as $levelName => $levelValues) {
			// Edit
			if ($i == $editLevel && isset($levelData) && !empty($levelData)) {
				$fileTemplate .= self::getTemplate($levelData);
			}
			else {
				// Retrieve existing level data
				$getLevelsData = ['name' => $levelName, 'adminlevel' => $levelValues['adminlevel'], 'access' => $levelValues['access'], 'permission' => $levelValues['permission']];
				
				// Add data to the template
				$fileTemplate .= self::getTemplate($getLevelsData);
			}
			$i++;
		}
		
		// Add a new one
		if ($editLevel === -1 && isset($levelData) && !empty($levelData)) {
			if (self::getId($levelData['name']) === -1) {
				$fileTemplate .= self::getTemplate($levelData);
			}
			else {
				return Utils::t('The admin level already exist! Change the name.');
			}
		}
		$fileTemplate .= self::$CONFIG_END_TEMPLATE;
		
		// Save
		return File::save(self::$CONFIG_PATH.self::$CONFIG_FILENAME, $fileTemplate, false);
	}
}
?>