<?php
/**
* CLASS UPLOAD
*
* Handles sending a file locally or via FTP
*
* @example:
*         $path = '/';
*         $replaceOldFile = false;
*         $allowedExtensions = array('jpg', 'png', 'gif');
*         $sizeLimit = 4 * 1024 * 1024;
*     LOCAL:
*         echo FileUploader::saveUploadedFile($path, $replaceOldFile, $allowedExtensions, $sizeLimit);
*     FTP:
*         echo FileUploader::saveUploadedFileToFTP($ftp_stream, $path, $replaceOldFile, $allowedExtensions, $sizeLimit);
*/


/**
* Handles a file sent via XHR (XMLHttpRequest)
*/
class UploadedFileXhr {
	
	/**
	* Save the file locally
	*
	* @param string $path -> Full path of the file including the filename
	* @return bool true if successful
	*/
	public function save($path): ?bool{
		$out = null;
		
		// Open PHP's "input" stream and create a temp file
		$input = fopen("php://input", "r");
		$temp = tmpfile();
		$realSize = stream_copy_to_stream($input, $temp);
		fclose($input);
		if( $realSize != $this->getSize() ){
			$out = false;
		}
		
		// Create the final file
		$target = fopen($path, "w");
		fseek($temp, 0, SEEK_SET);
		if( stream_copy_to_stream($temp, $target) > 0 ){
			$out = true;
		}
		fclose($target);
		
		return $out;
	}
	
	
	/**
	* Save the file to an FTP server
	*
	* @param resource $ftp_stream -> The FTP connection resource
	* @param string   $path       -> Full path of the destination folder
	* @param string   $filename   -> The file name
	* @return true if successful, otherwise false
	*/
	public function saveFTP($ftp_stream, string $path, string $filename): bool{
		$out = null;
		
		// Open PHP's "input" stream and create a temp file
		$input = fopen("php://input", "r");
		$temp = tmpfile();
		$realSize = stream_copy_to_stream($input, $temp);
		fclose($input);
		if( $realSize != $this->getSize() ){
			$out = false;
		}
		
		// Save the file to the FTP
		fseek($temp, 0, SEEK_SET);
		
		return ftp_fput($ftp_stream, $path.$filename, $temp, FTP_BINARY);
	}
	
	
	/**
	* Write the map to the "Maps" folder via the server
	*
	* @param resource $client   -> The GbxRemote client resource
	* @param string   $filename -> Path to the file relative to the "Maps" folder
	*/
	public function writeMap($client, $filename){
		$out = true;
		
		// Open PHP's "input" stream and create a temp file
		$input = fopen("php://input", "r");
		$temp = tmpfile();
		$realSize = stream_copy_to_stream($input, $temp);
		fclose($input);
		if( $realSize != $this->getSize() ){
			$out = false;
		}
		if($out != false){
			fseek($temp, 0, SEEK_SET);
			$file = stream_get_contents($temp);
			$str64 = new IXR_Base64($file);
			
			if( !$client->query('WriteFile', $filename, $str64) ){
				$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
			}
		}
		
		return $out;
	}
	
	
	/**
	* Add or insert a map into the server's list
	*
	* @param resource $client   -> The GbxRemote client resource
	* @param string   $filename -> Path to the file
	* @param array    $queries  -> Queries to run and the type of list addition: array('insert' => 'InsertMap', 'add' => 'AddMap', 'type' => 'add')
	* @return true if successful, otherwise error text
	*/
	public function saveMap($client, $filename, array $queries){
		$out = true;
		
		if($queries['type'] == 'insert'){
			if( !$client->query($queries['insert'], $filename) ){
				$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
			}
		}
		else{
			if( !$client->query($queries['add'], $filename) ){
				$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
			}
		}
		
		return $out;
	}
	
	
	/**
	* Get the file name
	*/
	public function getName(){
		return $_GET['qqfile'];
	}
	
	
	/**
	* Get the file size
	*/
	public function getSize(): int{
		if( isset($_SERVER['CONTENT_LENGTH']) ){
			return (int)$_SERVER['CONTENT_LENGTH'];            
		}
		else{
			throw new Exception('Getting content length is not supported.');
		}
	}
}


/**
* Handles a file sent via HTML Form (array $_FILES)
*/
class UploadedFileForm {
	
