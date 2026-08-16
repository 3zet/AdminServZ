<?php
define('ADMINSERV_TIMER', false);
define('ADMINSERV_VERSION', '2.1.2-tm2020');

/**
 * Helper: true for ManiaPlanet and TM2020 (everything except TmForever)
 */
function isMpApi(): bool {
	return defined('SERVER_VERSION_NAME') && SERVER_VERSION_NAME !== 'TmForever';
}

/**
 * Helper: true only for TM2020 (Name === 'Trackmania')
 */
function isTM2020(): bool {
	return defined('SERVER_VERSION_NAME') && SERVER_VERSION_NAME === 'Trackmania';
}


/**
* Class for AdminServ's core functionality
*/
class AdminServ {
	
	/**
	* Include PHP classes
	*/
	public static function getClass(): void{
		$pathClass = AdminServConfig::$PATH_RESOURCES.'class/';
		require_once $pathClass.'GbxRemote.inc.php';
		require_once $pathClass.'gbxdatafetcher.inc.php';
		require_once $pathClass.'utils.class.php';
		require_once $pathClass.'tmnick.class.php';
		require_once $pathClass.'timedate.class.php';
		require_once $pathClass.'file.class.php';
		require_once $pathClass.'folder.class.php';
		require_once $pathClass.'str.class.php';
		require_once $pathClass.'upload.class.php';
		require_once $pathClass.'zip.class.php';
		
		$pathCore = AdminServConfig::$PATH_RESOURCES.'core/';
		require_once $pathCore.'adminlevel.php';
		require_once $pathCore.'cache.php';
		require_once $pathCore.'event.php';
		require_once $pathCore.'logs.php';
		require_once $pathCore.'plugin.php';
		require_once $pathCore.'server.php';
		require_once $pathCore.'sort.php';
		require_once $pathCore.'ui.php';
	}
	
	
	/**
	* Debug methods
	*/
	public static function dsm($val): void{
		echo '<pre>';
		print_r($val);
		echo '</pre>';
	}
	public static function debug($globalValue = null){
		$const = get_defined_constants(true);
		
		return self::dsm(
			['GLOBALS' => ($globalValue) ? $GLOBALS[$globalValue] : $GLOBALS, 'ADMINSERV' => $const['user']]
		);
	}
	
