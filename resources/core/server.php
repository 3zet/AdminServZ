<?php

/**
* Class for server config management
*/
class AdminServServerConfig {
	
	/**
	* Constants
	*/
	private static string $CONFIG_PATH = './config/';
	private static string $CONFIG_FILENAME = 'servers.cfg.php';
	private static string $CONFIG_START_TEMPLATE = "<?php\nclass ServerConfig {\n\tpublic static \$SERVERS = array(\n\t\t/********************* SERVER CONFIGURATION *********************/\n\t\t\n";
	private static string $CONFIG_END_TEMPLATE =  "\t);\n}\n?>";
	
	
	/**
	* Determine if at least one server is available
	*
	* @param string $serverName -> Test if the server exists
	* @return bool
	*/
	public static function hasServer($serverName = null){
		$out = false;
		
		if (class_exists('ServerConfig')) {
			$serverList = ServerConfig::$SERVERS;
			
			if (isset($serverList) && !empty($serverList) && !isset($serverList['new server name']) && !isset($serverList[''])) {
				if ($serverName) {
					foreach ($serverList as $serverListName => $serverListData) {
						if ($serverListName === $serverName) {
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
	* Get a server's data
	*
	* @param string $serverName -> The server name in the config
	* @return array
	*/
	public static function getServer($serverName){
		$out = null;
		
		if( self::hasServer() ){
			if( isset(ServerConfig::$SERVERS[$serverName]) ){
				$out = ServerConfig::$SERVERS[$serverName];
			}
			else{
				$out = Utils::t('This server does not exist');
			}
		}
		else{
			$out = Utils::t('No server available');
		}
		
		return $out;
	}
	
	
	/**
     * Return the server's identifier in the config
     *
     * @param  string $serverName -> The server name in the config
     */
    public static function getServerId($serverName): int{
		$id = 0;
		$servers = ServerConfig::$SERVERS;
		$countServers = count($servers);
		
		// Look for the server's position from its name
		if( $countServers > 0 ){
			foreach($servers as $server_name => $server_values){
				if($server_name == $serverName){
					break;
				}
				else{
					$id++;
				}
			}
		}
		
		// If id = total number of servers -> not found
		if($id == $countServers ){
			return -1;
		}
		else{
			return $id;
		}
	}
	
	
	/**
	* Return the server name in the config
	*
	* @param  int $serverId -> The server's id in the config
	* @return string
	*/
	public static function getServerName($serverId){
		$out = null;
		$servers = ServerConfig::$SERVERS;
		
		if( count($servers) > 0 ){
			$i = 0;
			foreach($servers as $serverName => $serverValues){
				if($i == $serverId){
					$out = $serverName;
					break;
				}
				else{
					$i++;
				}
			}
		}
		
		return $out;
	}
	
	
	/**
	* Create the server template
	*
	* @param array $serverData -> assoc array(name, address, port, mapsbasepath, matchsettings, adminlevel => array(SuperAdmin, Admin, User));
	*/
	public static function getServerTemplate(array $serverData): string{
		$out = "\t\t'".$serverData['name']."' => array(\n"
			."\t\t\t'address'\t\t=> '".$serverData['address']."',\n"
			."\t\t\t'port'\t\t\t=> ".$serverData['port'].",\n"
            ."\t\t\t'ds_pw'\t\t\t=> '".$serverData['ds_pw']."',\n"
			."\t\t\t'mapsbasepath'\t=> '".$serverData['mapsbasepath']."',\n"
			."\t\t\t'matchsettings'\t=> '".$serverData['matchsettings']."',\n"
			."\t\t\t'adminlevel'\t=> array(";
			$adminlevelId = 0;
			$adminlevelCount = count($serverData['adminlevel']);
			foreach($serverData['adminlevel'] as $admLvlId => $admLvlValue){
				$out .= "'$admLvlId' => ";
				if( is_array($serverData['adminlevel'][$admLvlId]) ){
					$out .= "array('".implode("', '", str_replace(' ', '', $serverData['adminlevel'][$admLvlId]))."')";
				}
				else{
					$out .= "'".$serverData['adminlevel'][$admLvlId]."'";
				}
				
				if($adminlevelId < $adminlevelCount-1){
					$out .= ', ';
				}
				$adminlevelId++;
			}
		
		return $out . ")\n\t\t),\n";
	}
	
	
	/**
	* Save the server config password
	*
	* @param string $filename -> Path to the config file
	* @param string $password -> The password to write
	* @return true or text error
	*/
	public static function savePasswordConfig($filename, $password){
		$out = false;
		$seek = null;
		
		// Retrieve pointer
		$search = 'const PASSWORD = \'';
		$seek = File::getSeekFromString($filename, $search, true);
		
		// Write to file
		if($seek !== null){
			if( ($result = File::saveAtSeek($filename, $password, $seek)) !== true ){
				$out = $result;
			}
			else{
				$out = true;
			}
		}
		
		return $out;
	}
	
	
	/**
	* Save the servers config file
	*
	* @param array $serverData -> assoc array(name, address, port, matchsettings, adminlevel => array(SuperAdmin, Admin, User));
	* @param int   $editServer -> Id of the server to edit
	* @param array $serverList -> List of servers from the config
	* @return bool or string error
	*/
	public static function saveServerConfig(array $serverData = [], $editServer = -1, $serverList = [] ){
		// List of servers
		if( isset($serverList) && count($serverList) > 0 ){
			$servers = $serverList;
		}else{
			$servers = ServerConfig::$SERVERS;
		}
		
		$fileTemplate = self::$CONFIG_START_TEMPLATE;
		$i = 0;
		foreach($servers as $serverName => $serverValues){
			// Edit
			if($i == $editServer && isset($serverData) && count($serverData) > 0 ){
				$fileTemplate .= self::getServerTemplate($serverData);
			}
			else{
				// Retrieve existing server data
				$getServerValues = ['name' => $serverName, 'address' => $serverValues['address'], 'port' => $serverValues['port'], 'ds_pw' => $serverValues['ds_pw'], 'mapsbasepath' => $serverValues['mapsbasepath'] ?? '', 'matchsettings' => $serverValues['matchsettings'], 'adminlevel' => []];
				foreach($serverValues['adminlevel'] as $admLvlId => $admLvlValue){
					$getServerValues['adminlevel'][$admLvlId] = $admLvlValue;
				}
				
				// Add data to the template
				$fileTemplate .= self::getServerTemplate($getServerValues);
			}
			$i++;
		}
		
		// Add a new one
		if($editServer === -1 && isset($serverData) && count($serverData) > 0 ){
			if( self::getServerId($serverData['name']) === -1 ){
				$fileTemplate .= self::getServerTemplate($serverData);
			}
			else{
				return Utils::t('The server already exist! Change the name.');
			}
		}
		$fileTemplate .= self::$CONFIG_END_TEMPLATE;
		
		return File::save(self::$CONFIG_PATH.self::$CONFIG_FILENAME, $fileTemplate, false);
	}
}

?>