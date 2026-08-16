<?php
/**
* Class File
*
* File processing methods
*/
abstract class File {
	
	/**
	* Get a file's extension
	*
	* @param  string $filename -> The file's path or name
	* @return string "php"
	*/
	public static function getExtension($filename){
		$out = null;
		
		$pathinfo = pathinfo($filename);
		if( isset($pathinfo['extension']) ){
			$out = strtolower($pathinfo['extension']);
		}
		
		return $out;
	}
	
	
	/**
	* Get a double extension
	*
	* @param string $filename -> The file's path or name
	* @return string "class.php"
	*/
	public static function getDoubleExtension($filename){
		$out = null;
		$filenameEx = explode('.', $filename);
		$countFilenameEx = count($filenameEx);
		
		if( $countFilenameEx > 2 ){
			$out = $filenameEx[$countFilenameEx - 2];
			$out .= '.'.$filenameEx[$countFilenameEx - 1];
		}
		
		return strtolower((string) $out);
	}
	
	
	/**
	* Create or append data to a file
	*
	* @param  string $filename -> The file's path or name
	* @param  string $data     -> Data to write
	* @return true if successful, otherwise error string
	*/
	public static function save($filename, $data = null, $appendData = true){
		$out = null;
		
		if( file_exists($filename) ){
			$append = ($appendData) ? FILE_APPEND : 0;
			
			if( file_put_contents($filename, $data, $append) ){
				$out = true;
			}
			else{
				$out = 'No such file or file is not writable';
			}
		}
		else{
			if( $handle = fopen($filename, 'w') ){
				$out = true;
				fclose($handle);
			}
			else{
				$out = 'No such file or file is not writable';
			}
		}
		
		return $out;
	}
	
	
	/**
	* Save data to a file at the pointer
	*
	* @param  string $filename -> The file's path or name
	* @param  string $data     -> Data to write
	* @param  string $seek     -> The pointer for where to write
	*/
	public static function saveAtSeek($filename, $data, $seek = 0){
		$out = null;
		
		if( file_exists($filename) && is_writable($filename) ){
			$handle = fopen($filename, 'r+');
			fseek($handle, $seek, SEEK_CUR);
			if( fwrite($handle, $data) ){
				$out = true;
			}
			fclose($handle);
		}
		else{
			$out = 'No such file or file is not writable';
		}
		
		return $out;
	}
	
	
	/**
	* Get the pointer from a string within a file
	*
	* @param string $filename -> The file's path
	* @param string $string   -> The string to search for
	* @param bool   $lastChar -> Return the pointer from the last character of the searched string
	* @return int or text error
	*/
	public static function getSeekFromString($filename, $string, $lastChar = false){
		$out = null;
		
		if( file_exists($filename) && is_writable($filename) ){
			$handle = fopen($filename, 'r');
			$content = fread($handle, filesize($filename) );
			$out = strpos($content, $string);
			if($lastChar){
				$out += strlen($string);
			}
			fclose($handle);
		}
		else{
			$out = 'No such file or file is not writable';
		}
		
		return $out;
	}
	
	
	/**
	* Rename a file
	*
	* @param string $filename    -> Path of the file to rename
	* @param string $newfilename -> Path of the renamed file
	*/
	public static function rename($filename, $newfilename){
		$out = null;
		
		if( file_exists($filename) ){
			if( @rename($filename, $newfilename) ){
				$out = true;
			}
			else{
				$out = 'Unable to rename file';
			}
		}
		else{
			$out = 'No such file';
		}
		
		return $out;
	}
	
	
	/**
	* Delete a file
	*
	* @param string $filename -> Path of the file to delete
	*/
	public static function delete($filename){
		$out = null;
		
		if( file_exists($filename) ){
			if( @unlink($filename) ){
				$out = true;
			}
			else{
				$out = 'Unable to delete file';
			}
		}
		else{
			$out = 'No such file';
		}
		
		return $out;
	}
	
	
	/**
	* Send headers to download a file
	*
	* @param string $pathToFile -> Path of the file to download
	* @param int    $fileSize   -> File size, if null = automatic
	*/
	public static function sendDownloadHeaders($pathToFile, $fileSize = null): void{
		$path_parts = pathinfo($pathToFile);
		$filename = htmlspecialchars( trim($path_parts['basename']), ENT_QUOTES, 'UTF-8');
		
		header('Content-Disposition: attachment; filename="'.$filename);
		header('Content-Type: application/force-download');
		header('Content-Transfer-Encoding: binary');
		if($fileSize != null){
			header('Content-Length: '.$fileSize);
		}
		else{
			header('Content-Length: '.filesize($pathToFile));
		}
		header('Pragma: no-cache');
		if( preg_match('/MSIE/i', (string) $_SERVER['HTTP_USER_AGENT']) ){
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		}
		else{
			header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		}
		header('Expires: 0');
	}
	
	
	/**
	* Allows downloading a file
	*
	* @param string $pathToFile -> Path of the file to download
	* @param int    $fileSize   -> File size, if null = automatic
	*/
	public static function download($pathToFile, $fileSize = null): void{
		self::sendDownloadHeaders($pathToFile, $fileSize);
		flush();
		readfile($pathToFile);
	}
}
?>