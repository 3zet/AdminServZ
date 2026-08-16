<?php
	// ACTIONS
	if( isset($_POST['save']) && isset($_POST['list']) && $_POST['list'] != null ){
		$adminLevelList = AdminLevelConfig::$ADMINLEVELS;
		$list = explode(',', (string) $_POST['list']);
		
		$newAdminLevelList = [];
		foreach($list as $listLevelName){
			$newAdminLevelList[$listLevelName] = ['adminlevel' => $adminLevelList[$listLevelName]['adminlevel'], 'access' => $adminLevelList[$listLevelName]['access'], 'permission' => $adminLevelList[$listLevelName]['permission']];
		}
		
		AdminServAdminLevel::saveConfig([], -1, $newAdminLevelList);
		AdminServLogs::add('action', 'Order admin level list');
		Utils::redirection(false, '?p='.USER_PAGE);
	}
	
	
	// LEVELLIST
	$data['levels'] = [];
	if (AdminServAdminLevel::hasLevel()) {
		$data['levels'] = AdminLevelConfig::$ADMINLEVELS;
	}
?>