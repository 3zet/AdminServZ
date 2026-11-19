<?php
	// ACTIONS
	if( isset($_POST['save']) && isset($_POST['list']) && $_POST['list'] != null ){
		$serverList = ServerConfig::$SERVERS;
		$list = explode(',', (string) $_POST['list']);
		
		$newServerList = [];
		foreach($list as $listServerName){
			$newServerList[$listServerName] = ['address' => $serverList[$listServerName]['address'], 'port' => $serverList[$listServerName]['port'], 'mapsbasepath' => $serverList[$listServerName]['mapsbasepath'] ?? '', 'matchsettings' => $serverList[$listServerName]['matchsettings'], 'remoteroot' => $serverList[$listServerName]['remoteroot'] ?? '', 'localroot' => $serverList[$listServerName]['localroot'] ?? '', 'adminlevel' => $serverList[$listServerName]['adminlevel']];
		}
		
		AdminServServerConfig::saveServerConfig([], -1, $newServerList);
		AdminServLogs::add('action', 'Order server list');
		Utils::redirection(false, '?p='.USER_PAGE);
	}
	
	
	// SERVERLIST
	$data['servers'] = [];
	if (is_array(ServerConfig::$SERVERS) && !empty(ServerConfig::$SERVERS)) {
		$data['servers'] = ServerConfig::$SERVERS;
	}
?>