	/**
	* Save the file locally
	*
	* @param string $path -> Full path of the file including the filename
	* @return bool true if successful, otherwise false
	*/
	public function save($path): bool{
		if( !move_uploaded_file($_FILES['qqfile']['tmp_name'], $path) ){
			return false;
		}
		return true;
	}
	
	
	/**
	* Save the file to an FTP server
	*
	* @param resource $ftp_stream -> The FTP connection resource
	* @param string   $path       -> Full path of the destination folder
	* @param string   $filename   -> The file name
	* @return true if successful, otherwise false
	*/
	public function saveFTP($ftp_stream, string $path, string $filename): bool{
		return ftp_put($ftp_stream, $path.$filename, $_FILES['qqfile']['tmp_name'], FTP_BINARY);
	}
	
	
	/**
	* Write the map to the "Maps" folder via the server
	*
	* @param resource $client   -> The GbxRemote client resource
	* @param string   $filename -> Path to the file
	*/
	public function writeMap($client, $filename){
		$out = true;
		$str = file_get_contents($_FILES['qqfile']['tmp_name']);
		$str64 = new IXR_Base64($str);
		
		if( !$client->query('WriteFile', $filename, $str64) ){
			$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
		}
		
		return $out;
	}
	
	
	/**
	* Add or insert a map into the server's list
	*
	* @param resource $client   -> The GbxRemote client resource
	* @param string   $filename -> Path to the file
	* @param array    $queries           -> Queries to run and the type of list addition: array('insert' => 'InsertMap', 'add' => 'AddMap', 'type' => 'add')
	* @return true if successful, otherwise error text
	*/
	public function saveMap($client, $filename, array $queries){
		$out = true;
		
		if($queries['type'] == 'insert'){
			if( !$client->query($queries['insert'], $filename) ){
				$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
			}
		}
		else{
			if( !$client->query($queries['add'], $filename) ){
				$out = '['.$client->getErrorCode().'] '.$client->getErrorMessage();
			}
		}
		
		return $out;
	}
	
	
	/**
	* Get the file name
	*/
	public function getName(){
		return $_FILES['qqfile']['name'];
	}
	
	
	/**
	* Get the file size
	*/
	public function getSize(){
		return $_FILES['qqfile']['size'];
	}
}


/**
* Handles saving a file uploaded via XHR or Form
*/
class FileUploader {
	private readonly array $allowedExtensions;
	private \uploadedFileXhr|\uploadedFileForm|bool $file;
	
	function __construct(array $allowedExtensions = [], private $sizeLimit = 10485760){   
		// Override parameters in the config variables
		$allowedExtensions = array_map("strtolower", $allowedExtensions);
		$this->allowedExtensions = $allowedExtensions;
		
		// Test if the server can accept the config
		$this->_checkServerSettings();
		
		// Test if there's actually a file; if so, select the save type (XHR or Form)
		if( isset($_GET['qqfile']) ){
			$this->file = new uploadedFileXhr();
		}
		else if( isset($_FILES['qqfile']) ){
			$this->file = new uploadedFileForm();
		}
		else{
			$this->file = false; 
		}
	}
	
	
	/**
	* Check if the server can accept the configured max size
	*/
	private function _checkServerSettings(): void{
		$postSize = $this->_toBytes( ini_get('post_max_size') );
		$uploadSize = $this->_toBytes( ini_get('upload_max_filesize') );
		
		if( $postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit ){
			$size = max(1, $this->sizeLimit / 1024 / 1024) . 'Mo';
			die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
		}
	}
	
	
	/**
	* Convert GB, MB or KB from php.ini into bytes
	*/
	private function _toBytes(string|bool $str){
		$val = trim($str);
		$last = strtolower($str[strlen($str)-1]);
		switch($last){
			case 'g': $val *= 1024;
			case 'm': $val *= 1024;
			case 'k': $val *= 1024;
		}
		return $val;
	}
	
	
	/**
	* Save the sent file to a folder
	*
	* @param string   $uploadDirectory  -> Path of the destination folder
	* @param bool     $replaceOldFile   -> Replace existing files with the same name? No by default
	* @param function $filenameFunction -> The function that processes the filename
	* @return array('success' => true) or array('error' => 'error message')
	*/
	public function handleUpload(string $uploadDirectory, $replaceOldFile = false, $filenameFunction = null): array{
		// If we can write to the destination folder
		if( !is_writable($uploadDirectory) ){
			return ['error' => Utils::t('Server error. The upload destination folder isn\'t writable.')];
		}
		
		// If a file was actually sent
		if( !$this->file ){
			return ['error' => Utils::t('No file was uploaded')];
		}
		
		// Get the file size and check it's not empty or over the configured limit
		$size = $this->file->getSize();
		if($size === 0){
			return ['error' => Utils::t('The file is empty.')];
		}
		if($size > $this->sizeLimit){
			return ['error' => Utils::t('The file size is too large.')];
		}
		
		// Get the file name and extension to check it's a valid extension and the file name doesn't already exist
		$pathinfo = pathinfo( (string) $this->file->getName() );
		if($filenameFunction != null){
			$filename = $filenameFunction($pathinfo['filename']);
		}
		else{
			$filename = $pathinfo['filename'];
		}
		$ext = $pathinfo['extension'];
		if( $this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions) ){
			$these = implode(', ', $this->allowedExtensions);
			return ['error' => Utils::t('The file has invalid extension (allowed extensions:').' '.$these.').'];
		}
		if( !$replaceOldFile ){
			// For each file, check if it exists; if so, append a random number from 10 to 99
			while( file_exists($uploadDirectory . $filename . '.' . $ext) ){
				$filename .= random_int(10, 99);
			}
		}
		
