<?php
/**
* Class Str
*
* String processing methods
*/
abstract class Str {
	
	/**
	* Format the size
	* 
	* @param int $size -> Size
	* @return string
	*/
	public static function formatSize($size){
		$u = array('octets', 'Ko', 'Mo', 'Go', 'To');
		$i = 0;
		$m = 0;
		
		while($size >= 1){
			$m = $size;
			$size /= 1024;
			$i++;
		}
		if(!$i){
			$i = 1;
		}
		$d = explode('.', $m);
		
		if($d[0] != $m){
			$m = number_format($m, 1, ',', ' ');
		}
		
		return $m.' '.$u[$i-1];
	}
	
	
	/**
	* Change backslashes in a local link to forward slashes for web use
	*
	* @param string $path -> Path to modify
	* @return string
	*/
	public static function toSlash($path){
		$out = null;
		
		if( strstr($path, '\\') ){
			$newPath = explode('\\', $path);
			foreach($newPath as $part){
				$out .= $part.'/';
			}
			$out = substr($out, 0, -1);
		}
		else{
			$out = $path;
		}
		
		return $out;
	}
	
	
	/**
	* Replace or remove special characters
	*
	* @param string $str -> The string
	*/
	public static function replaceSpecialChars($str, $replaceSpace = true){
		// Lists
		$toRemove = array(
			"'", '"', '&', '<', '>', '+', '=', '*', '/', '²', '~', '#', '{', '[', '(', '|', '`', '\\', '^', '@', ')', ']', '}', '¨', '$', '£', '¤',
			'!', '%', '§', ':', ';', ',', '?', '«', '»', '“', '”', '„', '…', '¡', '¿', '‘', '’', 'ˆ', '˜', '¸', '·', '•', '¯', '‾', '—', '¦', '†',
			'‡', '¶', '©', '®', '™', '◊', '♠', '♣', '♥', '♦', '←', '↑', '→', '↓', '↔', 'Γ', 'δ', 'Δ', 'ζ', 'θ', 'Θ', 'ι', 'Λ', 'ξ', 'Ξ', 'π', 'Π',
			'σ', 'ς', 'Σ', 'φ', 'Φ', 'ψ', 'Ψ', 'ω', 'Ω', '°', '≤', '≥', '≈', '≠', '≡', '±', '×', '÷', '⁄', '‰', '¼', '½', '¾', '¹', '³', 'º', 'ª',
			'′', '″', '∂', '∏', '∑', '√', '∞', '¬', '∩', '∫', '⇒', '⇔', '∀', '∃', '∇', '∈', '∋', '∝', '∠', '∧', '∨', '∴', '∼', '⊂', '⊃',
			'⊆', '⊇', '⊥'
		);
		if($replaceSpace){
			$toDash = array(
				' ', '  '
			);
		}
		
		// Replacement
		$str = trim($str);
		$str = str_replace($toRemove, '', $str);
		if($replaceSpace){
			$str = str_replace($toDash, '-', $str);
		}
		else{
			$str = trim($str);
		}
		return $str;
	}
	
	
	/**
	* Replace accented characters with their normal equivalent
	*
	* @param string $str -> The string
	*/
	public static function replaceAccentChars($str){
		$str = utf8_decode($str);
		$str = strtr($str, utf8_decode('ÀÁÂÃÄÅΑλàáâãäåαβþÐÒÓÔÕÖØòóôõöøÈÉÊËèéêë€εƒÇç¢ÌÍÎÏìíîïÙÚÛÜ∪ùúûüµυÿýýŸÝ¥ÑñηŔŕρτ'), 'AAAAAAAAaaaaaaaBbDOOOOOOooooooEEEEeeeeEefCccIIIIiiiiUUUUUuuuuuuyyyYYYNnnRrpt');
		return utf8_encode($str);
	}
	
	
	/**
	* Replace or remove special characters and replace accented characters
	*
	* @param string $str -> The string
	*/
	public static function replaceChars($str){
		return self::replaceSpecialChars( self::replaceAccentChars($str) );
	}
	
	
	/**
	* Get the type of a value
	*
	* @param  mixed $val -> The value to analyze
	* @return string
	*/
	public static function getValueType($val){
		$out = null;
		
		if( is_string($val) ){
			$out = 'string';
		}
		elseif( is_bool($val) ){
			$out = 'bool';
		}
		elseif( is_float($val) ){
			$out = 'float';
		}
		elseif( is_int($val) ){
			$out = 'int';
		}
		elseif( is_array($val) ){
			$out = 'array';
		}
		elseif( is_object($val) ){
			$out = 'object';
		}
		elseif( is_resource($val) ){
			$out = 'resource';
		}
		
		return $out;
	}
	
	
	/**
	* Convert the type of a value
	*
	* @param mixed  $val  -> The value to convert
	* @param string $type -> The type to assign
	*/
	public static function setValueType($val, $type){
		switch($type){
			case 'array':
				$val = (array)$val;
				break;
			case 'bool':
			case 'boolean':
				$val = (bool)$val;
				break;
			case 'float':
			case 'double':
				$val = (float)$val;
				break;
			case 'int':
			case 'integer':
				$val = (int)$val;
				break;
			default:
				$val = (string)$val;
		}
		
		return $val;
	}
}
?>