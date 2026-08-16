<?php
	// SAVE
	if( isset($_POST['saveserver']) ){
		// Variables
        $displayServPassword = trim((string) $_POST['addDisplayServPassword']);
		$serverName = Str::replaceSpecialChars( htmlspecialchars(addslashes((string) $_POST['addServerName'])), false);
		$serverAddress = trim((string) $_POST['addServerAddress']);
		$serverPort = intval($_POST['addServerPort']);
		$serverMapsBasePath = trim((string) $_POST['addServerMapsBasePath']);
		$serverMatchSet = trim((string) $_POST['addServerMatchSet']);
		$serverAdmLvl = ['SuperAdmin' => $_POST['addServerAdmLvlSA'], 'Admin' => $_POST['addServerAdmLvlADM'], 'User' => $_POST['addServerAdmLvlUSR']];
		$isNotAnArray = ['all', 'local', 'none'];
		foreach($serverAdmLvl as $admLvlId => $admLvlValue){
			if( !in_array($admLvlValue, $isNotAnArray) ){
				$serverAdmLvl[$admLvlId] = explode(',', (string) $admLvlValue);
			}
			else{
				$serverAdmLvl[$admLvlId] = trim((string) $admLvlValue);
			}
		}
		$serverData = ['name' => $serverName, 'address' => $serverAddress, 'port' => $serverPort, 'mapsbasepath' => $serverMapsBasePath, 'matchsettings' => $serverMatchSet, 'adminlevel' => [], 'ds_pw' => $displayServPassword];
		foreach($serverAdmLvl as $admLvlId => $admLvlValue){
			$serverData['adminlevel'][$admLvlId] = $admLvlValue;
		}
		
		// Edit
		if($args['id'] !== -1){
			if( ($result = AdminServServerConfig::saveServerConfig($serverData, $args['id'])) !== true ){
				AdminServ::error( Utils::t('Unable to modify the server.').' ('.$result.')');
			}
			else{
				$action = Utils::t('This server has been modified.');
				AdminServ::info($action);
				AdminServLogs::add('action', $action);
				Utils::redirection(false, '?p=config-servers');
			}
		}
		// Add
		else{
			if( ($result = AdminServServerConfig::saveServerConfig($serverData)) !== true ){
				AdminServ::error( Utils::t('Unable to add the server.').' ('.$result.')');
			}
			else{
				$action = Utils::t('This server has been added.');
				AdminServ::info($action);
				AdminServLogs::add('action', $action);
				Utils::redirection(false, '?p='.USER_PAGE);
			}
		}
	}
	
	
	// READ
	$data = ['name' => null, 'address' => 'localhost', 'port' => 5000, 'mapsbasepath' => null, 'matchsettings' => 'MatchSettings/', 'adminlevel' => ['SuperAdmin' => 'all', 'Admin' => 'all', 'User' => 'all'], 'ds_pw' => 'User'];
	if($args['id'] !== -1){
		define('IS_SERVER_EDITION', true);
		$data['name'] = AdminServServerConfig::getServerName($args['id']);
		if($data['name']){
			$serverData = AdminServServerConfig::getServer($data['name']);
			$data['address'] = $serverData['address'];
			$data['port'] = $serverData['port'];
            $data['ds_pw'] = $serverData['ds_pw'];
			$data['mapsbasepath'] = $serverData['mapsbasepath'] ?? '';
			$data['matchsettings'] = $serverData['matchsettings'];
			foreach($serverData['adminlevel'] as $admLvlId => $admLvlValue){
				if( is_array($admLvlValue) ){
					$data['adminlevel'][$admLvlId] = implode(', ', $admLvlValue);
				}
				else{
					$data['adminlevel'][$admLvlId] = $admLvlValue;
				}
			}
		}
	}
?>