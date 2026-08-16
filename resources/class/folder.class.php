<?php
/**
* Class Folder
*
* Folder processing methods
*/
abstract class Folder {
	
	/**
	* List the folders and files in a directory
	*
	* @param  string $path                 -> Path of the directory to list
	* @param  mixed  $hidden_folders       -> Folders to hide: array('folder1', 'folder2'); or 'all' to skip loading folders
	* @param  mixed  $hidden_files         -> Files or extensions to hide: array('Thumbs.db', 'index.php', 'exe'); or 'all' to skip loading files
	* @param  int    $recent_status_period -> Period for showing "recent" status
	* @return array ['folders'], ['files']
	*/
	public static function read($path = '.', $hiddenFolders = [], $hiddenFiles = [], $recentStatusPeriod = 86400){
		$out = [];
		$midnight = time() - (date('H') * 3600) - (date('i') * 60);
		if( !str_ends_with($path, '/')){ $path .= '/'; }
		
		if( !class_exists('File') && !class_exists('Str') ){
			$out = 'Class "File" or "Str" not exists';
		}
		else{
			if( !is_dir($path) ){
				$out = 'Not directory';
			}
			else{
				$dir = scandir($path);
				foreach($dir as $entry){
					$entry = mb_convert_encoding($entry, 'UTF-8', 'ISO-8859-1');
					if($entry != '.' && $entry != '..'){
						$pathToEntry = $path.'/'.$entry;
						
						if($hiddenFolders !== 'all' && is_dir($pathToEntry) ){
							if( !in_array($entry, $hiddenFolders) ){
								$out['folders'][$entry]['size'] = Str::formatSize(self::getSize($pathToEntry));
								$out['folders'][$entry]['nb_file'] = self::countFiles($pathToEntry, $hiddenFiles);
							}
						}
						else if($hiddenFiles !== 'all' && !in_array($entry, $hiddenFiles) ){
							$extension = File::getExtension($entry);
							
							if( !in_array($extension, $hiddenFiles) ){
								$out['files'][$entry] = @array_slice(@stat($pathToEntry), 20, 3);
								$out['files'][$entry]['size'] = Str::formatSize($out['files'][$entry]['size']);
								$out['files'][$entry]['filename'] = $entry;
								$out['files'][$entry]['extension'] = $extension;
								$out['files'][$entry]['recent'] = (isset($out['files'][$entry]['mtime']) && ($out['files'][$entry]['mtime'] > ($midnight - $recentStatusPeriod))) ? 1 : 0;
							}
						}
					}
				}
				
				if( empty($out['folders']) ){
					$out['folders'] = null;
				}
				if( empty($out['files']) ){
					$out['files'] = null;
				}
			}
		}
		return $out;
	}
	
	
	/**
	* Create a folder
	*
	* @param string $dirname -> Path of the folder to create
	* @return true if successful, otherwise error message
	*/
	public static function create($dirname){
		$out = null;
		
		if( !file_exists($dirname) ){
			umask(0000);
			if( mkdir($dirname, 0777, true) ){
				$out = true;
			}
			else{
				$out = 'Create folder failed';
			}
		}
		else{
			$out = 'Folder already exists';
		}
		
		return $out;
	}
	
	
	/**
	* Rename a folder
	*
	* @param string $oldname -> Path of the old folder
	* @param string $newname -> Path of the new folder
	* @return true if successful, otherwise error message
	*/
	public static function rename(string $oldname, $newname){
		$out = null;
		
		if( file_exists($oldname) ){
			if( is_dir($oldname) ){
				if( rename($oldname, $newname) ){
					$out = true;
				}
				else{
					$out = 'Rename '.$oldname.' failed';
				}
			}
			else{
				$out = 'Not directory';
			}
		}
		else{
			$out = 'Folder no exists';
		}
		
		return $out;
	}
	
	
	/**
	* Delete a folder with its content (folders + files)
	*
	* @param string $dirname -> Path of the folder to delete
	* @return true if successful, otherwise error message
	*/
	public static function delete($dirname){
		$out = null;
		if( !str_ends_with($dirname, '/')){ $dirname .= '/'; }
		
		if( class_exists('File') ){
			if( file_exists($dirname) ){
				$dir = scandir($dirname);
				foreach($dir as $file){
					if($file != '.' && $file != '..'){
						$pathToFile = $dirname.$file;
						
						if( is_dir($pathToFile) ){
							self::delete($pathToFile);
						}
						else{
							File::delete($pathToFile);
						}
					}
				}
				
				if( @rmdir($dirname) !== true ){
					$out = 'Unable to delete '.$dirname;
				}
				else{
					$out = true;
				}
			}
			else{
				$out = 'Dir not exists';
			}
		}
		else{
			$out = 'Class "File" not exists';
		}
		
		return $out;
	}
	
	
	/**
	* Count the number of files in a folder
	*
	* @param string $path        -> The folder path
	* @param array  $hiddenFiles -> Files or extensions to ignore
	* @return int
	*/
	public static function countFiles($path, $hiddenFiles = [] ){
		$out = 0;
		if( !str_ends_with($path, '/')){ $path .= '/'; }
		
		if( class_exists('File') ){
			if( file_exists($path) ){
				$dir = scandir($path);
				foreach($dir as $file){
					$pathToFile = $path.$file;
					
					if( is_dir($pathToFile) && $file != '.' && $file != '..' ){
						if( is_numeric( $result = self::countFiles($pathToFile, $hiddenFiles) ) ){
							$out += $result;
						}
						else{
							$out = $result;
							break;
						}
					}
					else{
						if($file != '.' && $file != '..'){
							if( !in_array($file, $hiddenFiles) ){
								if( !in_array(File::getExtension($file), $hiddenFiles) ){
									$out++;
								}
							}
						}
					}
				}
			}
			else{
				$out = 'Dir not exists';
			}
		}
		else{
			$out = 'Class "File" not exists';
		}
		
		return $out;
	}
	
	
	/**
	* Size of a folder
	*
	* @param string $path -> The folder path
	* @return int
	*/
	public static function getSize($path){
		$out = 0;
		if( !str_ends_with($path, '/')){ $path .= '/'; }
		
		if( file_exists($path) ){
			$dir = scandir($path);
			foreach($dir as $file){
				if( $file != '..' && $file != '.' ){
					$pathToFile = $path.'/'.$file;
					if( is_dir($pathToFile) ){
						$out += self::getSize($pathToFile);
					}
					else{
						$out += @filesize($pathToFile);
					}
				}
			}
		}
		else{
			$out = 'Dir not exists';
		}
		
		return $out;
	}
	
	
	/**
	* Return the complete tree of the path with a folder offset
	*
	* @param string $path          -> The folder path
	* @param array  $hiddenFiles   -> Files or extensions to ignore
	* @param int    $hidePathLevel -> Number of path levels to hide from display
	* @return array
	*/
	public static function getArborescence(string $path = '.', $hiddenFolders = [], $hidePathLevel = 0){
		$out = [];
		$struct = self::read($path, $hiddenFolders);
		
		// Offset based on tree depth
		$countSlash = substr_count($path, '/') - $hidePathLevel;
		$pathLevel = null;
		for($i = 0; $i < $countSlash; $i++){
			$pathLevel .= '&#9474;&nbsp;';
		}
		$pathLevel .= '&#9500;&nbsp;';
		
		if( isset($struct['folders']) && count($struct['folders']) > 0 ){
			foreach($struct['folders'] as $dir => $values){
				$out[] = ['path' => $path.$dir.'/', 'name' => $dir, 'level' => $pathLevel];
				$out = array_merge($out, self::getArborescence($path.$dir.'/', $hiddenFolders, $hidePathLevel) );
			}
		}
		
		return $out;
	}
	
	
	/**
	* Check permissions against chmod
	*/
	public static function checkRights($path, $minChmod){
		$out = [];
		$chars = str_split((string) $minChmod);
		
		$i = 0;
		foreach($chars as $char){
			if($i == 0){ $group = 'owner'; }
			else if($i == 1){ $group = 'group'; }
			else{ $group = 'other'; }
			$out[$group]['request'] = $char;
			
			switch($char){
				case 7:
					$out[$group]['result']['r'] = is_readable($path);
					$out[$group]['result']['w'] = is_writable($path);
					$out[$group]['result']['x'] = is_executable($path);
					break;
				case 6:
					$out[$group]['result']['r'] = is_readable($path);
					$out[$group]['result']['w'] = is_writable($path);
					break;
				case 5:
					$out[$group]['result']['r'] = is_readable($path);
					$out[$group]['result']['x'] = is_executable($path);
					break;
				case 4:
					$out[$group]['result']['r'] = is_readable($path);
					break;
				case 3:
					$out[$group]['result']['w'] = is_writable($path);
					$out[$group]['result']['x'] = is_executable($path);
					break;
				case 2:
					$out[$group]['result']['w'] = is_writable($path);
					break;
				case 1:
					$out[$group]['result']['x'] = is_executable($path);
					break;
				default:
					$out[$group]['result']['r'] = false;
					$out[$group]['result']['w'] = false;
					$out[$group]['result']['x'] = false;
			}
			$i++;
		}
		
		return $out;
	}
	
	
	/**
	* Convert a chmod int (777) into a string (rwxrwxrwx)
	*/
	public static function chmodToStr($chmod){
		$out = null;
		$chars = str_split((string) $chmod);
		
		foreach($chars as $char){
			switch($char){
				case 7:
					$out .= 'rwx';
					break;
				case 6:
					$out .= 'rw-';
					break;
				case 5:
					$out .= 'r-x';
					break;
				case 4:
					$out .= 'r--';
					break;
				case 3:
					$out .= '-wx';
					break;
				case 2:
					$out .= '-w-';
					break;
				case 1:
					$out .= '--x';
					break;
			}
		}
		
		return $out;
	}
}
?>