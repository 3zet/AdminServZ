<?php
	// GAME VERSION
	// ChooseNextChallengeList — TmForever
	// ChooseNextMapList       — ManiaPlanet + TM2020
	if(!isMpApi()){
		$queries = ['chooseNextMap' => 'ChooseNextChallengeList'];
	}
	else{
		$queries = ['chooseNextMap' => 'ChooseNextMapList'];
	}

	// ACTIONS
	if( isset($_POST['save']) && isset($_POST['list']) && $_POST['list'] != null ){
		$list = explode(',', (string) $_POST['list']);

		if( !$client->query($queries['chooseNextMap'], $list) ){
			AdminServ::error();
		}
		else{
			AdminServLogs::add('action', 'Order map list');
			Utils::redirection(false, '?p='.USER_PAGE);
		}
	}


	// MAPLIST
	$data['maps'] = AdminServ::getMapList();
	unset($data['maps']['lst'][$data['maps']['cid']]);
?>
