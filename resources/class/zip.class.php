<?php
/**
* Class Zip
*
* Processing methods for ZIP archives
*/
class Zip extends ZipArchive {
	
	/**
	* Create a ZIP archive
	*
	* @param string $filename -> Archive name
	* @param array  $struct   -> Array of archive data:
	*  $struct = array(
	*    'dirname' => array(
	*       'dirname2' => array(
	*          'file3.txt'
	*        ),
	*        'file2.txt'
	*     ),
	*     'file1.txt'
	*  );
	* @return true if successful, otherwise false
	*/
	public function create($filename, $struct, &$errorMsg = null){
		$out = false;
		
		if( !file_exists($filename) ){
			$out = self::add($filename, $struct, $errorMsg);
		}
		else{
			$errorMsg = self::_getErrorMsg(ZIPARCHIVE::ER_EXISTS);
		}
		
		return $out;
	}
	
	
	/**
	* Add the structure's data into a ZIP archive
	*
	* @param string $filename -> Archive name
	* @param array  $struct   -> Array of archive data
	* @return true if successful, otherwise false
	*/
	public function add($filename, $struct, &$errorMsg = null){
		$out = false;
		$zip = new ZipArchive();
		
		if( $result = $zip->open($filename, ZIPARCHIVE::CREATE) ){
			self::_checkStructure($zip, $struct);
			$out = true;
			$zip->close();
		}
		else{
			$errorMsg = self::_getErrorMsg($result);
		}
		
		return $out;
	}
	
	
	/**
	* Extract an archive into a folder
	*
	* @param string $filename -> Archive path
	* @param string $path     -> Extraction path
	*/
	public function extract($filename, $path, &$errorMsg = null){
		$out = false;
		$zip = new ZipArchive();
		
		if( $result = $zip->open($filename) ){
			if( !$out = $zip->extractTo($path) ){
				$errorMsg = self::_getErrorMsg('ER_EXTRACT');
			}
			$zip->close();
		}
		else{
			$errorMsg = self::_getErrorMsg($result);
		}
		
		return $out;
	}
	
	
	/**
	* List and build the data structure
	*
	* @param object $object -> The ZipArchive instance
	* @param array  $struct -> Array of archive data
	* @param string $path   -> Path from the root of the structure
	*/
	private function _checkStructure($object, array $struct, $path = null): void{
		foreach($struct as $folder => $file){
				if( is_string($folder) ){
					$object->addEmptyDir($path.$folder);
					self::_checkStructure($object, $struct[$folder], $path.$folder.'/');
				}
				else{
					$filename = null;
					if( strstr((string) $file, '/') ){
						$pathinfo = pathinfo((string) $file);
						$filename = $pathinfo['basename'];
					}
					
					if($filename){
						$object->addFile($path.$file, $filename);
					}else{
						$object->addFile($path.$file);
					}
				}
			}
	}
	
	
	/**
	* Return the ZipArchive error message matching the error code
	*
	* @param int $errorCode -> The error code
	* @return string
	*/
	private function _getErrorMsg(int|array|float|string|bool|null $errorCode): ?string{
		$out = null;
		
		switch($errorCode){
			case ZIPARCHIVE::ER_EXISTS:
				$out = 'The file already exists.';
				break;
			case ZIPARCHIVE::ER_INCONS:
				$out = 'L\'archive ZIP est inconsistante.';
				break;
			case ZIPARCHIVE::ER_INVAL:
				$out = 'Argument invalide.';
				break;
			case ZIPARCHIVE::ER_MEMORY:
				$out = 'Memory error.';
				break;
			case ZIPARCHIVE::ER_NOENT:
				$out = 'Le fichier n\'existe pas.';
				break;
			case ZIPARCHIVE::ER_NOZIP:
				$out = 'N\'est pas une archive ZIP.';
				break;
			case ZIPARCHIVE::ER_OPEN:
				$out = 'Impossible d\'ouvrir le fichier.';
				break;
			case ZIPARCHIVE::ER_READ:
				$out = 'Erreur lors de la lecture.';
				break;
			case ZIPARCHIVE::ER_SEEK:
				$out = 'Erreur de position.';
				break;
			case 'ER_EXTRACT':
				$out = 'Erreur lors de l\'extraction.';
				break;
		}
		
		return $out;
	}
}
?>