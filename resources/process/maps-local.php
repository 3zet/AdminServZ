<?php
	// GAME VERSION
	// InsertChallenge / AddChallenge — TmForever
	// InsertMap       / AddMap       — ManiaPlanet + TM2020 (identical)
	if(!isMpApi()){
		$queries = ['insert' => 'InsertChallenge', 'add' => 'AddChallenge'];
	}
	else{
		$queries = ['insert' => 'InsertMap', 'add' => 'AddMap'];
	}

	// ACTIONS
	if( isset($_POST['addMap']) && isset($_POST['map']) && count($_POST['map']) > 0 ){
		foreach($_POST['map'] as $map){
			if( !$client->query($queries['add'], AdminServ::toServerPath($data['mapsDirectoryPath'].$map)) ){
				AdminServ::error();
			}
			else{
				AdminServLogs::add('action', 'Add map: '.$map);
			}
		}
	}
	else if( isset($_POST['insertMap']) && isset($_POST['map']) && count($_POST['map']) > 0 ){
		foreach($_POST['map'] as $map){
			if( !$client->query($queries['insert'], AdminServ::toServerPath($data['mapsDirectoryPath'].$map)) ){
				AdminServ::error();
			}
			else{
				AdminServLogs::add('action', 'Insert map: '.$map);
			}
		}
	}
	else if( isset($_POST['downloadMap']) && isset($_POST['map']) && count($_POST['map']) > 0 ){
		$countMaps = count($_POST['map']);
		if($countMaps > 1){
			$struct = [];
			foreach($_POST['map'] as $map){
				$struct[] = $data['mapsDirectoryPath'].$map;
			}
			$zipError = null;
			$zipFileName = 'maps.zip';
			if( !(new Zip())->create($zipFileName, $struct, $zipError) ){
				AdminServ::error($zipError);
			}
			else{
				File::download($zipFileName);
				AdminServLogs::add('action', 'Download packmap ('.$countMaps.' maps)');
				if( $result = File::delete($zipFileName) !== true ){
					AdminServ::error($result);
				}
			}
		}
		else{
			File::download($data['mapsDirectoryPath'].$_POST['map'][0]);
			AdminServLogs::add('action', 'Download map: '.$_POST['map'][0]);
		}
	}
	else if( isset($_POST['renameMapValid']) && isset($_POST['map']) && count($_POST['map']) > 0 && isset($_POST['renameMapList']) && count($_POST['renameMapList']) > 0 ){
		$i = 0;
		foreach($_POST['renameMapList'] as $newMapName){
			$result = File::rename($data['mapsDirectoryPath'].$_POST['map'][$i], $data['mapsDirectoryPath'].$args['directory'].$newMapName);
			if($result !== true ){
				AdminServ::error(Utils::t('Unable to rename the map').' : '.$newMapName.' ('.$result.')');
				break;
			}
			else{
				AdminServLogs::add('action', 'Rename map: '.$_POST['map'][$i].' to '.$newMapName);
			}
			$i++;
		}

		Utils::redirection(false, '?p='.USER_PAGE .$data['hasDirectory']);
	}
	else if( isset($_POST['renameAutoValid']) && isset($_POST['map']) && count($_POST['map']) > 0 && isset($_POST['renameMapList']) && count($_POST['renameMapList']) > 0 ){
		$i = 0;
		foreach($_POST['renameMapList'] as $newMapName){
			$result = File::rename($data['mapsDirectoryPath'].$_POST['map'][$i], $data['mapsDirectoryPath'].$args['directory'].Str::replaceChars($newMapName));
			if($result !== true){
				AdminServ::error(Utils::t('Unable to rename the map').' : '.$newMapName.' ('.$result.')');
				break;
			}
			else{
				AdminServLogs::add('action', 'Rename map (auto): '.$_POST['map'][$i].' to '.$newMapName);
			}
			$i++;
		}

		Utils::redirection(false, '?p='.USER_PAGE .$data['hasDirectory']);
	}
	else if( isset($_POST['moveMapValid']) && isset($_POST['moveDirectoryList']) && isset($_POST['map']) && count($_POST['map']) > 0 ){
		if($_POST['moveDirectoryList'] == '.'){
			$newPath = $data['mapsDirectoryPath'];
		}
		else{
			$newPath = $_POST['moveDirectoryList'];
		}

		foreach($_POST['map'] as $map){
			$result = File::rename($data['mapsDirectoryPath'].$map, $newPath.basename((string) $map) );
			if($result !== true ){
				AdminServ::error(Utils::t('Unable to move the map').' : '.$map.' ('.$result.')');
				break;
			}
			else{
				AdminServLogs::add('action', 'Move map: '.$map.' to '.$newPath.basename((string) $map) );
			}
		}

		Utils::redirection(false, '?p='.USER_PAGE .$data['hasDirectory']);
	}
	else if( isset($_POST['deleteMap']) && isset($_POST['map']) && count($_POST['map']) > 0 ){
		foreach($_POST['map'] as $map){
			$result = File::delete($data['mapsDirectoryPath'].$map);
			if($result !== true){
				AdminServ::error(Utils::t('Unable to delete the map').' : '.$map.' ('.$result.')');
				break;
			}
			else{
				AdminServLogs::add('action', 'Delete map: '.$map);
			}
		}

		Utils::redirection(false, '?p='.USER_PAGE .$data['hasDirectory']);
	}

	// Save MatchSettings -- SaveMatchSettings available in MP and TM2020
	if( (isset($_POST['addMap']) || isset($_POST['insertMap'])) && SERVER_MATCHSET ){
		if( isset($_POST['SaveCurrentMatchSettings']) && array_key_exists('SaveCurrentMatchSettings', $_POST) ){
			if( !$client->query('SaveMatchSettings', AdminServ::toServerPath($data['mapsDirectoryPath'] . SERVER_MATCHSET)) ){
				AdminServ::error();
			}
		}
	}


	// MAPLIST
	$sort = null;
	if( isset($_GET['sort']) && $_GET['sort'] != null){
		$sort = addslashes((string) $_GET['sort']);
	}
	$data['maps'] = AdminServ::getLocalMapList($data['currentDir'], $args['directory'], $sort);
?>
