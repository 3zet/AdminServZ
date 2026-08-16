<?php
	// RESULTS -- live standings (GetCurrentRanking)
	$data['results'] = AdminServ::getResults();

	// Current server info (needed for the game mode title)
	$data['serverInfo'] = AdminServ::getCurrentServerInfo();
?>
