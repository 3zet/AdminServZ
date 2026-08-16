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
	$method = $_POST['method'] ?? $_GET['method'] ?? null;

	// DATA
	$out = null;
	if( AdminServ::initialize() && isTM2020() && AdminServAdminLevel::isType('Admin') ){

		// Get the path to the Scripts/ directory (GameDataDirectory/../Scripts/)
		if( !$client->query('GameDataDirectory') ){
			echo json_encode(['error' => '['.$client->getErrorCode().'] '.$client->getErrorMessage()]);
			$client->Terminate();
			exit;
		}
		$gameDataDir = Str::toSlash( $client->getResponse() );
		if( !str_ends_with($gameDataDir, '/') ){ $gameDataDir .= '/'; }
		$scriptsDir = $gameDataDir . 'Scripts/Modes/';

		// Current script
		if( !$client->query('GetScriptName') ){
			echo json_encode(['error' => '['.$client->getErrorCode().'] '.$client->getErrorMessage()]);
			$client->Terminate();
			exit;
		}
		$scriptName = $client->getResponse()['CurrentValue'] ?? '';

		if( $method === 'get' ){
			// Return the list of available scripts + the content of the current one
			$scripts = [];
			if( is_dir($scriptsDir) ){
				$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scriptsDir, FilesystemIterator::SKIP_DOTS));
				foreach($it as $file){
					if( $file->isFile() && preg_match('/\.Script\.txt$/i', $file->getFilename()) ){
						// Path relative to UserData/Scripts/Modes/
						$rel = ltrim(str_replace($scriptsDir, '', Str::toSlash($file->getPathname())), '/');
						$scripts[] = $rel;
					}
				}
			}
			sort($scripts);

			// Content of the current script
			$content = null;
			$filePath = $scriptsDir . $scriptName;
			if( $scriptName && file_exists($filePath) ){
				$content = file_get_contents($filePath);
			}

			$out = [
				'scriptName'  => $scriptName,
				'scriptsDir'  => $scriptsDir,
				'scripts'     => $scripts,
				'content'     => $content,
				'editable'    => ($content !== null && is_writable($filePath)),
			];
		}
		elseif( $method === 'getfile' ){
			// Return the content of a specific file
			$name = $_POST['name'] ?? '';
			// Path traversal protection
			$name = str_replace(['..', '\\'], '', $name);
			$filePath = $scriptsDir . $name;
			if( $name && file_exists($filePath) && is_readable($filePath) ){
				$out = [
					'name'     => $name,
					'content'  => file_get_contents($filePath),
					'editable' => is_writable($filePath),
				];
			}
			else{
				$out = ['error' => 'File not found or not readable: '.$name];
			}
		}
		elseif( $method === 'save' ){
			// Save the script content and optionally set it active + restart
			$name    = $_POST['name']    ?? '';
			$content = $_POST['content'] ?? '';
			$reload  = isset($_POST['reload']) && $_POST['reload'] === '1';

			$name = str_replace(['..', '\\'], '', $name);
			if( !$name ){
				echo json_encode(['error' => 'Missing script name']);
				$client->Terminate();
				exit;
			}

			$filePath = $scriptsDir . $name;
			// Make sure the directory exists
			$dir = dirname($filePath);
			if( !is_dir($dir) ){
				if( !mkdir($dir, 0755, true) ){
					echo json_encode(['error' => 'Cannot create directory: '.$dir]);
					$client->Terminate();
					exit;
				}
			}

			if( file_put_contents($filePath, $content) === false ){
				$out = ['error' => 'Cannot write file: '.$filePath];
			}
			else{
				$out = ['success' => true, 'name' => $name];

				// If hot-reload is checked: SetScriptName + RestartMap
				if( $reload ){
					if( !$client->query('SetScriptName', $name) ){
						$out['reloadError'] = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
					}
					else{
						// TriggerModeScriptEventArray 'Maniaplanet.LoadingMap' doesn't exist in TM2020;
						// Use RestartMap
						if( !$client->query('RestartMap') ){
							$out['reloadError'] = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
						}
						else{
							$out['reloaded'] = true;
						}
					}
					AdminServLogs::add('action', 'Script editor: saved & reloaded '.$name);
				}
				else{
					AdminServLogs::add('action', 'Script editor: saved '.$name);
				}
			}
		}
	}
	else{
		$out = ['error' => 'Unauthorized or not TM2020'];
	}

	$client->Terminate();
	echo json_encode($out);
?>
