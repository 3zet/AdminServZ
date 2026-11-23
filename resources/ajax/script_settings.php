<?php
	// JSON endpoint: PHP notices must never reach the response body (they still go to the error log)
	ini_set('display_errors', '0');
	// INCLUDES
	session_start();
	if( !isset($_SESSION['adminserv']['sid']) ){ exit; }
	$configPath = '../../'.$_SESSION['adminserv']['path'].'config/';
	require_once $configPath.'adminlevel.cfg.php';
	require_once $configPath.'adminserv.cfg.php';
	require_once $configPath.'extension.cfg.php';
	require_once $configPath.'servers.cfg.php';
	require_once '../core/adminserv.php';
	AdminServConfig::$PATH_RESOURCES = '../';
	AdminServ::getClass();

	// ISSET
	if( isset($_POST['method']) ){ $method = $_POST['method']; }else{ $method = null; }
	if( isset($_POST['params']) ){ $params = $_POST['params']; }else{ $params = null; }

	// DATA
	$out = null;
	if( AdminServ::initialize() ){
		// TM2020: same ModeScript API as ManiaPlanet -- extend the check
		if( isMpApi() ){
			if($method == 'set' && $params != null){
				$scriptSettings = [];
				if( count($params) > 0 ){
					foreach($params as $param){
						$scriptSettings[$param['name']] = Str::setValueType($param['value'], $param['type']);
					}
				}

				if( !$client->query('SetModeScriptSettings', $scriptSettings) ){
					$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
				}
				else{
					$out = true;
				}
			}
			elseif($method == 'getcurrent'){
				// Return the current setting values (GetModeScriptSettings), not the definitions
				if( !$client->query('GetModeScriptSettings') ){
					$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
				}
				else{
					$out = $client->getResponse();
				}
			}
			else{
				// Default: GetModeScriptInfo (parameter definitions + default values)
				if( !$client->query('GetModeScriptInfo') ){
					$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
				}
				else{
					$out = $client->getResponse();

					// Extended description dictionary for known TM2020 parameters
					// Merged with the API description (API wins if non-empty)
					$extraDescs = [
						// === COMMON / GENERAL ===
						'S_ChatTime'                    => 'Time in seconds before the next map loads after a map ends.',
						'S_UseClublinks'                => 'Use clublinks to assign players to teams instead of the default team assignment.',
						'S_UseClublinksSponsors'        => 'Display club sponsors in the interface.',
						'S_NeutralEmblemUrl'            => 'URL to an image used as the neutral emblem (overrides the Nadeo logo).',
						'S_ScriptEnvironment'           => 'Execution environment: Production or Development. Development enables extra debug output.',
						'S_IsChannelServer'             => 'Run the server as a Nadeo channel server (requires special credentials).',

						// === WARM-UP ===
						'S_WarmUpNb'                    => 'Number of warm-up rounds before the match starts. Set to 0 to disable warm-up.',
						'S_WarmUpDuration'              => 'Duration of each warm-up round in seconds. 0 = unlimited (players must finish).',
						'S_WarmUpTimeout'               => 'Time in seconds after the first player finishes during which other players can still finish the warm-up round.',

						// === TIME ATTACK ===
						'S_TimeLimit'                   => 'Time limit for the map in seconds. 0 = no limit (server admin must skip manually).',
						'S_ForceLapsNb'                 => 'Override the number of laps defined in the map. -1 = use map setting.',

						// === ROUNDS ===
						'S_PointsLimit'                 => 'Number of points required to win the match.',
						'S_PointsRepartition'           => 'Comma-separated list of points awarded per finishing position (e.g. "10,6,4,3,2,1").',
						'S_RoundsPerMap'                => 'Number of rounds played on each map before rotating.',
						'S_MapsPerMatch'                => 'Number of maps played per match. -1 = play until a player reaches S_PointsLimit.',
						'S_UseTieBreak'                 => 'When enabled, the map continues if multiple players are tied on points at the limit.',
						'S_FinishTimeout'               => 'Time in seconds after the first player finishes before the round ends. -1 = based on map author time.',
						'S_RespawnBehaviour'            => 'Controls respawn: 0=default, 1=never (race ends on give-up), 2=always, 3=end round on give-up, 4=five seconds.',
						'S_NbOfWinners'                 => 'Number of winners required before the map ends.',
						'S_RoundsWithoutElimination'    => 'Number of rounds at the start of a Knockout match where no player is eliminated.',

						// === TEAM ===
						'S_MaxPointsPerRound'           => 'Maximum points a team can earn in a single round.',
						'S_PointsGap'                   => 'Minimum points difference between teams to end the match.',
						'S_UsePlayerClublinks'          => 'Use player clublinks to assign players to teams automatically.',
						'S_DisplayTeamScore'            => 'Show team score banner during the race.',
						'S_CumulatePoints'              => 'Accumulate points across rounds instead of resetting each round.',

						// === LAPS ===
						'S_ForceLapsNb'                 => 'Override the number of laps. -1 = use the map author setting.',
						'S_TimeLimit'                   => 'Total time limit in seconds. 0 = no time limit.',

						// === CUP ===
						'S_CupPointsLimit'              => 'Points required to become a finalist.',
						'S_CupRoundsPerMap'             => 'Number of rounds played on each map during the cup.',
						'S_CupNbWinners'                => 'Number of finalists needed before the cup ends.',
						'S_CupWarmUpDuration'           => 'Duration of the warm-up in seconds before the cup starts.',

						// === KNOCKOUT ===
						'S_FinishTimeout'               => 'Seconds after the first finisher before the round ends. -1 = auto based on map.',
						'S_RoundsWithoutElimination'    => 'Rounds at the start where no one is eliminated.',
						'S_SuddenDeathMode'             => 'In sudden death, every player who does not finish is eliminated.',
						'S_NbPlayersPerMap'             => 'Target number of players per map; adjusts elimination rate automatically.',

						// === ROYAL ===
						'S_RoundsPerMap'                => 'Number of rounds played per map.',
						'S_MapsPerMatch'                => 'Number of maps in the match.',
						'S_MatchPointsLimit'            => 'Points needed to win the entire match.',
						'S_RoundPointsLimit'            => 'Points needed to win an individual round.',
						'S_RoundTimeLimitAdded'         => 'Extra time added in seconds to the round timer.',
						'S_GoldTime'                    => 'Gold medal time for the current map (seconds).',
						'S_SilverTime'                  => 'Silver medal time for the current map (seconds).',
						'S_BronzeTime'                  => 'Bronze medal time for the current map (seconds).',
						'S_AllyRespawn'                 => 'Allow allies to respawn fallen players.',

						// === OBSTACLE / TRACKMASTER ===
						'S_Infinite'                    => 'Players can attempt the map as many times as they want without time pressure.',
						'S_MaxPlayerNb'                 => 'Maximum number of simultaneous players on the server.',
						'S_PruneInterval'               => 'Interval in seconds between server-side prune operations for inactive sessions.',
						'S_ScoreTable'                  => 'Show the scoreboard during the race.',

						// === CHAMPION ===
						'S_PointsRepartition'           => 'Points distributed per position (comma-separated).',
						'S_RoundsPerMap'                => 'Rounds played per map.',
						'S_MapsPerMatch'                => 'Maps per match (-1 = until limit is reached).',
						'S_MatchPointsLimit'            => 'Match points limit to win.',

						// === TMWT / COMP ===
						'S_DecoImageUrl_Screen16x9'     => 'URL to a 16:9 image displayed on in-game screens.',
						'S_DecoImageUrl_Screen8x1'      => 'URL to an 8:1 banner image displayed on in-game banners.',
						'S_DecoImageUrl_Screen16x1'     => 'URL to a 16:1 wide banner for in-game display.',
						'S_DecoImageUrl_LobbyBackground'=> 'URL to the lobby background image.',
						'S_EnableReadyManager'          => 'Show the ready-up manager widget to players.',
						'S_AutoStartDelay'              => 'Seconds to wait after all players are ready before auto-starting.',
						'S_AutoStartMinPlayerCount'     => 'Minimum number of ready players required for auto-start.',
						'S_DelayBeforeNextMap'          => 'Seconds between maps in a match.',
						'S_ForceLapsNb'                 => 'Override the map lap count. -1 = use map setting.',
						'S_UseAlternateRules'           => 'Enable alternate ruleset (depends on the script).',
						'S_MapPointsLimit'              => 'Points required to win a single map.',
						'S_TeamPointsLimit'             => 'Points required for a team to win the match.',

						// === LIVE / STREAMING ===
						'S_StreamerMode'                => 'Enable streamer-friendly mode (hides ghost car timings).',
						'S_AllowRespawn'                => 'Allow players to respawn during a round.',
						'S_HideOpponents'               => 'Hide opponent ghosts from the player\'s view.',
					];

					// Merge: extra desc used only when API Desc is empty
					if( isset($out['ParamDescs']) && is_array($out['ParamDescs']) ){
						foreach($out['ParamDescs'] as &$param){
							$name = $param['Name'] ?? '';
							if( empty(trim((string)($param['Desc'] ?? ''))) && isset($extraDescs[$name]) ){
								$param['Desc'] = $extraDescs[$name];
							}
						}
						unset($param);
					}

					// Pass extra descs to JS (for parameters the API doesn't return in ParamDescs)
					$out['_extraDescs'] = $extraDescs;
				}
			}
		}
		// TmForever: no ModeScript
		else{
			$out = false;
		}
	}

	// OUT
	$client->Terminate();
	echo json_encode($out);
?>
