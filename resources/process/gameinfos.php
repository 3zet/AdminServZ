<?php
	// SAVE
	if( isset($_POST['savegameinfos']) ){
		// Variables
		$struct = AdminServ::getGameInfosStructFromPOST();

		// TM2020 : SetGameInfos n'existe pas — on passe directement par SetScriptName
		if( isTM2020() ){
			$newScript = trim((string)($_POST['NextScriptName'] ?? ''));
			if( $newScript !== '' ){
				if( !$client->query('SetScriptName', $newScript) ){
					AdminServ::error();
				}
			}
		}
		else{
			// Requests
			if(!$client->query('SetGameInfos', $struct) && $client->getErrorMessage() != 'Script not allowed for this title.'){
				AdminServ::error();
			}
		}

		if( !isset($_SESSION['error']) ){
			// Team info
			if(SERVER_VERSION_NAME == 'ManiaPlanet' && isset($_POST['teamInfo1Name']) ){
				$team1 = ['name' => $_POST['teamInfo1Name'], 'color' => $_POST['teamInfo1Color'], 'colorhex' => $_POST['teamInfo1ColorHex'], 'country' => $_POST['teamInfo1Country']];
				$team2 =  ['name' => $_POST['teamInfo2Name'], 'color' => $_POST['teamInfo2Color'], 'colorhex' => $_POST['teamInfo2ColorHex'], 'country' => $_POST['teamInfo2Country']];

				AdminServ::setTeamInfo($team1, $team2);
			}

			// RoundCustomPoints
			if( isset($_POST['NextRoundCustomPoints']) && $_POST['NextRoundCustomPoints'] != null){
				$NextRoundCustomPoints = explode(',', (string) $_POST['NextRoundCustomPoints']);
				$NextRoundCustomPointsArray = [];
				foreach($NextRoundCustomPoints as $point){
						$NextRoundCustomPointsArray[] = intval( trim($point) );
					}
				if( !$client->query('SetRoundCustomPoints', $NextRoundCustomPointsArray) ){
					AdminServ::error();
				}
			}

			// MatchSettings
			if(SERVER_MATCHSET){
				$mapsDirectory = AdminServ::getMapsDirectoryPath();
				if( array_key_exists('SaveCurrentMatchSettings', $_POST) ){
					if( !$client->query('SaveMatchSettings', AdminServ::toServerPath($mapsDirectory . SERVER_MATCHSET)) ){
						AdminServ::error();
					}
				}
			}

			AdminServLogs::add('action', 'Save game infos');
			Utils::redirection(false, '?p='.USER_PAGE);
		}
	}
	$gameInfos = AdminServ::getGameInfos();
	$data['gameInfos'] = ['curr' => $gameInfos['curr'], 'next' => $gameInfos['next']];
	$data['teamInfo'] = ['team1' => ['name' => Utils::t('Blue'), 'color' => '0.667', 'colorhex' => '#0000ff', 'country' => 'World|France'], 'team2' => ['name' => Utils::t('Red'), 'color' => '0', 'colorhex' => '#ff0000', 'country' => 'World|France']];
	if( isset($_SESSION['adminserv']['teaminfo']) && count($_SESSION['adminserv']['teaminfo']) > 0 ){
		$data['teamInfo'] = ['team1' => $_SESSION['adminserv']['teaminfo']['team1'], 'team2' => $_SESSION['adminserv']['teaminfo']['team2']];
	}

	unset($gameInfos);
?>