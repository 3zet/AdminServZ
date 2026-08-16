<?php
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
	$activate = isset($_GET['activate']) && $_GET['activate'] === '1';
	$reload   = isset($_GET['reload'])   && $_GET['reload']   === '1';
	$subdir   = isset($_GET['subdir'])   ? trim(str_replace(['..', '\\'], '', (string)$_GET['subdir']), '/') : '';

	$out = ['success' => false];

	if( !AdminServ::initialize() || !isTM2020() || !AdminServAdminLevel::isType('Admin') ){
		echo json_encode(['error' => 'Unauthorized or not TM2020']);
		exit;
	}

	// Get GameDataDirectory
	if( !$client->query('GameDataDirectory') ){
		echo json_encode(['error' => '['.$client->getErrorCode().'] '.$client->getErrorMessage()]);
		$client->Terminate();
		exit;
	}
	$gameDataDir = Str::toSlash( $client->getResponse() );
	if( !str_ends_with($gameDataDir, '/') ){ $gameDataDir .= '/'; }
	$scriptsDir = $gameDataDir . 'Scripts/Modes/';
	if( $subdir ){ $scriptsDir .= $subdir . '/'; }

	// Make sure the directory exists
	if( !is_dir($scriptsDir) ){
		if( !mkdir($scriptsDir, 0755, true) ){
			echo json_encode(['error' => 'Cannot create scripts directory: '.$scriptsDir]);
			$client->Terminate();
			exit;
		}
	}

	// Handle the upload (qq.FileUploader or standard $_FILES)
	$filename = null;
	$tmpPath  = null;

	if( isset($_FILES['qqfile']) ){
		// Standard file input
		if( $_FILES['qqfile']['error'] !== UPLOAD_ERR_OK ){
			echo json_encode(['error' => 'Upload error: '.$_FILES['qqfile']['error']]);
			$client->Terminate();
			exit;
		}
		$filename = $_FILES['qqfile']['name'];
		$tmpPath  = $_FILES['qqfile']['tmp_name'];
	}
	elseif( isset($_GET['qqfile']) ){
		// Chunked/raw POST (qq.FileUploader)
		$filename = basename((string)$_GET['qqfile']);
		$tmpPath  = tempnam(sys_get_temp_dir(), 'as_script_');
		$input    = fopen('php://input', 'r');
		$dest     = fopen($tmpPath, 'w');
		if( !$input || !$dest ){
			echo json_encode(['error' => 'Cannot read upload stream']);
			$client->Terminate();
			exit;
		}
		stream_copy_to_stream($input, $dest);
		fclose($input);
		fclose($dest);
	}
	else{
		echo json_encode(['error' => 'No file received']);
		$client->Terminate();
		exit;
	}

	// Extension validation
	if( !preg_match('/\.Script\.txt$/i', $filename) ){
		echo json_encode(['error' => 'Invalid file type. Only .Script.txt files are allowed.']);
		$client->Terminate();
		exit;
	}

	// Safe file name
	$safeFilename = Str::replaceChars( basename($filename) );
	$destPath     = $scriptsDir . $safeFilename;

	if( !move_uploaded_file($tmpPath, $destPath) ){
		// Fallback for raw POST temp file
		if( !rename($tmpPath, $destPath) && !copy($tmpPath, $destPath) ){
			echo json_encode(['error' => 'Cannot save file to: '.$destPath]);
			$client->Terminate();
			exit;
		}
		if( file_exists($tmpPath) ){ @unlink($tmpPath); }
	}

	$relName = ($subdir ? $subdir.'/' : '') . $safeFilename;
	$out = ['success' => true, 'name' => $relName, 'path' => $destPath];

	// Optionally: set as the active script
	if( $activate ){
		if( !$client->query('SetScriptName', $relName) ){
			$out['activateError'] = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
		}
		else{
			$out['activated'] = true;
			if( $reload ){
				if( !$client->query('RestartMap') ){
					$out['reloadError'] = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
				}
				else{
					$out['reloaded'] = true;
				}
			}
		}
	}

	AdminServLogs::add('action', 'Script upload: '.$relName.($activate ? ' (activated)' : '').($reload ? ' (reloaded)' : ''));

	$client->Terminate();
	echo htmlspecialchars(json_encode($out), ENT_NOQUOTES);
?>
