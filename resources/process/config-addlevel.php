<?php
	// SAVE
	if (isset($_POST['savelevel'])) {
		// Variables
		$levelName = Str::replaceSpecialChars( htmlspecialchars(addslashes((string) $_POST['addLevelName'])), false);
		$levelType = $_POST['addLevelType'];
		$levelAccess = [];
		if ($_POST['selectedAccessSortList']) {
			$levelAccess = explode(',', (string) $_POST['selectedAccessSortList']);
		}
		$levelPermission = [];
		if ($_POST['selectedPermissionSortList']) {
			$levelPermission = explode(',', (string) $_POST['selectedPermissionSortList']);
		}
		$levelData = ['name' => $levelName, 'adminlevel' => ['type' => $levelType], 'access' => $levelAccess, 'permission' => $levelPermission];
		
		// Edit
		if($args['id'] !== -1){
			if( ($result = AdminServAdminLevel::saveConfig($levelData, $args['id'])) !== true ){
				AdminServ::error( Utils::t('Unable to modify the admin level.').' ('.$result.')');
			}
			else{
				$action = Utils::t('This admin level has been modified.');
				AdminServ::info($action);
				AdminServLogs::add('action', $action);
				Utils::redirection(false, '?p=config-adminlevel');
			}
		}
		// Add
		else{
			if( ($result = AdminServAdminLevel::saveConfig($levelData)) !== true ){
				AdminServ::error( Utils::t('Unable to add the admin level.').' ('.$result.')');
			}
			else{
				$action = Utils::t('This admin level has been added.');
				AdminServ::info($action);
				AdminServLogs::add('action', $action);
				Utils::redirection(false, '?p='.USER_PAGE);
			}
		}
	}
	
	
	// READ
	$defaultAccess = AdminServAdminLevel::getDefaultAccess();
	$defaultPermission = AdminServAdminLevel::getDefaultPermission();
	$data = ['name' => null, 'types' => AdminServAdminLevel::getDefaultType(), 'adminlevel' => ['type' => null], 'access' => ['default' => [], 'selected' => $defaultAccess], 'permission' => ['default' => [], 'selected' => $defaultPermission]];
	if($args['id'] !== -1){
		define('IS_LEVEL_EDITION', true);
		$data['name'] = AdminServAdminLevel::getName($args['id']);
		if($data['name']){
			$levelData = AdminServAdminLevel::getData($data['name']);
			
			$data['adminlevel'] = ['type' => $levelData['adminlevel']['type']];
			$data['access'] = ['default' => array_diff($defaultAccess, $levelData['access']), 'selected' => $levelData['access']];
			$data['permission'] = ['default' => array_diff($defaultPermission, $levelData['permission']), 'selected' => $levelData['permission']];
		}
	}
?>