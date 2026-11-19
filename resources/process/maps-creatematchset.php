<?php
	// SAVE
	if( isset($_POST['savematchsetting']) && isset($_SESSION['adminserv']['matchset_maps_selected']) ){
		// Filename
		$matchSettingName = Str::replaceChars($_POST['matchSettingName']);
		$filename = $data['mapsDirectoryPath'].$args['directory'].$matchSettingName;
		if(File::getExtension($matchSettingName) != 'txt'){
			$filename .= '.txt';
		}

		$struct = [];

		// Gameinfos
		// TM2020: getGameInfosStructFromPOST() returns [] -- the gameinfos section is empty,
		// because GameMode/ChatTime etc. don't exist. We only save script_name.
		if( isTM2020() ){
			// In TM2020 matchsettings there is no <gameinfos> section with modes.
			// Instead, we only use <mode_script_settings>.
			// Write the minimal section with the script name if available.
			$struct['gameinfos'] = [];
			if( isset($_POST['NextScriptName']) && $_POST['NextScriptName'] ){
				$struct['gameinfos']['script_name'] = $_POST['NextScriptName'];
			}
		}
		else{
			$gameinfos = AdminServ::getGameInfosStructFromPOST();
			$struct['gameinfos'] = [
				'game_mode'                  => $gameinfos['GameMode'],
				'chat_time'                  => $gameinfos['ChatTime'],
				'finishtimeout'              => $gameinfos['FinishTimeout'],
				'allwarmupduration'          => $gameinfos['AllWarmUpDuration'],
				'disablerespawn'             => $gameinfos['DisableRespawn'],
				'forceshowallopponents'      => $gameinfos['ForceShowAllOpponents'],
				'rounds_pointslimit'         => $gameinfos['RoundsPointsLimit'],
				'rounds_custom_points'       => $gameinfos['RoundCustomPoints'],
				'rounds_usenewrules'         => $gameinfos['RoundsUseNewRules'],
				'rounds_forcedlaps'          => $gameinfos['RoundsForcedLaps'],
				'rounds_pointslimitnewrules' => $gameinfos['RoundsPointsLimitNewRules'],
				'team_pointslimit'           => $gameinfos['TeamPointsLimit'],
				'team_maxpoints'             => $gameinfos['TeamMaxPoints'],
				'team_usenewrules'           => $gameinfos['TeamUseNewRules'],
				'team_pointslimitnewrules'   => $gameinfos['TeamPointsLimitNewRules'],
				'timeattack_limit'           => $gameinfos['TimeAttackLimit'],
				'timeattack_synchstartperiod'=> $gameinfos['TimeAttackSynchStartPeriod'],
				'laps_nblaps'                => $gameinfos['LapsNbLaps'],
				'laps_timelimit'             => $gameinfos['LapsTimeLimit'],
				'cup_pointslimit'            => $gameinfos['CupPointsLimit'],
				'cup_roundsperchallenge'     => $gameinfos['CupRoundsPerMap'],
				'cup_nbwinners'              => $gameinfos['CupNbWinners'],
				'cup_warmupduration'         => $gameinfos['CupWarmUpDuration'],
			];
			// script_name: MP and TM2020 (TM2020 handled above, this is MP only)
			if(SERVER_VERSION_NAME == 'ManiaPlanet'){
				$struct['gameinfos']['script_name'] = $gameinfos['ScriptName'];
			}
		}

		// HotSeat -- absent in TM2020, but writing it won't hurt (ignored by the server)
		if( !isTM2020() ){
			$struct['hotseat'] = [
				'game_mode'    => intval($_POST['hotSeatGameMode']),
				'time_limit'   => TimeDate::secToMillisec( intval($_POST['hotSeatTimeLimit']) ),
				'rounds_count' => intval($_POST['hotSeatCountRound']),
			];
		}

		// Filter
		$struct['filter'] = [
			'is_lan'                => array_key_exists('filterIsLan', $_POST),
			'is_internet'           => array_key_exists('filterIsInternet', $_POST),
			'is_solo'               => array_key_exists('filterIsSolo', $_POST),
			'is_hotseat'            => array_key_exists('filterIsHotSeat', $_POST),
			'sort_index'            => intval($_POST['filterSortIndex']),
			'random_map_order'      => array_key_exists('filterRandomMaps', $_POST),
			'force_default_gamemode'=> intval($_POST['filterDefaultGameMode']),
		];

		// ScriptSettings -- GetModeScriptInfo available in MP and TM2020
		if( isMpApi() ){
			if( !$client->query('GetModeScriptInfo') ){
				AdminServ::error();
			}
			else{
				$scriptsettings = $client->getResponse();

				if( !empty($scriptsettings['ParamDescs']) ){
					foreach($scriptsettings['ParamDescs'] as $param){
						$struct['scriptsettings'][] = ['name' => $param['Name'], 'type' => $param['Type'], 'value' => $param['Default']];
					}
				}
			}
		}

		// Maps -- 'map' for MP and TM2020, 'challenge' for TmForever
		$struct['startindex'] = 1;
		$maps = $_SESSION['adminserv']['matchset_maps_selected']['lst'];
		if( isset($maps) && is_array($maps) && !empty($maps) ){
			$mapsField = (!isMpApi()) ? 'challenge' : 'map';
			foreach($maps as $values){
				$struct[$mapsField][$values['UId']] = $values['FileName'];
			}
		}


		// Save
		if( ($result = AdminServ::saveMatchSettings($filename, $struct)) !== true ){
			AdminServ::error(Utils::t('Unable to save the MatchSettings').' : '.$matchSettingName.' ('.$result.')');
		}
		else{
			$action = Utils::t('The MatchSettings "!matchSettingName" was successfully created in the folder', ['!matchSettingName' => $matchSettingName]).' : '.$data['mapsDirectoryPath'].$args['directory'];
			AdminServ::info($action);
			AdminServLogs::add('action', $action);
			Utils::redirection(false, '?p='.USER_PAGE .$data['hasDirectory']);
		}
	}
	else{
		if( !isset($_GET['f']) ){
			unset($_SESSION['adminserv']['matchset_maps_selected']);
		}
	}


	// READ
	$data['directoryList'] = Folder::getArborescence($data['mapsDirectoryPath'], AdminServConfig::$MAPS_HIDDEN_FOLDERS, substr_count((string) $data['mapsDirectoryPath'], '/'));
	$data['matchSettings'] = [];

	// Edit an existing file
	if( isset($_GET['f']) && $_GET['f'] != null ){
		$data['pageTitle'] = Utils::t('Edit');
		$data['matchSettings']['name'] = $_GET['f'];
		$matchSettingsData = AdminServ::getMatchSettingsData($data['mapsDirectoryPath'].$args['directory'].$data['matchSettings']['name']);
		$data['gameInfos'] = ['curr' => null, 'next' => $matchSettingsData['gameinfos']];
		unset($matchSettingsData['gameinfos']);
		$data['matchSettings'] += $matchSettingsData;
		if( isset($data['matchSettings']['maps']) ){
			$maps = AdminServ::getMapListFromMatchSetting($data['matchSettings']['maps']);
			$data['matchSettings']['nbm'] = $maps['nbm']['count'];
			$_SESSION['adminserv']['matchset_maps_selected'] = $maps;
		}
		else{
			$data['matchSettings']['nbm'] = 0;
		}
	}
	// Create a new file
	else{
		$data['pageTitle'] = Utils::t('Create');
		$data['matchSettings']['name'] = 'match_settings';
		$gameInfos = AdminServ::getGameInfos();
		$data['gameInfos'] = ['curr' => null, 'next' => $gameInfos['next']];

		if( !isTM2020() ){
			$data['matchSettings']['hotseat'] = ['GameMode' => 1, 'TimeLimit' => 300000, 'RoundsCount' => 5];
		}

		$data['matchSettings']['filter'] = ['IsLan' => 1, 'IsInternet' => 1, 'IsSolo' => 0, 'IsHotseat' => 1, 'SortIndex' => 1000, 'RandomMapOrder' => 0, 'ForceDefaultGameMode' => 1];
		$data['matchSettings']['StartIndex'] = 0;
		$data['matchSettings']['nbm'] = 0;
	}
?>
