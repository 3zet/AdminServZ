<?php
	// JSON endpoint: PHP notices must never reach the response body (they still go to the error log)
	ini_set('display_errors', '0');
	// AUTHENTICATION
	$LEVEL = 'User';

	// ISSET
	if( isset($_GET['cfg']) ){ $path_cfg = $_GET['cfg']; }else{ $path_cfg = null; }
	if( isset($_GET['rsc']) ){ $path_rsc = $_GET['rsc']; }else{ $path_rsc = null; }

	// INCLUDES
	$serverConfig = '../../'.$path_cfg;
	if( file_exists($serverConfig) ){
		require_once $serverConfig;
	}
	require_once '../class/GbxRemote.inc.php';
	require_once '../class/tmnick.class.php';
	require_once '../class/utils.class.php';
	$langCode = Utils::getLang();
	$langFile = '../lang/'.$langCode.'.php';
	if( file_exists($langFile) ){
		require_once $langFile;
	}

	// FUNCTIONS
	function sortByTeam(array $a, array $b): int{
		if($a['teamId'] == 0){ $a['teamId'] = 'blue'; }
		else if($a['teamId'] == 1){ $a['teamId'] = 'red'; }
		else{ $a['teamId'] = 'spectator'; }
		if($b['teamId'] == 0){ $b['teamId'] = 'blue'; }
		else if($b['teamId'] == 1){ $b['teamId'] = 'red'; }
		else{ $b['teamId'] = 'spectator'; }

		if($a['teamId'] == $b['teamId']){ return 0; }
		if($a['teamId'] < $b['teamId']){ return -1; }else{ return 1; }
	}

	// DATA
	$out = [];
	if( class_exists('ServerConfig') ){
		if( isset(ServerConfig::$SERVERS) && count(ServerConfig::$SERVERS) > 0 && !isset(ServerConfig::$SERVERS['new server name']) && !isset(ServerConfig::$SERVERS['']) ){

			$serverId = 0;
			foreach(ServerConfig::$SERVERS as $serverValues){
				// Connection
				$client = new IXR_ClientMulticall_Gbx;
				if( !$client->InitWithIp($serverValues['address'], $serverValues['port']) ){
					$out['servers'][$serverId]['error'] = Utils::t('Offline server.');
				}
				else{
					if( !$client->query('Authenticate', $LEVEL, $serverValues['ds_pw']) ){
						$out['servers'][$serverId]['error'] = Utils::t('Authentication error.');
					}
					else{
						// Server version -- detect the type before the multicall
						$client->query('GetVersion');
						$version = $client->getResponse();
						$versionName = $version['Name'] ?? ''; // 'TmForever' | 'ManiaPlanet' | 'Trackmania' | pre-Forever titles
						// Challenge-era servers: TmForever and the 2006-2007 titles (TmUnited, TmNationsESWC, TmSunrise, TmOriginal)
						$isChallengeApi = in_array($versionName, ['TmForever', 'TmUnited', 'TmNationsESWC', 'TmSunrise', 'TmOriginal'], true);

						$out['servers'][$serverId]['version']['name'] = $versionName;

						// Link protocol
						$linkProtocol = $isChallengeApi ? 'tmtp' : 'maniaplanet';
						$out['servers'][$serverId]['version']['protocol'] = $linkProtocol;

						// Map info method name
						$queryName = ['getMapInfo' => $isChallengeApi ? 'GetCurrentChallengeInfo' : 'GetCurrentMapInfo'];

						// Requests -- GetGameMode replaced by GetModeScriptInfo for TM2020
						$client->addCall('GetServerName');
						$client->addCall('GetSystemInfo');
						$client->addCall('GetStatus');
						if($versionName == 'Trackmania'){
							// TM2020: GetGameMode doesn't exist -- fetch the script right away
							$client->addCall('GetModeScriptInfo');
						}
						else{
							$client->addCall('GetGameMode');
						}
						$client->addCall($queryName['getMapInfo']);
						$client->addCall('GetPlayerList', [50, 0]);
						$client->addCall('GetMaxPlayers');
						$client->multiquery();
						$queriesData = $client->getMultiqueryResponse();

						$out['servers'][$serverId]['name'] = TmNick::toHtml($queriesData['GetServerName'], 10, true, false, '#999');

						// Login + Title (pre-Forever servers have no GetSystemInfo, so no login to join with)
						$system = $queriesData['GetSystemInfo'];
						$out['servers'][$serverId]['serverlogin'] = $system['ServerLogin'] ?? '';
						$title = !$isChallengeApi ? ($system['TitleId'] ?? null) : null;
						$out['servers'][$serverId]['version']['title'] = $title;

						// Status
						$out['servers'][$serverId]['status'] = $queriesData['GetStatus']['Name'] ?? '';

						// GameMode -- TM2020 is always in Script mode
						if($versionName == 'Trackmania'){
							$modeInfo = $queriesData['GetModeScriptInfo'];
							$scriptBaseName = str_replace('.Script.txt', '', $modeInfo['Name'] ?? 'Script');
							// Strip the path (e.g. "TrackMania\TimeAttack" -> "TimeAttack")
							$scriptParts = explode('\\', $scriptBaseName);
							$scriptShort = end($scriptParts);
							$gameModeName = 'Script';
							$out['servers'][$serverId]['gamemode'] = 'Script ('.$scriptShort.')';
						}
						else{
							// ManiaPlanet / challenge-era servers
							$gameMode = (int) ($queriesData['GetGameMode'] ?? 0);
							// LOCAL array for displayserv reading only (NOT ExtensionConfig::$GAMEMODES,
							// because that config is also used by the save dropdown in Game Infos and has
							// its own, DIFFERENT numbering that works correctly for SetGameInfos -- don't touch).
							$gameModeListNameRead = ['Script', 'Rounds', 'TimeAttack', 'Team', 'Laps', 'Stunts', 'Cup'];
							if($isChallengeApi){
								// TMF and older: GetGameMode returns raw Rounds(0),TimeAttack(1),Team(2),Laps(3),Stunts(4),Cup(5)
								// -- with no Script mode at 0, hence the +1 correction for READING only (displayserv).
								$gameMode++;
							}
							$gameModeName = $gameModeListNameRead[$gameMode] ?? 'Unknown';

							if($gameModeName == 'Script'){
								$client->addCall('GetScriptName');
								$client->multiquery();
								$scriptname = $client->getMultiqueryResponse();
								$out['servers'][$serverId]['gamemode'] = $gameModeName.' ('.str_replace('.Script.txt', '', $scriptname['GetScriptName']['CurrentValue'] ?? '').')';
							}
							else{
								$out['servers'][$serverId]['gamemode'] = $gameModeName;
							}
						}

						// Map
						$currentMapInfo = $queriesData[$queryName['getMapInfo']];
						$currentMapEnv = $currentMapInfo['Environnement'] ?? '';
						if($currentMapEnv == 'Speed'){ $currentMapEnv = 'Desert'; }
						else if($currentMapEnv == 'Alpine'){ $currentMapEnv = 'Snow'; }
						$currentMapEnvFile = null;
						$pathToEnvFile = $path_rsc.'images/env/'.strtolower((string) $currentMapEnv).'.png';
						if( file_exists('../../'.$pathToEnvFile) ){
							$currentMapEnvFile = $pathToEnvFile;
						}
						$out['servers'][$serverId]['map']['name'] = TmNick::toHtml(htmlspecialchars((string) ($currentMapInfo['Name'] ?? ''), ENT_QUOTES, 'UTF-8'), 10, true, false, '#999');
						$out['servers'][$serverId]['map']['env']['name'] = $currentMapEnv;
						$out['servers'][$serverId]['map']['env']['filename'] = $currentMapEnvFile;

						// Players
						$playerList = $queriesData['GetPlayerList'];
						$countPlayerList = count($playerList);
						if( $countPlayerList > 0 ){
							$playerId = 0;
							foreach($playerList as $player){
								// TM2020 and ManiaPlanet use SpectatorStatus,
								// the original used IsSpectator (now outdated)
								$isSpectator = $player['SpectatorStatus'] ?? $player['IsSpectator'] ?? 0;
								$playerStatus = ($isSpectator != 0) ? Utils::t('Spectator') : Utils::t('Player');

								$player['TeamId'] ??= -1;
					if($player['TeamId'] == 0){ $teamName = Utils::t('Blue'); }
								else if($player['TeamId'] == 1){ $teamName = Utils::t('Red'); }
								else{ $teamName = Utils::t('Spectator'); }

								$out['players'][$serverId]['list'][$playerId] = [
									'name'     => TmNick::toHtml(htmlspecialchars((string) $player['NickName'], ENT_QUOTES, 'UTF-8'), 10, true),
									'status'   => $playerStatus,
									'teamId'   => $player['TeamId'],
									'teamName' => $teamName,
								];
								$playerId++;
							}
						}
						else{
							$out['players'][$serverId]['list'] = Utils::t('No player');
						}

						// Sort in team mode
						if($gameModeName == 'Team' && $countPlayerList > 0){
							uasort($out['players'][$serverId]['list'], 'sortByTeam');
						}

						// Count players
						$maxPlayers = $queriesData['GetMaxPlayers'];
						$out['players'][$serverId]['count']['current'] = $countPlayerList;
						$out['players'][$serverId]['count']['max'] = $maxPlayers['NextValue'] ?? 0;
					}
				}

				// Disconnect
				$client->Terminate();
				$serverId++;
			}
		}
	}

	// Return
	echo json_encode($out);
?>
