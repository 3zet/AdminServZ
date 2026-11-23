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
	AdminServUI::lang();

	// ISSET
	if( isset($_GET['path']) ){ $path = $_GET['path']; }else{ $path = null; }
	if( isset($_GET['gtlm']) ){ $gtlm = $_GET['gtlm']; }else{ $gtlm = null; }
	if( isset($_GET['mset']) ){ $mset = $_GET['mset']; }else{ $mset = null; }
	if( isset($_GET['type']) ){ $type = $_GET['type']; }else{ $type = null; }

	// DATA
	if( AdminServ::initialize() ){
		// Path
		// $localPath:  folder AdminServ writes to itself (null = let the game server write via WriteFile)
		// $serverPath: the same folder as the game server sees it (absolute, or relative to "Maps" for WriteFile)
		$mapDirectoryPath = AdminServ::getMapsDirectoryPath();
		if($path === null){
			$path = $mapDirectoryPath;
		}
		if( !str_ends_with((string) $path, '/')){
			$path = $path.'/';
		}
		$localPath = null;
		$serverPath = null;
		if( is_dir($path) && is_writable($path) ){
			$localPath = $path;
			$serverPath = AdminServ::toServerPath($path);
		}
		else if( is_dir($path) && ($relativePath = AdminServ::toMapsRelativePath($path)) !== null ){
			$serverPath = $relativePath;
		}
		else if( defined('AdminServConfig::UPLOAD_ONLINE_FOLDER') && AdminServConfig::UPLOAD_ONLINE_FOLDER != null ){
			$serverPath = AdminServConfig::UPLOAD_ONLINE_FOLDER;
		}
		else{
			$serverPath = '';
		}

		// Callback function for replacing special characters
		function replaceFilename($str){
			return Str::replaceChars($str);
		}

		// Game -- InsertMap/AddMap valid for MP and TM2020
		// TmForever uses InsertChallenge/AddChallenge
		if(!isMpApi()){
			$queries = ['insert' => 'InsertChallenge', 'add' => 'AddChallenge', 'type' => $type];
		}
		else{
			// MP and TM2020: same methods
			$queries = ['insert' => 'InsertMap', 'add' => 'AddMap', 'type' => $type];
		}

		// Max size
		if(AdminServConfig::SIZE_LIMIT == 'auto'){
			$sizeLimit1 = (intval(ini_get('post_max_size')) * 1024 * 1024);
			$sizeLimit2 = (intval(ini_get('upload_max_filesize')) * 1024 * 1024);
			$sizeLimit = min($sizeLimit1, $sizeLimit2);
		}
		else{
			$sizeLimit = (intval(AdminServConfig::SIZE_LIMIT) * 1024 * 1024);
		}

		// Save the file
		$result = FileUploader::saveUploadedFileToManiaPlanetServer($client, $localPath, $serverPath, $queries, AdminServConfig::$ALLOWED_EXTENSIONS, $sizeLimit, 'replaceFilename');

		// Save the MatchSettings -- SaveMatchSettings exists in MP and TM2020
		if($mset && SERVER_MATCHSET && $type != 'local' && isset($result['success']) && $result['success'] == true){
			if( !$client->query('SaveMatchSettings', AdminServ::toServerPath($mapDirectoryPath.SERVER_MATCHSET)) ){
				$result['error'] = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
			}
		}
	}
	$client->Terminate();
	echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
?>