	/**
	* Errors and info
	*/
	public static function error($text = null): void{
		global $client;
		// Try to get the error message from the dedicated server
		if($text === null){
			$text = '['.$client->getErrorCode().'] '.Utils::t( $client->getErrorMessage() );
		}
		
		AdminServLogs::add('error', $text);
		unset($_SESSION['info']);
		$_SESSION['error'] = $text;
	}
	public static function info($text): void{
		$_SESSION['info'] = $text;
	}
	
	
	/**
	* Page load time
	*/
	public static function startTimer(): void{
		global $timeStart;
		$timeStart = microtime(true);
	}
	public static function endTimer(): string{
		global $timeStart;
		$timeEnd = microtime(true);
		$time = $timeEnd - $timeStart;
		return number_format($time, 3);
	}
	
	
	/**
	* Check PHP version
	*/
	public static function checkPHPVersion(string $version): void{
		if( !version_compare(PHP_VERSION, $version, '>=') ){
			echo '<b>This PHP version is not compatible with AdminServ.</b><br />Your PHP version: '. PHP_VERSION .'<br />PHP version required: '.$version;
			exit;
		}
	}
	
	
	/**
     * Check read/write permissions for files
     *
     * @param array $list -> List of files to test: array('path' => 777)
     */
    public static function checkRights($list): void{
		if( count($list) > 0 ){
			foreach($list as $path => $minChmod){
				$result = Folder::checkRights($path, $minChmod);
				foreach($result as $grpName => $grpValues){
					foreach($grpValues['result'] as $bool){
						if(!$bool){
							self::error( Utils::t('This path was not required rights:').' '.$path.' ("'.$grpName.'" '.Utils::t('needs').' "'.$minChmod.'")');
							break;
						}
					}
				}
			}
		}
	}
	
	
	/**
	* Initialize the current server's client
	*
	* @param bool $fullInit -> Full initialization? yes by default.
	* If not, it doesn't retrieve any base info, only the connection
	* to the dedicated server and its authentication.
	* @return bool
	*/
	public static function initialize($fullInit = true){
		global $client;
		$out = false;
		
		if( isset($_SESSION['adminserv']['sid']) ){
			// CONSTANTS
			define('USER_ADMINLEVEL', $_SESSION['adminserv']['adminlevel'] ?? '');
			define('SERVER_ID', $_SESSION['adminserv']['sid'] ?? 0);
			define('SERVER_NAME', $_SESSION['adminserv']['name'] ?? '');
			define('SERVER_ADDR', ServerConfig::$SERVERS[SERVER_NAME]['address'] ?? '');
			define('SERVER_XMLRPC_PORT', ServerConfig::$SERVERS[SERVER_NAME]['port'] ?? 0);
			define('SERVER_MATCHSET', ServerConfig::$SERVERS[SERVER_NAME]['matchsettings'] ?? '');
			define('SERVER_MAPS_BASEPATH', ServerConfig::$SERVERS[SERVER_NAME]['mapsbasepath'] ?? '');
			define('SERVER_ADMINLEVEL', serialize( ServerConfig::$SERVERS[SERVER_NAME]['adminlevel'] ?? []));
			
			// CONNECTION
			$client = new IXR_ClientMulticall_Gbx;
			if( !$client->InitWithIp(SERVER_ADDR, SERVER_XMLRPC_PORT, AdminServConfig::SERVER_CONNECTION_TIMEOUT) ){
				Utils::redirection(false, '?error='.urlencode( (string) Utils::t('The server is not accessible.') ) );
			}
			else{
				if( !AdminServAdminLevel::userAllowed(USER_ADMINLEVEL) ){
					Utils::redirection(false, '?error='.urlencode( (string) Utils::t('You are not allowed at this admin level') ) );
				}
				else{
					if( !$client->query('Authenticate', AdminServAdminLevel::getType(), $_SESSION['adminserv']['password']) ){
						Utils::redirection(false, '?error='.urlencode( (string) Utils::t('The password doesn\'t match to the server.') ) );
					}
					else{
						if($fullInit){
							// Step 1: GetVersion + GetSystemInfo (without IsRelayServer -- doesn't exist in TM2020)
							$client->addCall('SetApiVersion', [date('Y-m-d')] );
							$client->addCall('GetVersion');
							$client->addCall('GetSystemInfo');

							if( !$client->multiquery() ){
								self::error();
							}
							else{
								$queriesData = $client->getMultiqueryResponse();

								// Version
								$getVersion = $queriesData['GetVersion'];
								define('SERVER_VERSION_NAME', $getVersion['Name']);
								define('SERVER_VERSION', $getVersion['Version']);
								define('SERVER_BUILD', $getVersion['Build']);
								if( isMpApi() ){
									define('API_VERSION', $getVersion['ApiVersion'] ?? '');
								}

								// SystemInfo
								$getSystemInfo = $queriesData['GetSystemInfo'];
								define('SERVER_LOGIN', $getSystemInfo['ServerLogin']);
								define('SERVER_PUBLISHED_IP', $getSystemInfo['PublishedIp']);
								define('SERVER_PORT', $getSystemInfo['Port']);
								define('SERVER_P2P_PORT', $getSystemInfo['P2PPort']);
								if( isMpApi() ){
									define('SERVER_TITLE', $getSystemInfo['TitleId'] ?? '');
									define('IS_SERVER', $getSystemInfo['IsServer'] ?? false);
									define('IS_DEDICATED', $getSystemInfo['IsDedicated'] ?? false);
								}

								// Step 2: IsRelayServer -- only for TmForever and ManiaPlanet
								if( isTM2020() ){
									define('IS_RELAY', false);
								}
								else{
									if( $client->query('IsRelayServer') ){
										define('IS_RELAY', $client->getResponse());
									} else {
										define('IS_RELAY', false);
									}
								}

								// Protocol
								if( isMpApi() ){
									TmNick::$linkProtocol = 'maniaplanet';
								}
								define('LINK_PROTOCOL', TmNick::$linkProtocol);
								
								// Display mode: detail or simple
								if( isset($_SESSION['adminserv']['mode']['general']) ){
									define('USER_MODE_GENERAL', $_SESSION['adminserv']['mode']['general']);
								}
								else{
									define('USER_MODE_GENERAL', 'simple');
								}
								if( isset($_SESSION['adminserv']['mode']['maps']) ){
									define('USER_MODE_MAPS', $_SESSION['adminserv']['mode']['maps']);
								}
								else{
									define('USER_MODE_MAPS', 'simple');
								}
								
								// TmForever
								if(SERVER_VERSION_NAME == 'TmForever'){
									array_shift(ExtensionConfig::$GAMEMODES);
									$stuntsGameMode = array_pop(ExtensionConfig::$GAMEMODES);
									$CupGameMode = array_pop(ExtensionConfig::$GAMEMODES);
									ExtensionConfig::$GAMEMODES[4] = $stuntsGameMode;
									ExtensionConfig::$GAMEMODES[5] = $CupGameMode;
								}
								
								$out = true;
							}
						}
						else{
							$out = true;
						}
					}
				}
			}
		}
		
		return $out;
	}
	
	
	/**
     * Return a TM or ManiaPlanet protocol link
     *
     * @param string $link -> Link: #join=server_login or /:manialink_name
     */
    public static function getProtocolLink(string $link): string {
		$out = null;
		$protocolName = 'maniaplanet';
		if (defined('LINK_PROTOCOL')) {
			$protocolName = LINK_PROTOCOL;
		}
		$protocolSeparator = '://';
		
		if (defined('SERVER_VERSION_NAME') && SERVER_VERSION_NAME == 'ManiaPlanet' && defined('SERVER_TITLE')) {
			$out = $protocolName.$protocolSeparator.$link.'@'.SERVER_TITLE;
		}
		else {
			$out = $protocolName.$protocolSeparator.$link;
		}
		
		return $out;
	}
	
	
	/**
	* Get the game mode name
	*
	* @param int  $gameMode  -> The response from GetGameMode()
	* @param bool $getManual -> Force manual retrieval of the name from the number in the config
	* @return string
	*/
	public static function getGameModeName($gameMode, $getManual = false){
		if( isTM2020() ){
			global $client;
			if( $client->query('GetModeScriptInfo') ){
				$info = $client->getResponse();
				return self::formatScriptName($info['Name'] ?? 'Script');
			}
			return 'Script';
		}
		$out = Utils::t('No game mode available');

		if( class_exists('ExtensionConfig') && isset(ExtensionConfig::$GAMEMODES) && count(ExtensionConfig::$GAMEMODES) > 0 ){
			if($getManual && defined('SERVER_VERSION_NAME') && SERVER_VERSION_NAME == 'TmForever'){
				$gameMode--;
				if( isset(ExtensionConfig::$GAMEMODES[$gameMode]) ){
					$out = ExtensionConfig::$GAMEMODES[$gameMode];
				}
			}
			else{
				$out = ExtensionConfig::$GAMEMODES[$gameMode];
			}
		}
		
		return $out;
	}
	
	
	/**
	* Determine if the given game mode name matches the current game mode
	*
	* @param string $gameModeName    -> Game mode name to test
	* @param int    $currentGameMode -> Current game mode ID. If null, the current game mode is fetched from the server
	* @return bool
	*/
	public static function isGameMode($gameModeName, $currentGameMode = null){
		global $client;
		$out = false;
		if( isTM2020() ){ return ($gameModeName === 'Script'); }
		if($currentGameMode === null){
			$client->query('GetGameMode');
			$currentGameMode = $client->getResponse();
		}
		
		if($gameModeName == self::getGameModeName($currentGameMode) ){
			$out = true;
		}
		
		return $out;
	}
	
	
	/**
	* Check if the server configuration is compatible with team mode
	*
	* @params int    $gameMode   -> Game mode ID
	* @params string $scriptName -> Script name if the game mode is 0
	* @return bool
	*/
	public static function checkDisplayTeamMode($gameMode, $scriptName = null){
		$out = false;
		
		if($gameMode == 0 && SERVER_VERSION_NAME == 'ManiaPlanet' && class_exists('ExtensionConfig') && isset(ExtensionConfig::$TEAMSCRIPTS) && count(ExtensionConfig::$TEAMSCRIPTS) > 0 ){
			foreach(ExtensionConfig::$TEAMSCRIPTS as $teamScript){
				if( stristr((string) $scriptName, (string) $teamScript) ){
					$out = true;
					break;
				}
			}
		}
		else{
			if( self::isGameMode('Team', $gameMode) ){
				$out = true;
			}
		}
		
		return $out;
	}
	
	
	/**
     * Format a script's name
     *
     * @param string $scriptName -> The script name returned by the server
     */
    public static function formatScriptName($scriptName): string{
		$out = str_ireplace('.script.txt', '', $scriptName);
		$scriptNameEx = explode('\\', $out);
		
		return $scriptNameEx[count($scriptNameEx)-1];
	}
	
	
	/**
     * Get current server information (map, server, stats, players)
     *
     * @param string $sortBy -> Sort order to apply to the list
     */
    public static function getCurrentServerInfo($sortBy = null): array{
		global $client;
		$out = [];
		
		// GAME
		if(SERVER_VERSION_NAME == 'TmForever'){
			$queryName = ['getMapInfo' => 'GetCurrentChallengeInfo'];
		}
		else{
			$queryName = ['getMapInfo' => 'GetCurrentMapInfo'];
		}
		
		// REQUESTS
		$client->addCall($queryName['getMapInfo']);
		if( AdminServAdminLevel::isType('Admin') ){
			$client->addCall('GetMapsDirectory');
		}
		// TM2020: GetGameMode() doesn't exist
		if( isTM2020() ){
			$client->addCall('GetModeScriptInfo');
		} else {
			$client->addCall('GetGameMode');
		}
		$client->addCall('GetServerName');
		$client->addCall('GetStatus');
		$client->addCall('GetCurrentCallVote');
		if( AdminServAdminLevel::isType('SuperAdmin') ){
			$client->addCall('GetNetworkStats');
		}
		$client->addCall('GetPlayerList', [AdminServConfig::LIMIT_PLAYERS_LIST, 0, 1] );
		
		if( !$client->multiquery() ){
			$out['error'] = Utils::t('Client not initialized');
		}
		else{
			// REQUEST DATA
			$queriesData = $client->getMultiqueryResponse();
			
			// GameMode
			// GameMode / ScriptName
			if( isTM2020() ){
				$modeInfo = $queriesData['GetModeScriptInfo'] ?? [];
				$out['srv']['gameModeId'] = 0;
				$out['srv']['gameModeName'] = 'Script';
				$out['srv']['gameModeScriptName'] = self::formatScriptName($modeInfo['Name'] ?? '');
			} else {
				$out['srv']['gameModeId'] = $queriesData['GetGameMode'];
				$out['srv']['gameModeName'] = self::getGameModeName($out['srv']['gameModeId']);
				$out['srv']['gameModeScriptName'] = null;
				if( self::isGameMode('Script', $out['srv']['gameModeId']) ){
					$client->query('GetModeScriptInfo');
					$getModeScriptInfo = $client->getResponse();
					if( isset($getModeScriptInfo['Name']) ){
						$out['srv']['gameModeScriptName'] = self::formatScriptName($getModeScriptInfo['Name']);
					}
				}
			}
			$displayTeamMode = self::checkDisplayTeamMode($out['srv']['gameModeId'], $out['srv']['gameModeScriptName']);
			
			// CurrentMapInfo
			$currentMapInfo = $queriesData[$queryName['getMapInfo']];
			$out['map']['name'] = TmNick::toHtml($currentMapInfo['Name'], 10, true, false, '#999');
			$out['map']['uid'] = $currentMapInfo['UId'];
			$out['map']['author'] = $currentMapInfo['Author'];
			$out['map']['enviro'] = $currentMapInfo['Environnement'];
			
			// Initialize the map thumbnail
			$out['map']['thumb'] = null;

			// Ensure the required data exists and is a string
			if (
				isset($queriesData['GetMapsDirectory'], $currentMapInfo['FileName']) &&
				is_string($queriesData['GetMapsDirectory']) &&
				is_string($currentMapInfo['FileName'])
			) {
				$mapFileName = $queriesData['GetMapsDirectory'] . $currentMapInfo['FileName'];

				if (file_exists($mapFileName)) {
					if (SERVER_VERSION_NAME === 'TmForever') {
						$Gbx = new GBXChallengeFetcher($mapFileName, false, true);
					} else {
						$Gbx = new GBXChallMapFetcher(false, true);
						$Gbx->processFile($mapFileName);
					}

					// Safely encode the thumbnail if it exists
					$out['map']['thumb'] = isset($Gbx->thumbnail) ? base64_encode((string) $Gbx->thumbnail) : null;
				}
			} else {
				// Log an error or handle the unexpected data type gracefully
				error_log('Invalid map directory or filename: ' . json_encode($queriesData) . ' | ' . json_encode($currentMapInfo));
			}

			
			$out['map']['callvote']['login'] = $queriesData['GetCurrentCallVote']['CallerLogin'];
			$out['map']['callvote']['cmdname'] = $queriesData['GetCurrentCallVote']['CmdName'];
			$out['map']['callvote']['cmdparam'] = $queriesData['GetCurrentCallVote']['CmdParam'];
			
			// TeamScores (team mode)
			if( self::isGameMode('Team', $out['srv']['gameModeId']) ){
				$client->query('GetCurrentRanking', 2, 0);
				$currentRanking = $client->getResponse();
				$out['map']['scores']['blue'] = $currentRanking[0]['Score'];
				$out['map']['scores']['red'] = $currentRanking[1]['Score'];
			}
			
			$out['srv']['name'] = TmNick::toHtml($queriesData['GetServerName'], 10, true, false, '#999');
			
			$out['srv']['status'] = $queriesData['GetStatus']['Name'];
			
			// NetworkStats
			if( isset($queriesData['GetNetworkStats']) && count($queriesData['GetNetworkStats']) > 0 ){
				$networkStats = $queriesData['GetNetworkStats'];
				$out['net']['uptime'] = TimeDate::secToStringTime($networkStats['Uptime'], false);
				$out['net']['nbrconnection'] = $networkStats['NbrConnection'];
				$out['net']['meanconnectiontime'] = TimeDate::secToStringTime($networkStats['MeanConnectionTime'], false);
				$out['net']['meannbrplayer'] = $networkStats['MeanNbrPlayer'];
				$out['net']['recvnetrate'] = $networkStats['RecvNetRate'];
				$out['net']['sendnetrate'] = $networkStats['SendNetRate'];
				$out['net']['totalreceivingsize'] = $networkStats['TotalReceivingSize'];
				$out['net']['totalsendingsize'] = $networkStats['TotalSendingSize'];
			}
			else{
				$out['net'] = null;
			}
			
			$playerList = $queriesData['GetPlayerList'];
			$countPlayerList = count($playerList);
			
			if( $countPlayerList > 0 ){
				$client->query('GetCurrentRanking', AdminServConfig::LIMIT_PLAYERS_LIST, 0);
				$rankingList = $client->GetResponse();
				$rankingKeyList = ['Rank', 'BestTime', 'BestCheckpoints', 'Score', 'NbrLapsFinished', 'LadderScore'];
				$i = 0;
				foreach($playerList as $player){
					// Nickname and Playerlogin
					$out['ply'][$i]['NickName'] = TmNick::toHtml(htmlspecialchars((string) $player['NickName'], ENT_QUOTES, 'UTF-8'), 10, true);
					$out['ply'][$i]['Login'] = $player['Login'];
					
					if($player['SpectatorStatus'] != 0){ $playerStatus = Utils::t('Spectator'); }else{ $playerStatus = Utils::t('Player'); }
					$out['ply'][$i]['PlayerStatus'] = $playerStatus;
					
					// Others
					$out['ply'][$i]['PlayerId'] = $player['PlayerId'];
					$out['ply'][$i]['TeamId'] = $player['TeamId'];
					if($player['TeamId'] == 0){ $teamName = Utils::t('Blue'); }else if($player['TeamId'] == 1){ $teamName = Utils::t('Red'); }else{ $teamName = Utils::t('Spectator'); }
					$out['ply'][$i]['TeamName'] = $teamName;
					$out['ply'][$i]['SpectatorStatus'] = $player['SpectatorStatus'];
					
					// Rankings
					foreach($rankingKeyList as $rankName){
						if( isset($rankingList[$i][$rankName]) ){
							$out['ply'][$i][$rankName] = $rankingList[$i][$rankName];
						}
					}
					if($player['LadderRanking'] == -1){
						$player['LadderRanking'] = Utils::t('Not rated');
					}
					$out['ply'][$i]['LadderRanking'] = $player['LadderRanking'];
					$i++;
				}
			}
			else{
				$out['ply'] = Utils::t('No player');
			}
			
			
			// Number of players
			if($countPlayerList > 1){
				$out['nbp'] = $countPlayerList.' '.Utils::t('players');
			}
			else{
				$out['nbp'] = $countPlayerList.' '.Utils::t('player');
			}
			
			
			// SORT
			if( is_array($out['ply']) && count($out['ply']) > 0 ){
				// If we're in team mode, sort by team
				if($displayTeamMode){
					uasort($out['ply'], 'AdminServSort::sortByRank');
					uasort($out['ply'], 'AdminServSort::sortByTeam');
				}
				else{
					switch($sortBy){
						case 'nickname':
							uasort($out['ply'], 'AdminServSort::sortByNickName');
							break;
						case 'ladder':
							uasort($out['ply'], 'AdminServSort::sortByLadderRanking');
							break;
						case 'login':
							uasort($out['ply'], 'AdminServSort::sortByLogin');
							break;
						case 'status':
							uasort($out['ply'], 'AdminServSort::sortByStatus');
							break;
						default:
							uasort($out['ply'], 'AdminServSort::sortByRank');
							uasort($out['ply'], 'AdminServSort::sortByStatus');
					}
				}
			}
		}
		
		return $out;
	}
	
	
	/**
	* Get the live ranking (GetCurrentRanking).
	* Works for ManiaPlanet, TM2020 and TmForever.
	*
	* @param int $maxInfos Maximum number of players to return (0 = all)
	* @param int $startIndex Starting index in the ranking
	*
	* @return array
	*/
	/**
	* Maps the script/game mode name to a set of results columns.
	* Column keys correspond to the fields returned by GetCurrentRanking.
	*/
	public static function getResultsColumns(string $modeName): array{
		$modeNameLow = strtolower($modeName);

		// TM2020 / ManiaPlanet -- detection based on script name
		if( str_contains($modeNameLow, 'timeattack') || str_contains($modeNameLow, 'time_attack') ){
			return ['Rank', 'NickName', 'Login', 'BestTime'];
		}
		if( str_contains($modeNameLow, 'laps') ){
			return ['Rank', 'NickName', 'Login', 'BestTime', 'NbrLapsFinished'];
		}
		if( str_contains($modeNameLow, 'knockout') ){
			return ['Rank', 'NickName', 'Login', 'BestTime'];
		}
		if( str_contains($modeNameLow, 'duos') || str_contains($modeNameLow, 'tmwt') ){
			return ['Rank', 'NickName', 'Login', 'Score'];
		}
		if( str_contains($modeNameLow, 'teams') || str_contains($modeNameLow, 'team') ){
			return ['Rank', 'NickName', 'Login', 'Score'];
		}
		if( str_contains($modeNameLow, 'reversecup') || str_contains($modeNameLow, 'reverse_cup') || str_contains($modeNameLow, 'reverse cup') ){
			return ['Rank', 'NickName', 'Login', 'Score'];
		}
		if( str_contains($modeNameLow, 'cup') ){
			return ['Rank', 'NickName', 'Login', 'Score'];
		}
		if( str_contains($modeNameLow, 'rounds') ){
			return ['Rank', 'NickName', 'Login', 'Score'];
		}
		if( str_contains($modeNameLow, 'stunts') ){
			return ['Rank', 'NickName', 'Login', 'Score'];
		}

		// Fallback -- show everything available, empty columns will be hidden by the frontend
		return ['Rank', 'NickName', 'Login', 'Score', 'BestTime', 'NbrLapsFinished'];
	}


