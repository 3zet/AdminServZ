<?php
	// EDIT
	if( isset($_POST['editserver']) ){
		$serverId = AdminServServerConfig::getServerId($_POST['server'][0]);
		Utils::redirection(false, '?p=config-addserver&id='.$serverId);
	}
	
	// DUPLICATE
	if( isset($_POST['duplicateserver']) ){
		$getServerData = AdminServServerConfig::getServer($_POST['server'][0]);
		
		// SET
		$setServerData = ['name' => trim( htmlspecialchars( addslashes($_POST['server'][0] . ' - '.Utils::t('copy') ) ) ), 'address' => trim((string) $getServerData['address']), 'port' => intval($getServerData['port']), 'matchsettings' => trim((string) $getServerData['matchsettings']), 'adminlevel' => ['SuperAdmin' => $getServerData['adminlevel']['SuperAdmin'], 'Admin' => $getServerData['adminlevel']['Admin'], 'User' => $getServerData['adminlevel']['User']]];
		if( AdminServServerConfig::saveServerConfig($setServerData) ){
			$action = Utils::t('This server has been duplicated.');
			AdminServ::info($action);
			AdminServLogs::add('action', $action);
			Utils::redirection(false, '?p='.USER_PAGE);
		}
		else{
			AdminServ::error( Utils::t('Unable to duplicate server.') );
		}
	}
	
	// DELETE
	if( isset($_POST['deleteserver']) ){
		$servers = ServerConfig::$SERVERS;
		unset($servers[$_POST['server'][0]]);
		if( ($result = AdminServServerConfig::saveServerConfig([], -1, $servers)) !== true ){
			AdminServ::error( Utils::t('Unable to delete server.').' ('.$result.')');
		}
		else{
			$action = Utils::t('The "!serverName" server has been deleted.', ['!serverName' => $_POST['server'][0]]);
			AdminServ::info($action);
			AdminServLogs::add('action', $action);
			Utils::redirection(false, '?p='.USER_PAGE);
		}
	}
	
	// SERVERLIST
	$data['servers'] = [];
	if (is_array(ServerConfig::$SERVERS) && !empty(ServerConfig::$SERVERS)) {
		$data['servers'] = ServerConfig::$SERVERS;
	}
	$data['count'] = count($data['servers']);
	$data['adminLevelsType'] = AdminServAdminLevel::getDefaultType();
?>