		// Save the file
		if( $this->file->save($uploadDirectory . $filename . '.' . $ext) ){
			return ['success' => true];
		}
		else{
			return ['error' => Utils::t('The file was not uploaded. Upload has been cancelled or a server error occurred.')];
		}
	}
	
	
	/**
	* Save the sent file to a folder on an FTP server
	*
	* @param resource $ftp_stream       -> The FTP connection resource
	* @param string   $uploadDirectory  -> Path of the destination folder
	* @param bool     $replaceOldFile   -> Replace existing files with the same name? No by default
	* @param function $filenameFunction -> The function that processes the filename
	* @return array('success' => true) or array('error' => 'error message')
	*/
	public function handleUploadFTP($ftp_stream, $uploadDirectory, $replaceOldFile = false, $filenameFunction = null): array{
		// If we can write to the destination folder
		if( ! @ftp_chdir($ftp_stream, $uploadDirectory) ){
			return ['error' => Utils::t('Server error. The upload destination folder isn\'t writable.')];
		}
		
		// If a file was actually sent
		if( !$this->file ){
			return ['error' => Utils::t('No file was uploaded')];
		}
		
		// Get the file size and check it's not empty or over the configured limit
		$size = $this->file->getSize();
		if($size === 0){
			return ['error' => Utils::t('The file is empty.')];
		}
		if($size > $this->sizeLimit){
			return ['error' => Utils::t('The file size is too large.')];
		}
		
		// Get the file name and extension to check it's a valid extension and the file name doesn't already exist
		$pathinfo = pathinfo( (string) $this->file->getName() );
		if($filenameFunction != null){
			$filename = $filenameFunction($pathinfo['filename']);
		}
		else{
			$filename = $pathinfo['filename'];
		}
		$ext = $pathinfo['extension'];
		if( $this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions) ){
			$these = implode(', ', $this->allowedExtensions);
			return ['error' => Utils::t('The file has invalid extension (allowed extensions:').' '.$these.').'];
		}
		
		// If old files should not be replaced
		if( !$replaceOldFile ){
			// Function to check if the file exists on the FTP
			function ftp_file_exists($ftp_stream, string $uploadDirectory, string $filename): bool{
				$files = ftp_nlist($ftp_stream, $uploadDirectory);
				if( count($files) > 0){
					foreach($files as $file){
						if($uploadDirectory.$filename == $file){
							return true;
						}
					}
				}
				return false;
			}
			// For each file, check if it exists; if so, append a random number from 10 to 99
			while( ftp_file_exists($ftp_stream, $uploadDirectory, $filename.'.'.$ext) ){
				$filename .= random_int(10, 99);
			}
		}
		
		// Save the file
		if( $this->file->saveFTP($ftp_stream, $uploadDirectory, $filename.'.'.$ext) ){
			return ['success' => true];
		}
		else{
			return ['error' => Utils::t('The file was not uploaded. Upload has been cancelled or a server error occurred.')];
		}
	}
	
	
	/**
	* Save the sent file to a folder on a Maniaplanet server
	*
	* @param resource $client           -> The XMLRPC client resource
	* @param string   $localDirectory   -> Writable local destination folder, or null to write through the game server (WriteFile)
	* @param string   $serverDirectory  -> The destination folder as the game server sees it
	* @param function $filenameFunction -> The function that processes the filename
	* @return array('success' => true) or array('error' => 'error message')
	*/
	public function handleUploadManiaPlanetServer($client, ?string $localDirectory, string $serverDirectory, $queries, $filenameFunction = null): array{
		// If the client is initialized
		if (!$client->socket || $client->protocol == 0) {
			return ['error' => Utils::t('Client not initialized')];
		}
		
		// If a file was actually sent
		if( !$this->file ){
			return ['error' => Utils::t('No file was uploaded')];
		}
		
		// Get the file size and check it's not empty or over the configured limit
		$size = $this->file->getSize();
		if($size === 0){
			return ['error' => Utils::t('The file is empty.')];
		}
		if($size > $this->sizeLimit){
			return ['error' => Utils::t('The file size is too large.')];
		}
		
		// Get the file name and extension to check it's a valid extension and the file name doesn't already exist
		$pathinfo = pathinfo( (string) $this->file->getName() );
		if($filenameFunction != null){
			$filename = $filenameFunction($pathinfo['filename']);
		}
		else{
			$filename = $pathinfo['filename'];
		}
		$ext = $pathinfo['extension'];
		if( $this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions) ){
			return ['error' => Utils::t('The file has invalid extension (allowed extensions:').' '.implode(', ', $this->allowedExtensions).').'];
		}
		
		// Save the file
		$error = false;
		$fileName = $filename.'.'.$ext;
		$serverPathToFile = $serverDirectory.$fileName;
		if( $localDirectory !== null ){
			if( ($result = $this->file->save($localDirectory.$fileName)) !== true ){
				$error = true;
			}
		}
		else{
			if( ($result = $this->file->writeMap($client, $serverPathToFile)) !== true ){
				$error = true;
			}
		}
		
		if(!$error){
			// Add/insert the map
			if( isset($_GET['type']) && $_GET['type'] != 'local' ){
				$out = $this->file->saveMap($client, $serverPathToFile, $queries);
			}
			// Local upload only
			else{
				$out = true;
			}
		}
		else{
			// Error from the save or writeMap functions
			if($result){
				$out = $result;
			}
			else{
				$out = 'write error';
			}
		}
		
		if($out === true){
			return ['success' => true, 'out' => $out];
		}
		else{
			return ['error' => Utils::t('The file was not uploaded. Upload has been cancelled or a server error occurred.').' ('.$out.')'];
		}
	}
	
	
	/**
	* Save the sent file locally
	*
	* @param string   $uploadDirectory   -> Path of the destination folder
	* @param bool     $replaceOldFile    -> Replace existing files with the same name? No by default
	* @param array    $allowedExtensions -> Allowed extensions: array('jpg', 'png', 'gif');
	* @param int      $sizeLimit         -> Upload size limit (equal to or lower than the PHP config)
	* @param function $filenameFunction  -> The function that processes the filename
	* @return array('success' => true) or array('error' => 'error message')
	*/
	public static function saveUploadedFile($uploadDirectory, $replaceOldFile = false, $allowedExtensions = [], $sizeLimit = 10485760, $filenameFunction = null){
		$uploader = new FileUploader($allowedExtensions, $sizeLimit);
		return $uploader->handleUpload($uploadDirectory, $replaceOldFile, $filenameFunction);
	}
	
	
	/**
	* Save the sent file to an FTP server
	*
	* @param resource $ftp_stream        -> The FTP connection resource
	* @param string   $uploadDirectory   -> Path of the destination folder
	* @param bool     $replaceOldFile    -> Replace existing files with the same name? No by default
	* @param array    $allowedExtensions -> Allowed extensions: array('jpg', 'png', 'gif');
	* @param int      $sizeLimit         -> Upload size limit (equal to or lower than the PHP config)
	* @param function $filenameFunction  -> The function that processes the filename
	* @return array('success' => true) or array('error' => 'error message')
	*/
	public static function saveUploadedFileToFTP($ftp_stream, $uploadDirectory, $replaceOldFile = false, $allowedExtensions = [], $sizeLimit = 10485760, $filenameFunction = null){
		$uploader = new FileUploader($allowedExtensions, $sizeLimit);
		return $uploader->handleUploadFTP($ftp_stream, $uploadDirectory, $replaceOldFile, $filenameFunction);
	}
	
	
	/**
	* Save the sent file to a Maniaplanet dedicated server
	*
	* @param resource $client            -> The XMLRPC client resource
	* @param string   $localDirectory    -> Writable local destination folder, or null to write through the game server
	* @param string   $serverDirectory   -> The destination folder as the game server sees it
	* @param array    $queries           -> Queries to run and the type of list addition: array('insert' => 'InsertMap', 'add' => 'AddMap', 'type' => 'add')
	* @param array    $allowedExtensions -> Allowed extensions: array('jpg', 'png', 'gif');
	* @param int      $sizeLimit         -> Upload size limit (equal to or lower than the PHP config)
	* @param function $filenameFunction  -> The function that processes the filename
	* @return array('success' => true) or array('error' => 'error message')
	*/
	public static function saveUploadedFileToManiaPlanetServer($client, $localDirectory, $serverDirectory, $queries, $allowedExtensions = [], $sizeLimit = 10485760, $filenameFunction = null){
		$uploader = new FileUploader($allowedExtensions, $sizeLimit);
		return $uploader->handleUploadManiaPlanetServer($client, $localDirectory, $serverDirectory, $queries, $filenameFunction);
	}
}
?>