	public static function getResults($maxInfos = null, $startIndex = 0): array{
		global $client;
		$out = [];

		// GetCurrentRanking: 0 as limit returns an empty list -- use a sensible limit
		if($maxInfos === null){
			$maxInfos = AdminServConfig::LIMIT_PLAYERS_LIST;
		}

		// Detect the current game mode / script name -- decides which columns to show
		$modeRawName = '';
		if( isTM2020() || (defined('SERVER_VERSION_NAME') && SERVER_VERSION_NAME == 'ManiaPlanet') ){
			if( $client->query('GetModeScriptInfo') ){
				$modeInfo = $client->getResponse();
				$modeRawName = self::formatScriptName($modeInfo['Name'] ?? '');
			}
		}
		else{
			// TmForever -- GetGameMode returns an integer, convert it to a name
			// (without $getManual=true -- initialize() already shifted ExtensionConfig::$GAMEMODES for TmForever)
			if( $client->query('GetGameMode') ){
				$gameModeId = $client->getResponse();
				$modeRawName = self::getGameModeName($gameModeId);
			}
		}
		$out['modeName'] = $modeRawName;
		$out['columns'] = self::getResultsColumns($modeRawName);

		if( !$client->query('GetCurrentRanking', $maxInfos, $startIndex) ){
			$out['error'] = Utils::t('Client not initialized');
			return $out;
		}

		$rankingList = $client->getResponse();
		$countRanking = count($rankingList);
		$isTeamMode = false;

		// Detect team mode (legacy: ranking = 2 entries, teams)
		if( $countRanking == 2 && isset($rankingList[0]['PlayerId']) && in_array($rankingList[0]['PlayerId'], [0, 1]) && !isset($rankingList[0]['Login']) ){
			$isTeamMode = true;
		}

		if( $countRanking > 0 ){
			$i = 0;
			foreach($rankingList as $ranking){
				$row = [];
				$row['Rank'] = $ranking['Rank'] ?? ($i + 1);
				$row['Login'] = $ranking['Login'] ?? null;
				$row['NickName'] = isset($ranking['NickName']) ? TmNick::toHtml(htmlspecialchars((string) $ranking['NickName'], ENT_QUOTES, 'UTF-8'), 10, true) : null;
				$row['PlayerId'] = $ranking['PlayerId'] ?? null;

				// Fields present only for legacy modes (Rounds, TimeAttack, Team, Laps, Cup, Stunts)
				// In TM2020 (Script), only Login/NickName/PlayerId/Rank are guaranteed.
				$row['Score'] = $ranking['Score'] ?? null;
				$row['BestTime'] = isset($ranking['BestTime']) && $ranking['BestTime'] >= 0 ? TimeDate::format($ranking['BestTime']) : null;
				$row['NbrLapsFinished'] = $ranking['NbrLapsFinished'] ?? null;
				$row['LadderScore'] = $ranking['LadderScore'] ?? null;

				// Best checkpoints (reference run)
				if( isset($ranking['BestCheckpoints']) && is_array($ranking['BestCheckpoints']) && count($ranking['BestCheckpoints']) > 0 ){
					$cpTimes = [];
					foreach($ranking['BestCheckpoints'] as $cpTime){
						$cpTimes[] = ($cpTime >= 0) ? TimeDate::format($cpTime) : '-';
					}
					$row['BestCheckpoints'] = $cpTimes;
				}
				else{
					$row['BestCheckpoints'] = null;
				}

				$out['lst'][$i] = $row;
				$i++;
			}
		}

		$out['count'] = $countRanking;
		$out['isTeamMode'] = $isTeamMode;
		if($countRanking == 0){ $out['lst'] = Utils::t('No player'); }

		return $out;
	}


