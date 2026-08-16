<?php

/**
* Class for cache management
*/
class AdminServCache {
	private readonly string $folder;
	
	/**
	* Cache initialization
	*/
	function __construct(){
		$this->folder = AdminServConfig::$PATH_RESOURCES.'cache/';
		
		if( !file_exists($this->folder) ){
			Folder::create($this->folder);
		}
	}
	
	
	/**
	* Save the value to a file
	*
	* @param string $key   -> Cache key
	* @param array  $value -> Value to store
	* @return bool
	*/
	public function set(string $key, $value){
		$out = false;
		$file = $this->folder . $key . '.json';
		$data = json_encode($value);
		
		if( file_exists($file) ){
			if( File::save($file, $data, false) ){
				$out = true;
			}
		}
		else{
			if( File::save($file) ){
				self::set($key, $value);
			}
		}
		
		$error = self::getErrorMsg('set');
		if($error){
			$out = false;
			AdminServ::error($error);
		}
		
		return $out;
	}
	
	
	/**
	* Get the value from a file
	*
	* @param string $key -> Cache key to retrieve
	* @return array()
	*/
	public function get(string $key){
		$out = [];
		$file = $this->folder . $key . '.json';
		
		if( file_exists($file) ){
			$data = file_get_contents($file);
			$out = json_decode($data, true);
		}
		
		$error = self::getErrorMsg('get');
		if($error){
			$out = [];
			AdminServ::error($error);
		}
		
		return $out;
	}
	
	
	/**
	* Delete a cache file
	*
	* @param string $key -> Cache key to delete
	* @return bool
	*/
	public function delete(string $key){
		$out = false;
		$file = $this->folder . $key . '.json';
		
		if( file_exists($file) ){
			if( ($result = File::delete($file)) !== true ){
				AdminServ::error($result);
			}
			else{
				$out = true;
			}
		}
		
		return $out;
	}
	
	
	/**
	* Get the error message from JSON encoding/decoding
	*/
	public static function getErrorMsg(string $type){
		switch( json_last_error() ){
			case JSON_ERROR_DEPTH:
				return 'CACHE ('.$type.') - Profondeur maximale atteinte';
			case JSON_ERROR_STATE_MISMATCH:
				return 'CACHE ('.$type.') - State mismatch or underflow';
			case JSON_ERROR_CTRL_CHAR:
				return 'CACHE ('.$type.') - Control character error';
			case JSON_ERROR_SYNTAX:
				return 'CACHE ('.$type.') - Syntax error; malformed JSON';
			case JSON_ERROR_UTF8:
				return 'CACHE ('.$type.') - Malformed UTF-8 characters, likely an encoding error';
		}
	}
}
?>