<?php
	// GAMEDATA
	if( AdminServAdminLevel::isType('Admin') ){
		if( !$client->query('GameDataDirectory') ){
			AdminServ::error();
		}
		else{
			$data['gameDataDirectory'] = $client->getResponse();
			$data['playlistDirectory'] = Folder::read($data['gameDataDirectory'].'Config', [], [], intval(AdminServConfig::RECENT_STATUS_PERIOD * 3600) );
		}
	}
	
	// ACTIONS
	// Clear the list
	if( isset($_GET['clean']) ){
		$clean = strtolower((string) $_GET['clean']);
		if($clean == 'banlist'){
			if( !$client->query('CleanBanList') ){
				AdminServ::error();
			}
			else{
				AdminServLogs::add('action', 'Clean banlist');
			}
		}
		else if($clean == 'ignorelist'){
			if( !$client->query('CleanIgnoreList') ){
				AdminServ::error();
			}
			else{
				AdminServLogs::add('action', 'Clean ignorelist');
			}
		}
		else if($clean == 'guestlist'){
			if( !$client->query('CleanGuestList') ){
				AdminServ::error();
			}
			else{
				AdminServLogs::add('action', 'Clean guestlist');
			}
		}
		else if($clean == 'blacklist'){
			if( !$client->query('CleanBlackList') ){
				AdminServ::error();
			}
			else{
				AdminServLogs::add('action', 'Clean blacklist');
			}
		}
	}
	// Blacklist
	else if( isset($_POST['blackListPlayer']) ){
		// Build the list of players to blacklist
		$blackListPlayer = [];
		if( isset($_POST['banlist']) && count($_POST['banlist']) > 0 ){
			$blackListPlayer = array_merge($blackListPlayer, $_POST['banlist']);
		}
		if( isset($_POST['guestlist']) && count($_POST['guestlist']) > 0 ){
			$blackListPlayer = array_merge($blackListPlayer, $_POST['guestlist']);
		}
		if( isset($_POST['ignorelist']) && count($_POST['ignorelist']) > 0 ){
			$blackListPlayer = array_merge($blackListPlayer, $_POST['ignorelist']);
		}
		$blackListPlayer = array_unique($blackListPlayer);
		
		// Blacklist from all lists
		foreach($blackListPlayer as $player){
				if( !$client->query('BlackList', $player) ){
					AdminServ::error();
					break;
				}
				else{
					AdminServLogs::add('action', 'Blacklist player: '.$player);
				}
			}
	}
	// Remove a player from one or more lists
	else if( isset($_POST['removeList']) ){
		if( isset($_POST['banlist']) && count($_POST['banlist']) > 0 ){
			foreach($_POST['banlist'] as $player){
				if( !$client->query('UnBan', $player) ){
					AdminServ::error();
					break;
				}
				else{
					AdminServLogs::add('action', 'Unban player: '.$player);
				}
			}
		}
		else if( isset($_POST['blacklist']) && count($_POST['blacklist']) > 0 ){
			foreach($_POST['blacklist'] as $player){
				if( !$client->query('UnBlackList', $player) ){
					AdminServ::error();
					break;
				}
				else{
					AdminServLogs::add('action', 'Unblacklist player: '.$player);
				}
			}
		}
		else if( isset($_POST['guestlist']) && count($_POST['guestlist']) > 0 ){
			foreach($_POST['guestlist'] as $player){
				if( !$client->query('RemoveGuest', $player) ){
					AdminServ::error();
					break;
				}
				else{
					AdminServLogs::add('action', 'Remove guest player: '.$player);
				}
			}
		}
		else if( isset($_POST['ignorelist']) && count($_POST['ignorelist']) > 0 ){
			foreach($_POST['ignorelist'] as $player){
				if( !$client->query('UnIgnore', $player) ){
					AdminServ::error();
					break;
				}
				else{
					AdminServLogs::add('action', 'Unignore player: '.$player);
				}
			}
		}
	}
	
	
	// ADD
	else if( isset($_POST['addPlayer']) ){
		// Variables
		$addPlayerList = $_POST['addPlayerList'];
		$addPlayerLogin = strtolower( trim((string) $_POST['addPlayerLogin']) );
		$addPlayerTypeList = $_POST['addPlayerTypeList'];
		
		// PlayerLogin
		if($addPlayerList != 'none' && $addPlayerList != 'more'){
			$playerlogin = $addPlayerList;
		}else{
			$playerlogin = $addPlayerLogin;
		}
		
		// Request
		if($playerlogin != Utils::t('Player login') ){
			// Invite
			if($addPlayerTypeList == 'guestlist'){
				if( !$client->query('AddGuest', $playerlogin) ){
					AdminServ::error();
				}
				else{
					AdminServLogs::add('action', 'Add guest player: '.$playerlogin);
				}
			}
			// Blacklist
			else if($addPlayerTypeList == 'blacklist'){
				if( !$client->query('BlackList', $playerlogin) ){
					AdminServ::error();
				}
				else{
					AdminServLogs::add('action', 'Add blacklist player: '.$playerlogin);
				}
			}
		}
	}
	
	
	// LOCAL PLAYLISTS
	else if( isset($_POST['savePlaylist']) && isset($_POST['playlist']) && count($_POST['playlist'] > 0) ){
		$i = 0;
		foreach($_POST['playlist'] as $playlist){
			$playlistEx = explode('|', (string) $playlist);
			$type = $playlistEx[0];
			$filename = $playlistEx[1];
			
			// Guestlist
			if($type == 'guestlist'){
				if( !$client->query('SaveGuestList', $filename) ){
					AdminServ::error();
					break;
				}
				else{
					AdminServLogs::add('action', 'Save guestlist');
				}
			}
			// BlackList
			elseif($type == 'blacklist'){
				if( !$client->query('SaveBlackList', $filename) ){
					AdminServ::error();
					break;
				}
				else{
					AdminServLogs::add('action', 'Save blacklist');
				}
			}
			$i++;
		}
		
		Utils::redirection(false, '?p='.USER_PAGE);
	}
	else if( isset($_POST['loadPlaylist']) && isset($_POST['playlist']) && count($_POST['playlist'] > 0) ){
		$i = 0;
		foreach($_POST['playlist'] as $playlist){
			$playlistEx = explode('|', (string) $playlist);
			$type = $playlistEx[0];
			$filename = $playlistEx[1];
			
			// Guestlist
			if($type == 'guestlist'){
				if( !$client->query('LoadGuestList', $filename) ){
					AdminServ::error();
					break;
				}
				else{
					AdminServLogs::add('action', 'Load guestlist');
				}
			}
			// BlackList
			elseif($type == 'blacklist'){
				if( !$client->query('LoadBlackList', $filename) ){
					AdminServ::error();
					break;
				}
				else{
					AdminServLogs::add('action', 'Load blacklist');
				}
			}
			$i++;
		}
		
		Utils::redirection(false, '?p='.USER_PAGE);
	}
	else if( isset($_POST['deletePlaylist']) && isset($_POST['playlist']) && count($_POST['playlist'] > 0) ){
		foreach($_POST['playlist'] as $playlist){
			$playlistEx = explode('|', (string) $playlist);
			$filename = $playlistEx[1];
			
			if( !File::delete($data['gameDataDirectory'].'Config/'.$filename) ){
				AdminServ::error(Utils::t('Unable to delete the playlist').' : '.$filename);
				break;
			}
			else{
				AdminServLogs::add('action', 'Delete playlist: '.$filename);
			}
		}
		
		Utils::redirection(false, '?p='.USER_PAGE);
	}
	else if( isset($_POST['createPlaylistValid']) && isset($_POST['createPlaylistName']) && $_POST['createPlaylistName'] != null ){
		$filename = Str::replaceChars($_POST['createPlaylistName']);
		
		// Guestlist
		if( $filename != Str::replaceChars(Utils::t('Playlist name')) ){
			if($_POST['createPlaylistType'] == 'guestlist'){
				if( !$client->query('SaveGuestList', $filename) ){
					AdminServ::error();
				}
				else{
					AdminServLogs::add('action', 'Create playlist (guestlist): '.$filename);
				}
			}
			// Blacklist
			elseif($_POST['createPlaylistType'] == 'blacklist'){
				if( !$client->query('SaveBlackList', $filename) ){
					AdminServ::error();
				}
				else{
					AdminServLogs::add('action', 'Create playlist (blacklist): '.$filename);
				}
			}
		}
		
		Utils::redirection(false, '?p='.USER_PAGE);
	}
	
	// READ
	$listLimit = listLimit(AdminServConfig::LIMIT_PLAYERS_LIST);
	$client->addCall('GetBanList', [$listLimit, 0] );
	$client->addCall('GetBlackList', [$listLimit, 0] );
	$client->addCall('GetGuestList', [$listLimit, 0] );
	if( !isTmClassic() ){
		$client->addCall('GetIgnoreList', [$listLimit, 0] );
	}
	if( !$client->multiquery() ){
		AdminServ::error();
	}
	else{
		$queriesData = $client->getMultiqueryResponse();
		$data['banlist']['list'] = $queriesData['GetBanList'];
		$data['blacklist']['list'] = $queriesData['GetBlackList'];
		$data['guestlist']['list'] = $queriesData['GetGuestList'];
		$data['ignorelist']['list'] = $queriesData['GetIgnoreList'] ?? [];
		$data['banlist']['count'] = count($data['banlist']['list']);
		$data['blacklist']['count'] = count($data['blacklist']['list']);
		$data['guestlist']['count'] = count($data['guestlist']['list']);
		$data['ignorelist']['count'] = count($data['ignorelist']['list']);
	}
	
	// List of players present on the server
	$data['players']['listOptions'] = AdminServUI::getPlayerList();
	$data['players']['count'] = AdminServ::getNbPlayers();
?>