	/**
	* @deprecated
	* Get the main server login from a Relay server
	*
	* @return string
	*/
	public static function getMainServerLoginFromRelay(){
		global $client;
		$out = null;
		
		if( AdminServAdminLevel::isType('Admin') ){
			if( !$client->query('GameDataDirectory') ){
				self::error();
			}
			else{
				// Login retrieval
				$out = null;
			}
		}
		
		return $out;
	}
	
	
	/**
     * Get the number of players present on the server
     *
     * @param bool $spectator -> Include spectators in the calculation
     */
    public static function getNbPlayers($spectator = true): int{
		global $client;
		$out = 0;
		
		if( !$client->query('GetPlayerList', AdminServConfig::LIMIT_PLAYERS_LIST, 0, 1) ){
			self::error();
		}
		else{
			$playerList = $client->getResponse();
			$countPlayerList = count($playerList);
			
			if($spectator){
				$out = $countPlayerList;
			}
			else{
				if($countPlayerList > 0){
					foreach($playerList as $player){
						if($player['SpectatorStatus'] == 0){
							$out++;
						}
					}
				}
			}
		}
		
		return $out;
	}
	
	
	/**
	* Quick administration
	*
	* @param  string $cmd -> The command name (PrevMap, RestartMap, NextMap or ForceEndRound)
	* @return true if successful, otherwise an error message
	*/
	public static function speedAdmin($cmd){
		global $client;
		$out = true;
		
		// Method depending on the game
		if($cmd != 'ForceEndRound'){
			if(SERVER_VERSION_NAME == 'TmForever'){
				$queries = ['restartMap' => 'RestartChallenge', 'nextMap' => 'NextChallenge', 'getCurrentMapIndex' => 'GetCurrentChallengeIndex', 'setNextMapIndex' => 'SetNextChallengeIndex'];
			}else{
				$queries = ['restartMap' => 'RestartMap', 'nextMap' => 'NextMap', 'getCurrentMapIndex' => 'GetCurrentMapIndex', 'setNextMapIndex' => 'SetNextMapIndex'];
			}
		}
		
		// If it's Cup mode
		$isCupMode = false;
		if( !isTM2020() && self::isGameMode('Cup') ){
			$isCupMode = true;
		}
		
		// Depending on the requested command
		switch($cmd){
			case 'PrevMap':
				if( !$client->query($queries['getCurrentMapIndex']) ){
					$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
				}
				else{
					$currentMapIndex = $client->getResponse();
					if($currentMapIndex === 0){
						$nbMaps = self::getNbMaps( self::getMapList() );
						$prevMapIndex =  $nbMaps['nbm']['count'] - 1;
					}
					else{
						$prevMapIndex = $currentMapIndex - 1;
					}
					
					if( !$client->query($queries['setNextMapIndex'], $prevMapIndex) ){
						$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
					}
					else{
						self::speedAdmin('NextMap');
					}
				}
				break;
			case 'RestartMap':
				if( !$client->query($queries['restartMap'], $isCupMode) ){
					$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
				}
				break;
			case 'NextMap':
				if( !$client->query($queries['nextMap'], $isCupMode) ){
					$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
				}
				break;
			case 'ForceEndRound':
				if($isCupMode){
					if( !$client->query($queries['nextMap']) ){
						$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
					}
				}
				elseif( isTM2020() ){
					$sent = false;
					foreach(['Trackmania.ForceEndRound', 'Maniaplanet.WarmUp.ForceStop'] as $event){
						if( $client->query('TriggerModeScriptEventArray', $event, []) ){ $sent = true; break; }
						$client->resetError();
					}
					if(!$sent){ $out = Utils::t('ForceEndRound is not available for this game mode in TM2020'); }
				}
				else{
					if( !$client->query('ForceEndRound') ){
						$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
					}
				}
				break;
			default:
				$out = Utils::t('Unknown command');
		}
		
		return $out;
	}
	
	
	/**
	* Get server options
	*
	* @global resource $client -> The client must be initialized
	* @return array
	*/
	public static function getServerOptions(){
		global $client;
		$out = [];
		
		if(SERVER_VERSION_NAME == 'TmForever'){
			$client->addCall('GetServerOptions', [1] );
		}
		else{
			$client->addCall('GetServerOptions');
		}
		$client->addCall('GetBuddyNotification', [''] );
		if(SERVER_VERSION_NAME == 'ManiaPlanet'){
			$client->addCall('AreHornsDisabled');
		}
		
		if( !$client->multiquery() ){
			self::error();
		}
		else{
			$queriesData = $client->getMultiqueryResponse();
			$out = $queriesData['GetServerOptions'];
			$out['Name'] = stripslashes((string) $out['Name']);
			if($out['Name'] == null){
				$out['Name'] = SERVER_NAME;
			}
			$out['NameHtml'] = TmNick::toHtml($out['Name'], 10, false, false, '#666');
			$out['Comment'] = stripslashes((string) $out['Comment']);
			$out['CommentHtml'] = TmNick::toHtml('$i'.nl2br($out['Comment']), 10, false, false, '#666');
			if( isset($out['CurrentLadderMode']) && $out['CurrentLadderMode'] !== 0){
				$out['CurrentLadderModeName'] = Utils::t('Forced'); 
			}
			else{
				$out['CurrentLadderModeName'] = isset($out['CurrentLadderMode']) ? Utils::t('Inactive') : null;
			}
			if( isset($out['CurrentVehicleNetQuality']) && $out['CurrentVehicleNetQuality'] !== 0){
				$out['CurrentVehicleNetQualityName'] = Utils::t('High');
			}
			else{
				$out['CurrentVehicleNetQualityName'] = isset($out['CurrentVehicleNetQuality']) ? Utils::t('Fast') : null;
			}
			$out['BuddyNotification'] = $queriesData['GetBuddyNotification'];
			if(SERVER_VERSION_NAME == 'ManiaPlanet'){
				$out['DisableHorns'] = $queriesData['AreHornsDisabled'];
			}
			else{
				$out['DisableHorns'] = null;
			}
		}
		
		return $out;
	}
	
	
	/**
	* Return the structure for saving the server options
	*
	* @return array
	*/
	public static function getServerOptionsStruct(){
		if(SERVER_VERSION_NAME == 'TmForever'){
			$keys = ['allowMapDownload' => 'AllowChallengeDownload'];
		}
		else{
			$keys = ['allowMapDownload' => 'AllowMapDownload'];
		}
		
		$out = ['Name' => stripslashes((string) $_POST['Name']), 'Comment' => stripslashes((string) $_POST['Comment']), 'Password' => trim((string) $_POST['Password']), 'PasswordForSpectator' => trim((string) $_POST['PasswordForSpectator']), 'NextMaxPlayers' => intval($_POST['NextMaxPlayers']), 'NextMaxSpectators' => intval($_POST['NextMaxSpectators']), 'IsP2PUpload' => array_key_exists('IsP2PUpload', $_POST), 'IsP2PDownload' => array_key_exists('IsP2PDownload', $_POST), 'NextLadderMode' => intval($_POST['NextLadderMode']), 'NextVehicleNetQuality' => intval($_POST['NextVehicleNetQuality']), 'NextCallVoteTimeOut' => TimeDate::secToMillisec( intval($_POST['NextCallVoteTimeOut']) ), 'CallVoteRatio' => (double)$_POST['CallVoteRatio'], $keys['allowMapDownload'] => array_key_exists('AllowMapDownload', $_POST), 'AutoSaveReplays' => array_key_exists('AutoSaveReplays', $_POST), 'HideServer' => (int)array_key_exists('HideServer', $_POST), 'BuddyNotification' => array_key_exists('BuddyNotification', $_POST)];
		if(SERVER_VERSION_NAME == 'ManiaPlanet'){
			$out['ClientInputsMaxLatency'] = ($_POST['ClientInputsMaxLatency'] == 'more') ? $_POST['ClientInputsMaxLatencyValue'] : $_POST['ClientInputsMaxLatency'];
			$out['DisableHorns'] = array_key_exists('DisableHorns', $_POST);
		}
		
		return $out;
	}
	
	
	/**
	* Save the server options
	*
	* @param array $struct -> Structure containing the fields required by the SetServerOptions() method
	*/
	public static function setServerOptions(array $struct){
		global $client;
		$out = false;
		
		if( !$client->query('SetServerOptions', $struct) ){
			self::error();
		}
		else{
			$client->addCall('SetHideServer', [$struct['HideServer']] );
			$client->addCall('SetBuddyNotification', ['', $struct['BuddyNotification']] );
			if(SERVER_VERSION_NAME == 'ManiaPlanet'){
				$client->addCall('SetClientInputsMaxLatency', [$struct['ClientInputsMaxLatency']] );
				$client->addCall('DisableHorns', [$struct['DisableHorns']] );
			}
			
			if( !$client->multiquery() ){
				self::error();
			}
			else{
				$out = true;
			}
		}
		
		return $out;
	}
	
	
	/**
	* Export the server options to a file
	*
	* @param  string $file -> Path of the file to write
	* @param  array  $data -> Server options structure to write
	* @return bool
	*/
	public static function exportServerOptions(string $file, $data){
		$out = false;
		
		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->formatOutput = true;
		$srvopts = $xml->createElement('ServerOptions');
		$srvopts = $xml->appendChild($srvopts);
		foreach($data as $dataField => $dataValue){
			$srvoptsElement = $xml->createElement($dataField, $dataValue);
			$srvoptsElementAttribute = $xml->createAttribute('type');
			$srvoptsElementAttribute->value = Str::getValueType($dataValue);
			$srvoptsElement->appendChild($srvoptsElementAttribute);
			$srvoptsElement = $srvopts->appendChild($srvoptsElement);
		}
		
		if( $result = $xml->save($file) > 0 ){
			$out = true;
			self::info( Utils::t('Server options are exported in').' '.$file);
		}
		else{
			self::error( Utils::t('Unable to export server options') );
		}
		
		return $out;
	}
	
	
	/**
     * Import the server options from a file
     *
     * @param  string $file -> Path of the file to read
     */
    public static function importServerOptions(string $file): array{
		global $client;
		$out = [];
		
		if( file_exists($file) ){
			$dom = new DOMDocument();
			$dom->load($file);
			$srvopts = $dom->childNodes->item(0);
			
			for($i = 0; $i < $srvopts->childNodes->length; $i++){
				$srvoptsElement = $srvopts->childNodes->item($i);
				if($srvoptsElement->nodeName != '#text'){
					$out[$srvoptsElement->nodeName] = Str::setValueType($srvoptsElement->nodeValue, $srvoptsElement->getAttribute('type') );
				}
			}
		}
		else{
			self::error( Utils::t('No such file or file is not readable').' : '.$file);
		}
		
		return $out;
	}
	
	
	/**
	* Get game information
	*
	* @global resource $client -> The client must be initialized
	* @return array
	*/
	public static function getGameInfos(){
		global $client;
		$out = [];

		// TM2020: GetGameInfos() doesn't exist -- we use GetModeScriptInfo
		if( isTM2020() ){
			$client->addCall('GetScriptName');
			$client->addCall('GetModeScriptInfo');
			$client->addCall('GetModeScriptSettings');
			$client->addCall('GetForceShowAllOpponents');

			if( !$client->multiquery() ){
				self::error();
			}
			else{
				$qd = $client->getMultiqueryResponse();
				$scriptName = $qd['GetScriptName']['CurrentValue'] ?? '';
				$modeInfo   = $qd['GetModeScriptInfo'];
				$settings   = $qd['GetModeScriptSettings'];
				$fsa        = $qd['GetForceShowAllOpponents'];

				$base = [
					'GameMode'              => 0,
					'ScriptName'            => $scriptName,
					'ForceShowAllOpponents' => $fsa['CurrentValue'] ?? 0,
					'ChatTime' => null, 'FinishTimeout' => null,
					'AllWarmUpDuration' => null, 'DisableRespawn' => null,
					'RoundsPointsLimit' => null, 'RoundCustomPoints' => null,
					'RoundsUseNewRules' => null, 'RoundsForcedLaps' => null,
					'TeamPointsLimit' => null, 'TeamMaxPoints' => null,
					'TeamUseNewRules' => null,
					'TimeAttackLimit' => null, 'TimeAttackSynchStartPeriod' => null,
					'LapsNbLaps' => null, 'LapsTimeLimit' => null,
					'CupPointsLimit' => null, 'CupRoundsPerMap' => null,
					'CupNbWinners' => null, 'CupWarmUpDuration' => null,
					'ModeScriptSettings' => $settings,
					'ModeScriptInfo'     => $modeInfo,
				];

				$out['curr'] = $base;
				$out['next'] = array_merge($base, [
					'ScriptName'            => $qd['GetScriptName']['NextValue'] ?? $scriptName,
					'ForceShowAllOpponents' => $fsa['NextValue'] ?? 0,
				]);
			}
			return $out;
		}

		// Game
		if(SERVER_VERSION_NAME == 'TmForever'){
			$queries = ['CupRoundsPerMap' => 'GetCupRoundsPerChallenge'];
		}
		else{
			$queries = ['CupRoundsPerMap' => 'GetCupRoundsPerMap'];
		}

		// Requests
		$client->addCall('GetGameInfos');
		$client->addCall('GetAllWarmUpDuration');
		$client->addCall('GetDisableRespawn');
		$client->addCall('GetForceShowAllOpponents');
		if(SERVER_VERSION_NAME == 'ManiaPlanet'){
			$client->addCall('GetScriptName');
		}
		$client->addCall('GetCupPointsLimit');
		$client->addCall($queries['CupRoundsPerMap']);
		$client->addCall('GetCupNbWinners');
		$client->addCall('GetCupWarmUpDuration');
		$client->addCall('GetRoundCustomPoints');
		
		if( !$client->multiquery() ){
			self::error();
		}
		else{
			$queriesData = $client->getMultiqueryResponse();
			
			// Game infos
			$currGamInf = $queriesData['GetGameInfos']['CurrentGameInfos'];
			$nextGamInf = $queriesData['GetGameInfos']['NextGameInfos'];
			
			// WarmUp count
			$currGamInf['AllWarmUpDuration'] = $queriesData['GetAllWarmUpDuration']['CurrentValue'];
			$nextGamInf['AllWarmUpDuration'] = $queriesData['GetAllWarmUpDuration']['NextValue'];
			
			$currGamInf['DisableRespawn'] = $queriesData['GetDisableRespawn']['CurrentValue'];
			$nextGamInf['DisableRespawn'] = $queriesData['GetDisableRespawn']['NextValue'];
			
			$currGamInf['ForceShowAllOpponents'] = $queriesData['GetForceShowAllOpponents']['CurrentValue'];
			$nextGamInf['ForceShowAllOpponents'] = $queriesData['GetForceShowAllOpponents']['NextValue'];
			
			// ScriptName
			$currGamInf['ScriptName'] = null;
			$nextGamInf['ScriptName'] = null;
			if(SERVER_VERSION_NAME == 'ManiaPlanet'){
				$currGamInf['ScriptName'] = $queriesData['GetScriptName']['CurrentValue'];
				$nextGamInf['ScriptName'] = $queriesData['GetScriptName']['NextValue'];
			}
			
			// Cup mode
			$currGamInf['CupPointsLimit'] = $queriesData['GetCupPointsLimit']['CurrentValue'];
			$nextGamInf['CupPointsLimit'] = $queriesData['GetCupPointsLimit']['NextValue'];
			$currGamInf['CupRoundsPerMap'] = $queriesData[$queries['CupRoundsPerMap']]['CurrentValue'];
			$nextGamInf['CupRoundsPerMap'] = $queriesData[$queries['CupRoundsPerMap']]['NextValue'];
			$currGamInf['CupNbWinners'] = $queriesData['GetCupNbWinners']['CurrentValue'];
			$nextGamInf['CupNbWinners'] = $queriesData['GetCupNbWinners']['NextValue'];
			$currGamInf['CupWarmUpDuration'] = $queriesData['GetCupWarmUpDuration']['CurrentValue'];
			$nextGamInf['CupWarmUpDuration'] = $queriesData['GetCupWarmUpDuration']['NextValue'];
			
			$RoundCustomPoints = implode(',', $queriesData['GetRoundCustomPoints']);
			$currGamInf['RoundCustomPoints'] = $RoundCustomPoints;
			$nextGamInf['RoundCustomPoints'] = $RoundCustomPoints;
			
			// Return
			$out['curr'] = $currGamInf;
			$out['next'] = $nextGamInf;
		}
		
		return $out;
	}
	
	
	/**
     * Return the structure for saving the game info
     */
    public static function getGameInfosStructFromPOST(): array{
		if($_POST['NextFinishTimeoutValue'] < 2){
			if($_POST['NextFinishTimeout'] == 0){ $FinishTimeout = 0; }
			else if($_POST['NextFinishTimeout'] == 1){ $FinishTimeout = 1; }
		}
		else{ $FinishTimeout = TimeDate::secToMillisec( intval($_POST['NextFinishTimeoutValue']) ); }
		if( array_key_exists('NextDisableRespawn', $_POST) === true ){ $DisableRespawn = false; }
		else{ $DisableRespawn = true; }
		if($_POST['NextForceShowAllOpponentsValue'] < 2){
			if($_POST['NextForceShowAllOpponents'] == 0){ $NextForceShowAllOpponents = 0; }
			else if($_POST['NextForceShowAllOpponents'] == 1){ $NextForceShowAllOpponents = 1; }
		}
		else{ $NextForceShowAllOpponents = intval($_POST['NextForceShowAllOpponentsValue']); }
		
		$out = ['GameMode' => intval($_POST['NextGameMode']), 'ChatTime' => TimeDate::secToMillisec( intval($_POST['NextChatTime'] - 8) ), 'RoundsPointsLimit' => intval($_POST['NextRoundsPointsLimit']), 'RoundCustomPoints' => intval($_POST['NextRoundCustomPoints']), 'RoundsUseNewRules' => array_key_exists('NextRoundsUseNewRules', $_POST), 'RoundsForcedLaps' => intval($_POST['NextRoundsForcedLaps']), 'RoundsPointsLimitNewRules' => intval($_POST['NextRoundsPointsLimit']), 'TimeAttackLimit' => TimeDate::secToMillisec( intval($_POST['NextTimeAttackLimit']) ), 'TimeAttackSynchStartPeriod' => TimeDate::secToMillisec( intval($_POST['NextTimeAttackSynchStartPeriod']) ), 'TeamPointsLimit' => intval($_POST['NextTeamPointsLimit']), 'TeamMaxPoints' => intval($_POST['NextTeamMaxPoints']), 'TeamUseNewRules' => array_key_exists('NextTeamUseNewRules', $_POST), 'TeamPointsLimitNewRules' => intval($_POST['NextTeamPointsLimit']), 'LapsNbLaps' => intval($_POST['NextLapsNbLaps']), 'LapsTimeLimit' => TimeDate::secToMillisec( intval($_POST['NextLapsTimeLimit']) ), 'FinishTimeout' => $FinishTimeout, 'AllWarmUpDuration' => intval($_POST['NextAllWarmUpDuration']), 'DisableRespawn' => $DisableRespawn, 'ForceShowAllOpponents' => $NextForceShowAllOpponents, 'CupPointsLimit' => intval($_POST['NextCupPointsLimit']), 'CupRoundsPerMap' => intval($_POST['NextCupRoundsPerMap']), 'CupNbWinners' => intval($_POST['NextCupNbWinners']), 'CupWarmUpDuration' => intval($_POST['NextCupWarmUpDuration'])];
		if(SERVER_VERSION_NAME != 'TmForever'){
			$out += ['ScriptName' => $_POST['NextScriptName']];
		}
		
		return $out;
	}
	
	
	/**
     * Save team info
     * @param array $team1 -> (assoc) array(name, color (0 to 1), country)
     * @return bool
     */
    public static function setTeamInfo(array $team1, array $team2){
		global $client;
		$out = false;
		
		if( !$client->query('SetTeamInfo', 'Unused', 0., 'World', $team1['name'], (double)$team1['color'], $team1['country'], $team2['name'], (double)$team2['color'], $team2['country']) ){
			AdminServ::error();
		}
		else{
			$_SESSION['adminserv']['teaminfo'] = ['team1' => $team1, 'team2' => $team2];
			$out = true;
		}
		
		return $out;
	}
	
	
	/**
	* Add chat line on server
	*
	* @param string $message       -> Text message
	* @param string $nickname      -> Nickname
	* @param string $color         -> Text color
	* @param string $destination   -> Message destination: server or player login
	* @param string $showAdminText -> Display "Admin" before the message
	* @return bool or text error
	*/
	public static function addChatServerLine($message, $nickname = null, string $color = '$ff0', $destination = 'server', $showAdminText = false){
		global $client;
		$out = false;
		$admin = null;
		Utils::addCookieData('adminserv_user', [AdminServUI::theme(), AdminServUI::lang(), $nickname, $color], AdminServConfig::COOKIE_EXPIRE);
		
		if($showAdminText){
			$admin = '$fffAdmin:';
		}
		
		if($nickname){
			$nickname = '$g$ff0'.TmNick::stripNadeoCode($nickname, ['$s'] );
		}
		
		$nickname = '$s$ff0['.$admin.$nickname.'$z$s$ff0]$z';
		$message = $nickname.' '.$color.$message;
		$_SESSION['adminserv']['chat_dst'] = $destination;
		
		if($destination === 'server'){
			if( !$client->query('ChatSendServerMessage', $message) ){
				$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
			}
			else{
				$out = true;
			}
		}
		else{
			if( !$client->query('ChatSendServerMessageToLogin', $message, $destination) ){
				$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
			}
			else{
				$out = true;
			}
		}
		
		return $out;
	}
	
	
	/**
	* Get server chat lines
	*
	* @param bool $hideServerLines -> Hide lines coming from a server manager
	* @return string
	*/
	public static function getChatServerLines($hideServerLines = false): ?string{
		global $client;
		$out = null;

		if( !$client->query('GetChatLines') ){
			$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
		}
		else{
			$chatLines = $client->getResponse();

			foreach($chatLines as $line){
				if( self::isServerLine($line) ){
					if($hideServerLines){ continue; }

					// Server line -- keep TM formatting, only remove $< and $>
					$line = str_replace(['$<', '$>'], '', (string) $line);
					$out .= TmNick::toHtml($line, 10, false, false, '#999');
				}
				else{
					// Player message: format $<Nickname$>: content
					// Split at $> to separate the nick from the message
					$parts = explode('$>', (string) $line, 2);

					// Nick: remove $< (context wrapper), keep TM codes
					$nickname = str_replace(['[$<', '$<', '['], '', $parts[0]);

					// Message: keep full TM formatting (substr removes ': ')
					$message = isset($parts[1]) ? substr($parts[1], 2) : '';

					// Combine into one string: yellow bracket + nick with TM codes + message with TM codes
					$fullLine = '$ff0$o['.$nickname.'$z$ff0$o]$z '.$message;
					$out .= TmNick::toHtml($fullLine, 10, false, false, '#ccc');
				}
			}
		}

		return $out;
	}
	
	
	/**
	* Return true if the line was generated by the server
	*
	* @param string $line -> The line from the GetChatLines response
	* @return bool
	*/
	public static function isServerLine($line){
		$out = false;
		$char = substr(mb_convert_encoding($line, 'ISO-8859-1'), 0, 1);
		
		if($char == '<' || $char == '/' || str_starts_with($line, '$99F') || str_starts_with($line, 'Invalid time') || $char == '?'){
			$out = true;
		}
		
		return $out;
	}
	
	
	/**
	* Get the path of the "Maps" folder
	*
	* @global resource $client -> The client must be initialized
	* @return string
	*/
	public static function getMapsDirectoryPath(){
		global $client;
		$out = null;
		
		if(SERVER_VERSION_NAME == 'TmForever'){
			$queryName = 'GetTracksDirectory';
		}
		else{
			$queryName = 'GetMapsDirectory';
		}
		
		if( !$client->query($queryName) ){
			$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
		}
		else{
			$out = Str::toSlash( $client->getResponse() );
			if( !str_ends_with($out, '/')){ $out .= '/'; }
			if(SERVER_MAPS_BASEPATH){
				$out .= Str::toSlash(SERVER_MAPS_BASEPATH);
				if( !str_ends_with($out, '/')){ $out .= '/'; }
			}
		}
		
		return $out;
	}
	
	
	/**
     * Return an array with the map count and title
     *
     * @param array $array -> The array containing the map list
     */
    public static function getNbMaps($array): array{
		$out = [];
		
		if( isset($array['lst']) && is_array($array['lst']) ){
			$countMapsList = count($array['lst']);
		}
		else{
			$countMapsList = 0;
		}
		
		$out['nbm']['count'] = $countMapsList;
		if($countMapsList > 1){
			$out['nbm']['title'] = Utils::t('maps');
		}
		else{
			$out['nbm']['title'] = Utils::t('map');
		}
		
		return $out;
	}
	
	
	/**
	* Get the list of maps on the server
	*
	* @global resource $client -> The client must be initialized
	* @param  string   $sortBy -> Sort order to apply to the list
	* @return array
	*/
	public static function getMapList($sortBy = null){
		global $client;
		$out = [];
		
		// Methods
		if(SERVER_VERSION_NAME == 'TmForever'){
			$queryName = ['mapList' => 'GetChallengeList', 'mapIndex' => 'GetCurrentChallengeIndex'];
		}
		else{
			$queryName = ['mapList' => 'GetMapList', 'mapIndex' => 'GetCurrentMapIndex'];
		}
		
		// MAPSLIST
		if( !$client->query($queryName['mapList'], AdminServConfig::LIMIT_MAPS_LIST, 0) ){
			$out['error'] = Utils::t('Client not initialized');
		}
		else{
			$mapList = $client->getResponse();
			$countMapList = count($mapList);
			$client->query($queryName['mapIndex']);
			$out['cid'] = $client->getResponse();
			
			if( $countMapList > 0 ){
				$i = 0;
				foreach($mapList as $map){
					$name = htmlspecialchars((string) $map['Name'], ENT_QUOTES, 'UTF-8');
					$out['lst'][$i]['Name'] = TmNick::toHtml($name, 10, true);
					
					// Environment
					$env = $map['Environnement'];
					if($env == 'Speed'){ $env = 'Desert'; }else if($env == 'Alpine'){ $env = 'Snow'; }
					$out['lst'][$i]['Environment'] = $env;
					
					// Others
					$out['lst'][$i]['UId'] = $map['UId'];
					$out['lst'][$i]['FileName'] = $map['FileName'];
					$out['lst'][$i]['Author'] = $map['Author'];
					$out['lst'][$i]['GoldTime'] = TimeDate::format($map['GoldTime']);
					$out['lst'][$i]['CopperPrice'] = $map['CopperPrice'];
					if(SERVER_VERSION_NAME == 'ManiaPlanet'){
						$out['lst'][$i]['Type']['Name'] = self::formatScriptName($map['MapType']);
						$out['lst'][$i]['Type']['FullName'] = $map['MapType'];
						$out['lst'][$i]['Style']['Name'] = self::formatScriptName($map['MapStyle']);
						$out['lst'][$i]['Style']['FullName'] = $map['MapStyle'];
					}
					$i++;
				}
			}
			
			// Number of maps
			$out += self::getNbMaps($out);
			if($out['nbm']['count'] == 0){
				$out['lst'] = Utils::t('No map');
			}
			
			
			// SORT
			if($sortBy != null){
				if( is_array($out['lst']) && count($out['lst']) > 0 ){
					switch($sortBy){
						case 'name':
							uasort($out['lst'], 'AdminServSort::sortByName');
							break;
						case 'env':
							uasort($out['lst'], 'AdminServSort::sortByEnviro');
							break;
						case 'author':
							uasort($out['lst'], 'AdminServSort::sortByAuthor');
							break;
						case 'goldtime':
							uasort($out['lst'], 'AdminServSort::sortByGoldTime');
							break;
						case 'cost':
							uasort($out['lst'], 'AdminServSort::sortByPrice');
							break;
					}
				}
				$out['lst'] = array_values($out['lst']);
			}
		}
		
		return $out;
	}
	
	
	/**
     * Get the list of maps on the server and return one specific field
     *
     * @global resource $client -> The client must be initialized
     */
    public static function getMapListField($field): array{
		global $client;
		$out = [];
		
		// Methods
		if(SERVER_VERSION_NAME == 'TmForever'){
			$queryName = ['mapList' => 'GetChallengeList'];
		}
		else{
			$queryName = ['mapList' => 'GetMapList'];
		}
		
		// Mapslist
		if( !$client->query($queryName['mapList'], AdminServConfig::LIMIT_MAPS_LIST, 0) ){
			$out['error'] = Utils::t('Client not initialized');
		}
		else{
			$mapList = $client->getResponse();
			$countMapList = count($mapList);
			if( $countMapList > 0 ){
				$i = 0;
				foreach($mapList as $map){
					switch($field){
						case 'Name':
							$name = htmlspecialchars((string) $map['Name'], ENT_QUOTES, 'UTF-8');
							$out[] = TmNick::toHtml($name, 10, true);
							break;
						case 'Environment':
							$env = $map['Environnement'];
							if($env == 'Speed'){ $env = 'Desert'; }else if($env == 'Alpine'){ $env = 'Snow'; }
							$out[] = $env;
							break;
						case 'UId':
							$out[] = $map['UId'];
							break;
						case 'FileName':
							$out[] = $map['FileName'];
							break;
						case 'Author':
							$out[] = $map['Author'];
							break;
						case 'GoldTime':
							$out[] = TimeDate::format($map['GoldTime']);
							break;
						case 'CopperPrice':
							$out[] = $map['CopperPrice'];
							break;
						case 'MapType':
							$out[] = $map['MapType'];
							break;
						case 'MapStyle':
							$out[] = $map['MapStyle'];
							break;
					}
					$i++;
				}
			}
		}
		
		return $out;
	}
	
	
	/**
	* Get the list of local maps from a path
	*
	* @param string $path   -> Path of the folder to list
	* @param string $sortBy -> Sort order to apply to the list
	* @return array
	*/
	public static function getLocalMapList($directory, string $currentPath, $sortBy = null){
		global $client;
		$out = [];
		
		if( is_array($directory) ){
			if( !empty($directory['files']) ){
				// Retrieve existing cache
				$mapsDirectoryPath = self::getMapsDirectoryPath();
				$cache = new AdminServCache();
				$cacheKey = 'mapslist-'.Str::replaceChars($mapsDirectoryPath.$currentPath);
				$cacheMaps = $cache->get($cacheKey);
				
				// Files
				$files = [];
				foreach($directory['files'] as $fileName => $fileValues){
					$dbExt = File::getDoubleExtension($fileName);
					if( in_array($dbExt, AdminServConfig::$MAP_EXTENSION) ){
						$files[$fileName] = $fileValues;
					}
				}
				
				// Remove excess cache
				$cacheOverFiles = array_diff_key($cacheMaps, $files);
				if( !empty($cacheOverFiles) ){
					foreach($cacheOverFiles as $fileName => $fileValues){
						if( isset($cacheMaps[$fileName]) ){
							unset($cacheMaps[$fileName]);
						}
					}
					$cache->set($cacheKey, $cacheMaps);
				}
				
				// Add missing files to the cache
				$cacheMissingFiles = array_diff_key($files, $cacheMaps);
				if( !empty($cacheMissingFiles) ){
					$path = $mapsDirectoryPath.$currentPath;
					
					// Cache creation
					foreach($cacheMissingFiles as $file => $values){
						// Data
						$processFile = mb_convert_encoding($file, 'ISO-8859-1');
						if(SERVER_VERSION_NAME == 'TmForever'){
							$Gbx = new GBXChallengeFetcher($path.$processFile);
						}
						else{
							$Gbx = new GBXChallMapFetcher();
							$Gbx->processFile($path.$processFile);
						}
						
						// Name
						$filename = $Gbx->name;
						if($filename == 'read error'){
							$filename = str_ireplace('.'.$dbExt, '', $file);
						}
						$name = htmlspecialchars((string) $filename, ENT_QUOTES, 'UTF-8');
						$out['lst'][$file]['Name'] = TmNick::toHtml($name, 10, true);
						
						// Environment
						$env = $Gbx->envir;
						if($env == 'read error'){ $env = null; }
						if($env == 'Speed'){ $env = 'Desert'; }else if($env == 'Alpine'){ $env = 'Snow'; }
						$out['lst'][$file]['Environment'] = $env;
						
						// Others
						$out['lst'][$file]['FileName'] = $currentPath.$file;
						$uid = $Gbx->uid;
						if($uid == 'read error'){ $uid = null; }
						$out['lst'][$file]['UId'] = $uid;
						$author = $Gbx->author;
						if($author == 'read error'){ $author = null; }
						$out['lst'][$file]['Author'] = $author;
						$out['lst'][$file]['Recent'] = $values['recent'];
						
						// MapType
						$mapType = $Gbx->mapType;
						if($mapType == null && $Gbx->typeName != null){
							$mapType = $Gbx->typeName;
						}
						$out['lst'][$file]['Type']['Name'] = self::formatScriptName($mapType);
						$out['lst'][$file]['Type']['FullName'] = $mapType;
					}
					
					// Cache update
					if( !empty($cacheMaps) ){
						$out['lst'] = array_merge($cacheMaps, $out['lst']);
					}
					$cache->set($cacheKey, $out['lst']);
				}
				else{
					$out['lst'] = $cacheMaps;
				}
                $currentMapsListUId = self::getMapListField('UId');
				foreach($out['lst'] as &$file){
					$file['OnServer'] = false;
					if($currentMapsListUId){
						if( in_array($file['UId'], $currentMapsListUId) ){
							$file['OnServer'] = true;
						}
					}
				}
				
				// Number of maps
				$out += self::getNbMaps($out);
				if($out['nbm']['count'] == 0){
					$out['lst'] = Utils::t('No map');
				}
				else{
					// SORTS
					if($sortBy != null){
						switch($sortBy){
							case 'filename':
								uasort($out['lst'], 'AdminServSort::sortByFileName');
								break;
							case 'name':
								uasort($out['lst'], 'AdminServSort::sortByName');
								break;
							case 'env':
								uasort($out['lst'], 'AdminServSort::sortByEnviro');
								break;
							case 'type':
								uasort($out['lst'], 'AdminServSort::sortByType');
								break;
							case 'author':
								uasort($out['lst'], 'AdminServSort::sortByAuthor');
								break;
						}
					}
				}
			}
			else{
				$out += self::getNbMaps($out);
				$out['lst'] = Utils::t('No map');
			}
		}
		// Return errors from the read method
		else{
			$out = $directory;
		}
		
		return $out;
	}
	
	
	/**
	* Get the list of local matchsettings from a path
	*
	* @param string $path -> Path of the folder to list
	* @return array
	*/
	public static function getLocalMatchSettingList($directory, string $currentPath){
		$out = [];
		
		if( is_array($directory) ){
			if( !empty($directory['files']) ){
				$mapsDirectoryPath = self::getMapsDirectoryPath();
				
				foreach($directory['files'] as $file => $values){
					if( in_array(File::getExtension($file), AdminServConfig::$MATCHSET_EXTENSION) ){
						$matchsetData = self::getMatchSettingsData($mapsDirectoryPath.$currentPath.$file, ['maps']);
						$matchsetNbmCount = 0;
						if( isset($matchsetData['maps']) ){
							$matchsetNbmCount = count($matchsetData['maps']);
						}
						$matchsetNbmTitle = ($matchsetNbmCount > 1) ? Utils::t('maps') : Utils::t('map');
						
						$out['lst'][$file]['Name'] = substr((string) $file, 0, -4);
						$out['lst'][$file]['FileName'] = $currentPath.$file;
						$out['lst'][$file]['Nbm'] = $matchsetNbmCount.' '.$matchsetNbmTitle;
						$out['lst'][$file]['Mtime'] = $values['mtime'];
						$out['lst'][$file]['Recent'] = $values['recent'];
					}
				}
			}
			
			// Number of matchsettings
			if( isset($out['lst']) && is_array($out['lst']) ){
				$out['nbm']['count'] = count($out['lst']);
				$out['nbm']['title'] = ($out['nbm']['count'] > 1) ? Utils::t('matchsettings') : Utils::t('matchsetting');
			}
			else{
				$out['nbm']['count'] = 0;
				$out['nbm']['title'] = Utils::t('matchsetting');
			}
			if($out['nbm']['count'] == 0){
				$out['lst'] = Utils::t('No matchsetting');
			}
		}
		else{
			// Return errors from the read method
			$out = $directory;
		}
		
		return $out;
	}
	
	
	/**
	* Save the MatchSettings selection to the session
	*
	* @param array $maps -> Array of maps to add to the selection
	*/
	public static function saveMatchSettingSelection($maps = [] ): void{
		// List of maps
		$out = [];
		if( isset($_SESSION['adminserv']['matchset_maps_selected']) ){
			$mapsSelected = $_SESSION['adminserv']['matchset_maps_selected'];
			if( isset($mapsSelected['lst']) && is_array($mapsSelected['lst']) && count($mapsSelected['lst']) > 0 ){
				foreach($mapsSelected['lst'] as $values){
					$out['lst'][] = $values;
				}
			}
		}
		if( isset($maps['lst']) && is_array($maps['lst']) && count($maps['lst']) > 0 ){
			foreach($maps['lst'] as $values){
				$out['lst'][] = $values;
			}
		}
		if( isset($out['lst']) && count($out['lst']) > 0 ){
			$out['lst'] = array_unique($out['lst'], SORT_REGULAR);
		}
		
		// Number of maps
		$out += self::getNbMaps($out);
		if($out['nbm']['count'] == 0){
			$out['lst'] = Utils::t('No map');
		}
		
		// Session update
		$_SESSION['adminserv']['matchset_maps_selected'] = $out;
	}
	
	
	/**
	* Save a MatchSettings
	*
	* @param string $filename -> The path of the folder in which the MatchSettings will be created
	* @param array  $struct   -> The MatchSettings structure with its data
	* $struct = Array
	* (
	*  [gameinfos] => Array
	*   (
	*    [game_mode] => 0
	*    etc...
	*   )
	*  [hotseat] => Array()
	*  [filter] => Array()
	*  [startindex] => 1
	*  [map] => Array
	*   (
	*    [8bDoQMwzUllV0D9eu7hSth3rQs6] => name.Map.Gbx
	*    etc...
	*   )
	* )
	* @return true if successful, otherwise an error
	*/
	public static function saveMatchSettings($filename, $struct){
		$out = false;
		
		if(SERVER_VERSION_NAME == 'TmForever'){
			$mapField = 'challenge';
		}
		else{
			$mapField = 'map';
		}
		
		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->formatOutput = true;
		$playlist = $xml->createElement('playlist');
		$playlist = $xml->appendChild($playlist);
		
		// GameInfos, Hotseat, Filter
		$structFields = ['gameinfos', 'hotseat', 'filter'];
		foreach($structFields as $strucField){
			if( isset($struct[$strucField]) && !empty($struct[$strucField]) ){
				$node = $xml->createElement($strucField);
				$node = $playlist->appendChild($node);
				foreach($struct[$strucField] as $field => $value){
					$childNode = $xml->createElement($field, $value);
					$childNode = $node->appendChild($childNode);
				}
			}
		}
		
		// Script settings
		if( isset($struct['scriptsettings']) && !empty($struct['scriptsettings']) ){
			$scriptsettings = $xml->createElement('mode_script_settings');
			$scriptsettings = $playlist->appendChild($scriptsettings);
			foreach($struct['scriptsettings'] as $settingParams){
				$setting = $xml->createElement('setting');
				$setting = $scriptsettings->appendChild($setting);
				if( !empty($settingParams) ){
					foreach($settingParams as $paramKey => $paramValue){
						$param = $xml->createAttribute($paramKey);
						$param->value = $paramValue;
						$setting->appendChild($param);
					}
				}
			}
		}
		
		// Maps
		$startindex = $xml->createElement('startindex', $struct['startindex']);
		$playlist->appendChild($startindex);
		if( isset($struct[$mapField]) && !empty($struct[$mapField]) ){
			foreach($struct[$mapField] as $dataIdent => $dataFile){
				$map = $xml->createElement($mapField);
				$map = $playlist->appendChild($map);
				$file = $xml->createElement('file', $dataFile);
				$file = $map->appendChild($file);
				if(SERVER_VERSION_NAME == 'TmForever'){
					$ident = $xml->createElement('ident', $dataIdent);
					$ident = $map->appendChild($ident);
				}
			}
		}
		
		if( !$xml->save($filename) ){
			$out = Utils::t('Saving XML file error');
		}
		else{
			$out = true;
		}
		
		return $out;
	}
	
	
	/**
	* Extract data from a MatchSettings and return an array
	*
	* @param string $filename -> The MatchSettings path
	* @param array  $list     -> List of fields to return
	* @return array
	*/
	public static function getMatchSettingsData($filename, $list = ['gameinfos', 'hotseat', 'filter', 'scriptsettings', 'maps'] ){
		$out = [];
		$xml = null;
		
		if( file_exists($filename) ){
			$xml = new DOMDocument('1.0', 'utf-8');
			$xml->load($filename);
		}
		
		if($xml){
			// Gameinfos
			if( in_array('gameinfos', $list) ){
				$fields = ['game_mode' => 'GameMode', 'chat_time' => 'ChatTime', 'finishtimeout' => 'FinishTimeout', 'allwarmupduration' => 'AllWarmUpDuration', 'disablerespawn' => 'DisableRespawn', 'forceshowallopponents' => 'ForceShowAllOpponents', 'rounds_pointslimit' => 'RoundsPointsLimit', 'rounds_custom_points' => 'RoundCustomPoints', 'rounds_usenewrules' => 'RoundsUseNewRules', 'rounds_forcedlaps' => 'RoundsForcedLaps', 'rounds_pointslimitnewrules' => 'RoundsPointsLimitNewRules', 'team_pointslimit' => 'TeamPointsLimit', 'team_maxpoints' => 'TeamMaxPoints', 'team_usenewrules' => 'TeamUseNewRules', 'team_pointslimitnewrules' => 'TeamPointsLimitNewRules', 'timeattack_limit' => 'TimeAttackLimit', 'timeattack_synchstartperiod' => 'TimeAttackSynchStartPeriod', 'laps_nblaps' => 'LapsNbLaps', 'laps_timelimit' => 'LapsTimeLimit', 'cup_pointslimit' => 'CupPointsLimit', 'cup_roundsperchallenge' => 'CupRoundsPerMap', 'cup_nbwinners' => 'CupNbWinners', 'cup_warmupduration' => 'CupWarmUpDuration'];
				if(SERVER_VERSION_NAME != 'TmForever'){
					$fields['script_name'] = 'ScriptName';
				}
				
				foreach($fields as $fieldXML => $fieldName){
					$fieldList = $xml->getElementsByTagName($fieldXML);
					if($fieldList->length > 0){
						$out['gameinfos'][$fieldName] = $fieldList->item(0)->nodeValue;
					}
				}
			}
			
			if( in_array('hotseat', $list) ){
				$fields = ['game_mode' => 'GameMode', 'time_limit' => 'TimeLimit', 'rounds_count' => 'RoundsCount'];
				
				foreach($fields as $fieldXML => $fieldName){
					$fieldList = $xml->getElementsByTagName($fieldXML);
					if($fieldList->length > 0){
						$out['hotseat'][$fieldName] = $fieldList->item(0)->nodeValue;
					}
				}
			}
			
			if( in_array('filter', $list) ){
				$fields = ['is_lan' => 'IsLan', 'is_internet' => 'IsInternet', 'is_solo' => 'IsSolo', 'is_hotseat' => 'IsHotseat', 'sort_index' => 'SortIndex', 'random_map_order' => 'RandomMapOrder', 'force_default_gamemode' => 'ForceDefaultGameMode'];
				
				foreach($fields as $fieldXML => $fieldName){
					$fieldList = $xml->getElementsByTagName($fieldXML);
					if($fieldList->length > 0){
						$out['filter'][$fieldName] = $fieldList->item(0)->nodeValue;
					}
				}
			}
			
			// Script Settings
			if( in_array('scriptsettings', $list) ){
				$scriptsettings = $xml->getElementsByTagName('setting');
				if($scriptsettings->length > 0){
					$i = 0;
					foreach($scriptsettings as $setting){
						if( $setting->hasAttributes() ){
							foreach($setting->attributes as $attName => $attrNode) {
								$out['scriptsettings'][$i][$attName] = $attrNode->value;
							}
						}
						$i++;
					}
				}
			}
			
			// Maps
			if( in_array('maps', $list) ){
				$fieldStartIndex = $xml->getElementsByTagName('startindex');
				if($fieldStartIndex->length > 0){
					$out['StartIndex'] = $fieldStartIndex->item(0)->nodeValue;
				}
				
				$mapsField = (SERVER_VERSION_NAME == 'TmForever') ? 'challenge' : 'map';
				$fieldMaps = $xml->getElementsByTagName($mapsField);
				
				foreach($fieldMaps as $map){
					$ident = null;
					$fieldIdent = $map->getElementsByTagName('ident');
					if($fieldIdent->length > 0){
						$ident = $fieldIdent->item(0)->nodeValue;
					}
					$file = null;
					$fieldFile = $map->getElementsByTagName('file');
					if($fieldFile->length > 0){
						$file = $fieldFile->item(0)->nodeValue;
					}
					
					if($ident){
						$out['maps'][$ident] = $file;
					}
					else{
						$out['maps'][] = $file;
					}
				}
			}
		}
		
		return $out;
	}
	
	
	/**
     * Format map data from a MatchSettings
     *
     * @global resource $client -> The client must be initialized
     * @param  array    $maps   -> The array extracted from the matchsettings: assoc array(ident => filename)
     */
    public static function getMapListFromMatchSetting($maps): array{
		global $client;
		$out = [];
		$path = self::getMapsDirectoryPath();
		$countMapList = count($maps);
		
		if($countMapList > 0){
			$i = 0;
			foreach($maps as $mapFileName){
				if( in_array(File::getDoubleExtension($mapFileName), AdminServConfig::$MAP_EXTENSION) ){
					// Data
					if(SERVER_VERSION_NAME == 'TmForever'){
						$Gbx = new GBXChallengeFetcher($path.Str::toSlash($mapFileName));
					}
					else{
						$Gbx = new GBXChallMapFetcher();
						$Gbx->processFile($path.Str::toSlash($mapFileName));
					}
					
					$name = htmlspecialchars((string) $Gbx->name, ENT_QUOTES, 'UTF-8');
					$out['lst'][$i]['Name'] = TmNick::toHtml($name, 10, true);
					
					// Environment
					$env = $Gbx->envir;
					if($env == 'Speed'){ $env = 'Desert'; }else if($env == 'Alpine'){ $env = 'Snow'; }
					$out['lst'][$i]['Environment'] = $env;
					
					// Others
					$out['lst'][$i]['FileName'] = $mapFileName;
					$out['lst'][$i]['UId'] = $Gbx->uid;
					$out['lst'][$i]['Author'] = $Gbx->author;
					$i++;
				}
			}
		}
		
		// Number of maps
		$out += self::getNbMaps($out);
		if($out['nbm']['count'] == 0){
			$out['lst'] = Utils::t('No map');
		}
		
		return $out;
	}
	
	
	/**
	* Extract data from a playlist (blacklist or guestlist)
	*
	* @param string $filename -> The playlist path
	* @return array
	*/
	public static function getPlaylistData($filename){
		$out = [];
		
		$xml = null;
		if( file_exists($filename) ){
			$xml = new DOMDocument('1.0', 'utf-8');
			$xml->load($filename);
		}
		
		if($xml){
			$root = $xml->documentElement;
			$out['type'] = $root->nodeName;
			$players = $xml->getElementsByTagName($root->nodeName);
			foreach($players as $player){
				$login = $player->getElementsByTagName('login');
				if($login->length > 0){
					$out['logins'][] = $login->item(0)->nodeValue;
				}
			}
		}
		
		return $out;
	}
}
?>
