<?php
/**
* This class handles TMF strings, meaning it can convert a TM string into:
*   - html (shadow and links are handled)
*   - plain text -> without formatting
*   - image (any supported format, with or without background image)
*
* @author Mr. Das - Gildas de Cadoudal - gdecad@gmail.com
* @package framework
*/
class TmNick {
	/**
	* These codes are valid across all TrackMania games:
	* 
	* $t Uppercase
	* $i Italic
	* $s Shadowed
	* $w Wide spacing
	* $n Short spacing
	* $m Normal spacing
	* $g Default color
	* $z Reset everything
	* $$ Write a "$"
	* $o Bold with semi-wide spacing
	* $h Internal link to a Manialink
	* $p Internal link with player info
	* $l External link to a website
	*/
	public  static $m_fontPath = 'fonts/';
	public  static $linkProtocol = 'tmtp';
	private static string $m_pattern = '/\\$\\$|\\$[0-9a-fA-F]{1,3}|\\$[twmgonishplzTWMGONISHLZ]/';
	private static string $m_patternLink = '/(?:\\[(?P<url>[^\\[\\]]*)\\]){0,1}(?P<label>.+){0,1}/';
	private static array $m_lstTags = ['h', 'p', 'l', 'o', 'g', 't', 'w', 'm', 'n', 'i', 's', 'z', 'color'];
	private static array $m_css = ['h' => 'tmnick_internallink', 'p' => 'tmnick_internallink_withlogin', 'l' => 'tmnick_externallink', 'o' => 'tmnick_bold', 'g' => 'tmnick_defaultcolor', 't' => 'tmnick_upper', 'w' => 'tmnick_wide', 'm' => 'tmnick_normal', 'n' => 'tmnick_short', 'i' => 'tmnick_italic', 's' => 'tmnick_shadowed', 'z' => 'tmnick_raz'];
	private static array $m_fonts = ['h' => '', 'l' => '', 'o' => 'DM', 'g' => '', 't' => '', 'w' => '', 'm' => '', 'n' => 'CN', 'i' => 'IT', 's' => '', 'z' => 'MD'];
	private static string $m_defaultHexaOnError = '0';
	private static array $m_colorNameToHtml = ['aliceblue' => '#f0f8ff', 'antiquewhite' => '#faebd7', 'aqua' => '#00ffff', 'aquamarine' => '#7fffd4', 'azure' => '#f0ffff', 'beige' => '#f5f5dc', 'bisque' => '#ffe4c4', 'black' => '#000000', 'blanchedalmond' => '#ffebcd', 'blue' => '#0000ff', 'blueviolet' => '#8a2be2', 'brown' => '#a52a2a', 'burlywood' => '#deb887', 'cadetblue' => '#5f9ea0', 'chartreuse' => '#7fff00', 'chocolate' => '#d2691e', 'coral' => '#ff7f50', 'cornflowerblue' => '#6495ed', 'cornsilk' => '#fff8dc', 'crimson' => '#dc143c', 'cyan' => '#00ffff', 'darkblue' => '#00008b', 'darkcyan' => '#008b8b', 'darkgoldenrod' => '#b8860b', 'darkgray' => '#a9a9a9', 'darkgrey' => '#a9a9a9', 'darkgreen' => '#006400', 'darkkhaki' => '#bdb76b', 'darkmagenta' => '#8b008b', 'darkolivegreen' => '#556b2f', 'darkorange' => '#ff8c00', 'darkorchid' => '#9932cc', 'darkred' => '#8b0000', 'darksalmon' => '#e9967a', 'darkseagreen' => '#8fbc8f', 'darkslateblue' => '#483d8b', 'darkslategray' => '#2f4f4f', 'darkslategrey' => '#2f4f4f', 'darkturquoise' => '#00ced1', 'darkviolet' => '#9400d3', 'deeppink' => '#ff1493', 'deepskyblue' => '#00bfff', 'dimgray' => '#696969', 'dimgrey' => '#696969', 'dodgerblue' => '#1e90ff', 'firebrick' => '#b22222', 'floralwhite' => '#fffaf0', 'forestgreen' => '#228b22', 'fuchsia' => '#ff00ff', 'gainsboro' => '#dcdcdc', 'ghostwhite' => '#f8f8ff', 'gold' => '#ffd700', 'goldenrod' => '#daa520', 'gray' => '#808080', 'grey' => '#808080', 'green' => '#008000', 'greenyellow' => '#adff2f', 'honeydew' => '#f0fff0', 'hotpink' => '#ff69b4', 'indianred' => '#cd5c5c', 'indigo' => '#4b0082', 'ivory' => '#fffff0', 'khaki' => '#f0e68c', 'lavender' => '#e6e6fa', 'lavenderblush' => '#fff0f5', 'lawngreen' => '#7cfc00', 'lemonchiffon' => '#fffacd', 'lightblue' => '#add8e6', 'lightcoral' => '#f08080', 'lightcyan' => '#e0ffff', 'lightgoldenrodyellow' => '#fafad2', 'lightgray' => '#d3d3d3', 'lightgrey' => '#d3d3d3', 'lightgreen' => '#90ee90', 'lightpink' => '#ffb6c1', 'lightsalmon' => '#ffa07a', 'lightseagreen' => '#20b2aa', 'lightskyblue' => '#87cefa', 'lightslategray' => '#778899', 'lightslategrey' => '#778899', 'lightsteelblue' => '#b0c4de', 'lightyellow' => '#ffffe0', 'lime' => '#00ff00', 'limegreen' => '#32cd32', 'linen' => '#faf0e6', 'magenta' => '#ff00ff', 'maroon' => '#800000', 'mediumaquamarine' => '#66cdaa', 'mediumblue' => '#0000cd', 'mediumorchid' => '#ba55d3', 'mediumpurple' => '#9370d8', 'mediumseagreen' => '#3cb371', 'mediumslateblue' => '#7b68ee', 'mediumspringgreen' => '#00fa9a', 'mediumturquoise' => '#48d1cc', 'mediumvioletred' => '#c71585', 'midnightblue' => '#191970', 'mintcream' => '#f5fffa', 'mistyrose' => '#ffe4e1', 'moccasin' => '#ffe4b5', 'navajowhite' => '#ffdead', 'navy' => '#000080', 'oldlace' => '#fdf5e6', 'olive' => '#808000', 'olivedrab' => '#6b8e23', 'orange' => '#ffa500', 'orangered' => '#ff4500', 'orchid' => '#da70d6', 'palegoldenrod' => '#eee8aa', 'palegreen' => '#98fb98', 'paleturquoise' => '#afeeee', 'palevioletred' => '#d87093', 'papayawhip' => '#ffefd5', 'peachpuff' => '#ffdab9', 'peru' => '#cd853f', 'pink' => '#ffc0cb', 'plum' => '#dda0dd', 'powderblue' => '#b0e0e6', 'purple' => '#800080', 'red' => '#ff0000', 'rosybrown' => '#bc8f8f', 'royalblue' => '#4169e1', 'saddlebrown' => '#8b4513', 'salmon' => '#fa8072', 'sandybrown' => '#f4a460', 'seagreen' => '#2e8b57', 'seashell' => '#fff5ee', 'sienna' => '#a0522d', 'silver' => '#c0c0c0', 'skyblue' => '#87ceeb', 'slateblue' => '#6a5acd', 'slategray' => '#708090', 'slategrey' => '#708090', 'snow' => '#fffafa', 'springgreen' => '#00ff7f', 'steelblue' => '#4682b4', 'tan' => '#d2b48c', 'teal' => '#008080', 'thistle' => '#d8bfd8', 'tomato' => '#ff6347', 'turquoise' => '#40e0d0', 'violet' => '#ee82ee', 'wheat' => '#f5deb3', 'white' => '#ffffff', 'whitesmoke' => '#f5f5f5', 'yellow' => '#ffff00', 'yellowgreen' => '#9acd32'];
	
	
	/**
	* Convert a TM string to HTML
	*
	* @param string $nick                   -> The TM string, no length limit, can be multiline
	* @param int    $fontSize               -> Font size
	* @param bool   $forcedDefault          -> Force the default color to be used instead of formatting
	* @param string $defaultColor           -> The default color (can be HTML, named, RGB array, or TM format)
	* @param string $defaultBackgroundColor -> The background color (transparent is possible) (can be HTML, named, RGB array, or TM format)
	* @return string HTML string without the CSS
	*/
	public static function toHtml($nick, $fontSize = 11, $forcedShadow = false, $forcedDefault = false, $defaultColor = 'white', $defaultBackgroundColor = 'transparent'): string{
		if($forcedShadow){ $nick = '$s'.str_replace(['$s', '$S'], '', $nick); }
		$linkProtocol = strtolower((string) self::$linkProtocol);
		
		$defaultHTMLColor = $defaultColor;
		if( empty($defaultColor) ){
			$defaultHTMLColor = 'white';
		}
		else if($defaultColor[0] == '$'){
			$defaultHTMLColor = self::colorToHtml($defaultColor);
		}
		
		$defaultHTMLBackgroundColor = $defaultBackgroundColor;
		if( empty($defaultBackgroundColor) ){
			$defaultHTMLBackgroundColor = 'black';
		}
		else if($defaultBackgroundColor[0] == '$'){
			$defaultHTMLBackgroundColor = self::colorToHtml($defaultBackgroundColor);
		}
		
		$strHtml = '<span class="tmnick_global" style="background-color:'.$defaultHTMLBackgroundColor.'">';
		$lastLink = false;
		$lastLinkTag = false;
		$stack = self::_parse($nick, $defaultColor);
		foreach($stack as $ss){
			$lstClass = $lstStyle = [];
			$prefix = $suffix = '';
			$preContent = $postContent = '';
			foreach($ss['tagsOpen'] as $tag => $open){
				if( (is_string($open) and !empty($open)) or (is_bool($open) and $open) ){
					if( ($tag == 'h' or $tag == 'p' or $tag == 'l') and $lastLink !== $open ){
						$scheme = parse_url($open, PHP_URL_SCHEME);
						if( is_null($scheme) ){
							if($tag == 'l'){
								$open = 'http://'.$open;
							}
							else if($tag == 'h'){
								$open = $linkProtocol.':///:'.$open;
							}
							else if($tag == 'p'){
								$open = $linkProtocol.':///:'.$open.'?playerlogin=&lang=&nickname=&path=';
							}
						}
						
						$prefix .= '<a href="'.$open.'" target="_blank" class="'.self::$m_css[$tag].'">';
						$lastLinkTag = $tag;
						$lastLink = $open;
					}
					else if($tag == 'color'){
						if($forcedDefault){
							$lstStyle[] = 'color:'.$defaultHTMLColor;
						}
						else{
							$lstStyle[] = 'color:'.$open;
						}
					}
					else{
						$lstClass[] = self::$m_css[$tag];
					}
				}
				else{
					if($tag == $lastLinkTag and $lastLink !== $open){
						$prefix .= '</a>';
						$lastLink = false;
					}
				}
			}
			
			$strHtml .= $prefix;
			if( $ss['content'] !== '' && $ss['content'] !== null ){
				$strHtml .= '<span class="{$class$}" style="font-size: '.$fontSize.'pt;{$style$}">';
				$strHtml .= $preContent.'{$content$}'.$postContent;
				$strHtml .= '</span>';
			}
			$strHtml .= $suffix;
			
			$strHtml = str_replace('{$content$}', $ss['content'], $strHtml);
			$strHtml = str_replace('{$class$}', implode(' ', $lstClass), $strHtml);
			$strHtml = str_replace('{$style$}', implode(';', $lstStyle), $strHtml);
		}
		
		$tags = ['h', 'p', 'l'];
		foreach($tags as $tag){
			if( isset($ss) and $ss['tagsOpen'][$tag] ){
				$strHtml .= '</a>';
			}
		}
		
		return $strHtml . '</span>';
	}
	
	
	/**
	* Display a TM string as an image
	*
	* @param string $nick                 -> The TM string, no length limit, can be multiline
	* @param string $type                 -> The image type (gif, jpg, jpeg, png, ico, bmp, wbmp, xpm)
	* @param int    $fontSize             -> Font size
	* @param bool   $forceDefault         -> Force the default color to be used instead of formatting
	* @param string $defaultColor         -> The default color (can be HTML, named, RGB array, or TM format)
	* @param string $defaultImgBackground -> Path to the background image if you want one
	* @param array  $arrRelativePosition  -> Assoc array with 'x' and 'y' keys, giving the text position relative to the left and/or top edge of the background image; values can be negative, in which case they represent the position from the right and/or bottom edge
	* @return bool
	*/
	public static function toImg($nick, string $type = 'png', $fontSize = 12, $forceDefault = false, $defaultColor = 'white', $defaultImgBackground = null, $arrRelativePosition = [] ){
		if(self::_isSupportedImageType($type) === false){
			$imErr = self::_getErrorRessource('The type "'.$type.'" is not supported.');
			return self::_getImageFromRessource($imErr, 'png');
		}
		
		$res = true;
		self::toText($nick);
		
		$lines = str_replace('\r\n', '\n', $nick);
		$lines = str_replace('\r', '\n', $lines);
		$lines = explode('\n', $lines);
		
		// this gets the length of the longest string, in characters to determine
		// the width of the output image
		$xMax = -1;
		$len = 0;
		$count_lines =  count($lines);
		for($x = 0; $x < $count_lines; $x++){
			if(strlen($lines[$x]) > $len){
				$len = strlen(self::toText($lines[$x]));
				$xMax = $x;
			}
		}
		
		$defaultRGBColor = self::colorToRGB($defaultColor);
		
		$width = $height = 0;
		
		// initialization
		$fontFile = self::$m_fontPath.'FRADMIT.TTF';
		$box = self::_imagettfbboxextended($fontSize, 0, $fontFile, self::toText($lines[$xMax]));
		
		// next we turn the height and width into pixel values
		$width = $box['width'] * 2;
		$height = $box['height'] * 2;
		$baseline = $box['y'];
		
		// create image with dimensions to fit text, plus two extra rows and
		// two extra columns for border
		$hasBgImd = false;
		$im = null;
		if( !is_null($defaultImgBackground) and file_exists($defaultImgBackground) ){
			$infos = getimagesize($defaultImgBackground);
			$width = $infos[0];
			$height = $infos[0];
			
			$imgString = file_get_contents($defaultImgBackground);
			$im = imagecreatefromstring($imgString);
			$hasBgImd = true;
		}else{
			$im = imagecreate($width, $height);
		}
		
		if($im){
			// image creation success
			$backColor = imagecolorallocatealpha($im, 254, 254, 254, 127);
			$shadowColor = imagecolorallocatealpha($im, 105, 105, 105, 0);
			if(!$hasBgImd){
				imagefill($im, 0, 0, $backColor);
			}
			
			// this loop outputs the error message to the image
			// imagestring(image, font, x, y, msg, color);
			$x = $y = 0;
			
			if(is_array($arrRelativePosition) and count($arrRelativePosition) > 0){
				if( isset($arrRelativePosition['y']) ){
					if($arrRelativePosition['y'] < 0){
						$y = $width;
					}
					$y += $arrRelativePosition['y'];
				}
				if( isset($arrRelativePosition['x']) ){
					if($arrRelativePosition['x'] < 0){
						$x = $height;
					}
					$x += $arrRelativePosition['x'];
				}
			}
			
			$newX = 0;
			$newY = 0;
			for($i = 0; $i < count($lines); $i++){
				$stack = self::_parse($lines[$i]);
				$xl = $x;
				
				foreach($stack as $ss){
					if($forceDefault){
						$color = $defaultRGBColor;
					}else{
						$color = self::colorToRGB($ss['tagsOpen']['color']);
					}
					$textColor = imagecolorallocatealpha($im, $color[0], $color[1], $color[2], 0);
					$fontText = self::$m_fontPath.'FRA';
					if($ss['tagsOpen']['o'] === true){
						$fontText .= self::$m_fonts['o'];
					}else{
						$fontText .= self::$m_fonts['z'];
					}
					foreach($ss['tagsOpen'] as $t => $o){
						if($o === true and $t != 'o' and $t != 'z'){
							$fontText .= self::$m_fonts[$t];
						}
					}
					$fontText .= '.TTF';
					
					$tbox = self::_imagettfbboxextended($fontSize, 0, $fontText, $ss['content']);
					
					if($ss['tagsOpen']['s'] === true){
						imagettftext($im, $fontSize, 0, $xl + $tbox['x'] + 1, $y + $baseline + ($i * ($tbox['height'] + 5)) + 1, $shadowColor, $fontText, (string) $ss['content']);
					}
					imagettftext($im, $fontSize, 0, $xl + $tbox['x'], $y + $baseline + ($i * ($tbox['height'] + 5)), $textColor, $fontText, (string) $ss['content']);
					
					$xl += $tbox['width'];
					
					if($newX < $xl){
						$newX = $xl;
					}
					if( $newY < ($y + $baseline + ($i * ($tbox['height'] + 5))) + (abs($baseline - $tbox['y'])) ){
						$newY = ($y + $baseline + ($i * ($tbox['height'] + 5))) + (abs($baseline - $tbox['y']));
					}
					if($ss['tagsOpen']['s'] === true){
						$newX++;
						$newY++;
					}
				}
			}
			
			// now, render your image using your favorite image* function
			// (imagejpeg, for instance)
			if(!$hasBgImd){
				$im2 = imagecreate($newX, $newY);
				imagecopyresized($im2, $im, 0, 0, 0, 0, $newX, $newY, $newX, $newY);
				
				$res = self::_getImageFromRessource($im2, $type);
				imagedestroy($im2);
			}else{
				$res = self::_getImageFromRessource($im, $type);
			}
			imagedestroy($im);
		}else{
			// image creation failed, so just dump the array along with extra error
			$error[] = 'Is GD Installed ?';
			die(var_dump($error));
		}
		
		return $res;
	}
	
	
	/**
	* Convert a TM string to plain text
	*
	* @param string $nick -> The TM string, no length limit, can be multiline
	* @return string
	*/
	public static function toText($nick): ?string{
		
		if( !is_string($nick) or empty($nick) ){
			return '';
		}
		
		preg_match_all(self::$m_pattern, $nick, $matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
		$matches = $matches[0];
		
		$strText = '';
		
		$lastPos = 0;
		foreach($matches as $matche){
			[$code, $pos] = $matche;
			
			if($code === '$$'){
				$strText .= substr($nick, $lastPos, $pos - $lastPos) . '$';
				$lastPos = $pos + strlen($matche[0]);
			}
			else{
				$strText .= substr($nick, $lastPos, $pos - $lastPos);
				$lastPos = $pos + strlen($matche[0]);
			}
		}
		
		$strText .= substr($nick, $lastPos, strlen($nick) - $lastPos);
		
		return preg_replace('/(\\[[^\\[\\]]*\\])/', '', $strText);
	}
	
	
	/**
	* Convert a color in HTML, TM, RGB array, or named format into HTML format
	*
	* @param string $color
	* @return string
	*/
	public static function colorToHtml($color){
		$colorHtml = '#';
		
		if( is_string($color) and !empty($color) and strlen($color) >= 2 and strlen($color) <= 4 and ($color[0] == '$' or $color[0] == '#') ){
			// TM color code: $ + 1 to 3 hex digits. Missing digits = '0'
			// (confirmed in-game: $00 -> black, $F0 -> red, $AB -> AABB00)
			$hex = substr($color, 1);
			$hex = preg_replace('/[g-z]/', (string) self::$m_defaultHexaOnError, strtolower($hex));
			$hex = str_pad($hex, 3, '0', STR_PAD_RIGHT);
			$colorHtml .= $hex[0];
			$colorHtml .= $hex[0];
			$colorHtml .= $hex[1];
			$colorHtml .= $hex[1];
			$colorHtml .= $hex[2];
			$colorHtml .= $hex[2];
		}
		else if(is_array($color) and count($color) == 3){
			$colorHtml .= sprintf('#%02X%02X%02X', $color[1], $color[2], $color[3]);
		}
		else if(is_string($color) and !empty($color) and strlen($color) == 7 and $color[0] == '#'){
			$color = preg_replace('/[g-z]/', (string) self::$m_defaultHexaOnError, strtolower($color));
			$colorHtml = $color;
		}
		else{
			$color = strtolower($color);
			$colorHtml = self::$m_colorNameToHtml[$color];
		}
		
		return $colorHtml;
	}
	
	
	/**
	* Convert a color in HTML, TM, RGB array, or named format into RGB format
	*
	* @param string $color
	* @return string
	*/
	public static function colorToRGB($color){
		$colorRGB = [0, 0, 0];
		
		if( is_string($color) and !empty($color) and strlen($color) == 4 and ($color[0] == '$' or $color[0] == '#') ){
			$color = preg_replace('/[g-z]/', (string) self::$m_defaultHexaOnError, strtolower($color));
			$hex = $color[1];
			$hex .= $color[1];
			$colorRGB[0] = hexdec($hex);
			$hex = $color[2];
			$hex .= $color[2];
			$colorRGB[1] = hexdec($hex);
			$hex = $color[3];
			$hex .= $color[3];
			$colorRGB[2] = hexdec($hex);
		}
		else if(is_array($color) and count($color) == 3){
			$colorRGB = $color;
		}
		else if(is_string($color) and !empty($color) and strlen($color) == 7 and $color[0] == '#'){
			$color = preg_replace('/[g-z]/', (string) self::$m_defaultHexaOnError, strtolower($color));
			$hex = $color[1];
			$hex .= $color[2];
			$colorRGB[0] = hexdec($hex);
			$hex = $color[3];
			$hex .= $color[4];
			$colorRGB[1] = hexdec($hex);
			$hex = $color[5];
			$hex .= $color[6];
			$colorRGB[2] = hexdec($hex);
		}
		else{
			$color = strtolower($color);
			$colorRGB = self::colorToRGB(self::$m_colorNameToHtml[$color]);
		}
		
		return $colorRGB;
	}
	
	
	/**
     * Return the CSS code needed to format the HTML code
     */
    public static function getCss(): string{
		return '<style type="text/css">
			.tmnick_global {
				font-style: normal;
				font-weight: none;
				letter-spacing: 0;
			}
			.tmnick_normal { letter-spacing: 0; }
			.tmnick_short { letter-spacing: -1px; }
			.tmnick_wide { letter-spacing: 2px; }
			.tmnick_raz { font-style: normal; font-weight: none; letter-spacing: 0; }
			.tmnick_italic { font-style: italic; }
			.tmnick_bold { font-weight: bold; letter-spacing: 1px; }
			.tmnick_upper { text-transform: uppercase; }
			span.tmnick_shadow { text-shadow: 0 1px 1px #333; }
			.tmnick_shadowed { position: relative; }
			.tmnick_shadowed span { position: relative; }
			span.tmnick_shadow { position: absolute; }
			.tmnick_defaultcolor { }
			a.tmnick_internallink, a.tmnick_internallink:visited {
				color: inherit;
				text-decoration: none;
				border-bottom: thin solid Black;
			}
			a.tmnick_internallink:hover {
				color: inherit;
				text-decoration: none;
				border-bottom: thin solid Blue;
			}
			a.tmnick_internallink_withlogin, a.tmnick_internallink_withlogin:visited  {
				color: inherit;
				text-decoration: none;
				border-bottom: thin solid #F0E68C;
			}
			a.tmnick_internallink_withlogin:hover {
				color: inherit;
				text-decoration: none;
				border-bottom: thin solid Gold;
			}
			a.tmnick_externallink, a.tmnick_externallink:visited {
				color: inherit;
				text-decoration: none;
				border-bottom: thin solid Black;
			}
			a.tmnick_externallink:hover {
				color: inherit;
				text-decoration: none;
				border-bottom: thin solid Orange;
			}
		</style>';
	}
	
	
	/**
	* Remove all trackmania codes
	*
	* @param string $str   -> Text to process
	* @param array $select -> One or more codes to remove. For example, to remove only the "$s" code
	* @return string text without $s, $o, etc. codes
	*/
	public static function stripNadeoCode($str, $select = [] ): string{
		if( !empty($select) ){
			$stripNadeoCode = $select;
		}
		else{
			$stripNadeoCode = ['$w', '$W', '$n', '$N', '$o', '$O', '$i', '$I', '$t', '$T', '$s', '$S', '$g', '$G', '$z', '$Z', '$<'];
		}
		
		return str_ireplace($stripNadeoCode, '', $str);
	}
	
	
	/**
     * Parse a TM string into an internal data structure used by the toHtml, toImg, and toText methods
     *
     * @param string $nick the TM string, no length limit, can be multiline
     * @param string $defaultColor the default color (can be HTML, named, RGB array, or TM format)
     */
    private static function _parse($nick, $defaultColor = 'white'): array{
		
		if( !is_string($nick) or empty($nick) ){
			return [];
		}
		
		$defaultHTMLColor = $defaultColor;
		if( empty($defaultColor) ){
			$defaultHTMLColor = 'white';
		}
		else if($defaultColor[0] == '$'){
			$defaultHTMLColor = self::colorToHtml($defaultColor);
		}
		
		$nb = preg_match_all(self::$m_pattern, $nick, $matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
		$matches = $matches[0];
		
		$strContent = '';
		$lastPos = 0;
		
		$tagsOpen = ['h' => false, 'p' => false, 'l' => false, 'o' => false, 'g' => false, 't' => false, 'w' => false, 'm' => false, 'n' => false, 'i' => false, 's' => false, 'color' => $defaultColor];
		
		$stack = [];
		foreach($matches as $matche){
			[$code, $pos] = $matche;
			
			// Escape "$$" -> literal "$" (before the generic tag check,
			// otherwise $lastPos doesn't advance and "$$" stays as-is in the text)
			if($code === '$$'){
				$strContent = substr($nick, $lastPos, $pos - $lastPos) . '$';
				if( $strContent !== '' && $strContent !== null ){
					array_push($stack, ['content' => $strContent, 'tagsOpen' => $tagsOpen]);
				}
				$lastPos = $pos + strlen($code);
				
				foreach($stack as $s => $st){
					if( $st['content'] === '' || $st['content'] === null ){
						unset($stack[$s]);
					}
				}
				continue;
			}
			
			$tag = strtolower(substr($code, 1));
			$param = null;
			if(strlen($tag) >= 1 && strlen($tag) <= 3 && ctype_xdigit($tag)){
				// color (1 to 3 hex digits -- missing digits default to '0' in-game)
				$param = $tag;
				$tag = 'color';
			}
			
			if( !in_array($tag, self::$m_lstTags) ){
				continue;
			}
			
			$strContent = substr($nick, $lastPos, $pos - $lastPos);
			if( $strContent !== '' && $strContent !== null ){
				array_push($stack, ['content' => $strContent, 'tagsOpen' => $tagsOpen]);
			}
			
			switch($tag){
				// reset (close)
				case 'z':
					$tagsOpen = array_merge($tagsOpen,
						['o' => false, 'g' => false, 't' => false, 'w' => false, 'm' => false, 'n' => false, 'i' => false, 's' => false, 'color' => $defaultColor]
					);
					break;
				
				// defaultcolor (close)
				case 'g':
					$tagsOpen['color'] = $defaultColor;
					break;
				
				// internal link (open)
				case 'h':
				// internal link with login (open)
				case 'p':
				// external link (open)
				case 'l':
					if(!$tagsOpen[$tag]){
						// open
						$tagsOpen[$tag] = true;
					}
					else{
						// close
						$strLink = '';
						$i = count($stack) - 1;
						while($i >= 0 and $stack[$i]['tagsOpen'][$tag]){
							$strLink = $stack[$i]['content'] . $strLink;
							$i--;
						}
						
						$url = null;
						$nb = preg_match(self::$m_patternLink, $strLink, $regs, PREG_OFFSET_CAPTURE);
						
						if( !empty($regs['label'][0]) ){
							if( empty($regs['url'][0]) ){
								$url = $regs['label'][0];
							}else{
								$url = $regs['url'][0];
							}
						}
						
						$nb = count($stack) - 1;
						$lastMax = strlen($strLink);
						for($j = $nb; $j >= ($i+1); $j--){
							if( !is_null($url) ){
								$subLabel = $stack[$j]['content'];
								$max = max($lastMax - strlen($subLabel), $regs['label'][1]);
								$subLabel = substr($strLink, $max, $lastMax - $max);
								$lastMax = $max;
								$stack[$j]['content'] = $subLabel;
								
								$stack[$j]['tagsOpen'][$tag] = $url;
							}else{
								// discard
								unset($stack[$j]);
							}
						}
						
						$tagsOpen[$tag] = false;
					}
					break;
					
				// shadow (open)
				case 's':
				// upper
				case 't':
				// bold
				case 'o':
				// italic
				case 'i':
					if(!$tagsOpen[$tag]){
						// open
						$tagsOpen[$tag] = true;
					}else{
						// close
						$tagsOpen[$tag] = false;
					}
					break;
				
				// wide (open)
				case 'w':
				// normal (open)
				case 'm':
				// short (open)
				case 'n':
					if(!$tagsOpen[$tag]){
						// open
						$tagsOpen['w'] = false;
						$tagsOpen['m'] = false;
						$tagsOpen['n'] = false;
						$tagsOpen[$tag] = true;
					}else{
						// close
						$tagsOpen[$tag] = false;
					}
					break;
				
				case 'color':
					// color (open)
					$tagsOpen[$tag] = self::colorToHtml($code);
					break;
				
				default:
					$stack[(count($stack) - 1)]['content'] .= $code;
					break;
			}
			
			foreach($stack as $s => $st){
				if( $st['content'] === '' || $st['content'] === null ){
					unset($stack[$s]);
				}
			}
			
			$lastPos = $pos + strlen($matche[0]);
		}
		
		$strContent = substr($nick, $lastPos);
		if( $strContent !== '' && $strContent !== null ){
			array_push($stack, ['content' => $strContent, 'tagsOpen' => $tagsOpen]);
		}
		
		$tags = ['h', 'p', 'l'];
		foreach($tags as $tag){
			if($tagsOpen[$tag]){
				// close
				$strLink = '';
				$i = count($stack) - 1;
				while($i >= 0 and $stack[$i]['tagsOpen'][$tag]){
					$strLink = $stack[$i]['content'] . $strLink;
					$i--;
				}
				
				$url = null;
				$nb = preg_match(self::$m_patternLink, $strLink, $regs, PREG_OFFSET_CAPTURE);
				
				if(!empty($regs['label'][0]) ){
					if(empty($regs['url'][0]) ){
						$url = $regs['label'][0];
					}else{
						$url = $regs['url'][0];
					}
				}
				
				$nb = count($stack) - 1;
				$lastMax = strlen($strLink);
				for($j = $nb; $j >= ($i+1); $j--){
					if( !is_null($url) ){
						$subLabel = $stack[$j]['content'];
						$max = max($lastMax - strlen($subLabel), $regs['label'][1]);
						$subLabel = substr($strLink, $max, $lastMax - $max);
						$lastMax = $max;
						$stack[$j]['content'] = $subLabel;
						
						$stack[$j]['tagsOpen'][$tag] = $url;
					}else{
						// discard
						unset($stack[$j]);
					}
				}
				
				$tagsOpen[$tag] = false;
			}
		}
		
		return $stack;
	}
	
	
	/**
     * This function extends imagettfbbox and includes within the returned array
     * the actual text width and height as well as the x and y coordinates the
     * text should be drawn from to render correctly.  This currently only works
     * for an angle of zero and corrects the issue of hanging letters e.g. jpqg
     *
     * @param float $size
     * @param string $text
     * @return array
     */
    private static function _imagettfbboxextended($size, int $angle, string $fontfile, $text){
		$bbox = imagettfbbox($size, $angle, $fontfile, $text);
	
		//calculate x baseline
		if($bbox[0] >= -1){
			$bbox['x'] = abs($bbox[0] + 1) * -1;
		}else{
			$bbox['x'] = abs($bbox[0] + 2);
		}
	
		//calculate actual text width
		$bbox['width'] = abs($bbox[2] - $bbox[0]);
		if($bbox[0] < -1){
			$bbox['width'] = abs($bbox[2]) + abs($bbox[0]) - 1;
		}
		
		//caculate y baseline
		$bbox['y'] = abs($bbox[5] + 1);
		
		//calculate actaul text height
		$bbox['height'] = abs($bbox[7]) - abs($bbox[1]);
		if($bbox[3] > 0){
			$bbox['height'] = abs($bbox[7] - $bbox[1]) - 1;
		}
		
		return $bbox;
	}
	
	
	/**
	* Build an error image resource displaying all the errors from $errors
	*
	* @param mixed $errors string of error or array of string of error
	* @return ressource
	*/
	private static function _getErrorRessource(string $errors){
		// $error is an array of error messages, each taking up one line
        $errors = str_replace('\r\n', '\n', $errors);
        $errors = str_replace('\r', '\n', $errors);
        $errors = explode('\n', $errors);
        $error = [];
		foreach($errors as $err){
			$err = str_replace('\r\n', '\n', $err);
			$err = str_replace('\r', '\n', $err);
			$error = array_merge($error, explode('\n', $err));
		}
		
		// initialization
		$fontSize = 12;
		
		// this gets the length of the longest string, in characters to determine
		// the width of the output image
		$xMax = -1;
		$len = 0;
		for($x = 0; $x < count($error); $x++){
			if(strlen($error[$x]) > $len){
				$len = strlen($error[$x]);
				$xMax = $x;
			}
		}
		
		$fontFile = self::$m_fontPath.'FRAMD.TTF';
		$box = imagettfbbox($fontSize, 0, $fontFile, $error[$xMax]);
		
		// next we turn the height and width into pixel values
		$width = abs($box[2] - $box[0]);
		$textHeight = abs($box[1] - $box[7]);
		$height = ($textHeight + 2) * count($error);
		
		$im = imagecreate($width, $height);
		
		if($im){
			// image creation success
			$textColor = imagecolorallocatealpha($im, 255, 0, 0, 0);
			$backColor = imagecolorallocatealpha($im, 255, 255, 255, 127);
			imagefill($im, 0, 0, $backColor);
			// this loop outputs the error message to the image
			for($y = 0; $y < count($error); $y++){
				// imagestring(image, font, x, y, msg, color);
				imagettftext($im, $fontSize, 0, 0, ($textHeight + 5) * $y + $textHeight, $textColor, $fontFile, $error[$y]);
			}
			// now, render your image using your favorite image* function
			// (imagejpeg, for instance)
			return $im;
		}else{
			// image creation failed, so just dump the array along with extra error
			$error[] = "Is GD Installed?";
			die(var_dump($error));
		}
	}
	
	
	/**
     * Determine if the given type is supported
     *
     * @param string $type supported type (gif, jpg, jpeg, png, ico, bmp, wbmp, xpm)
     */
    private static function _isSupportedImageType(string $type): int|false{
		$type = strtoupper($type);
		return match ($type) {
            'GIF' => IMG_GIF,
            'JPG' => IMG_JPG,
            'JPEG' => IMG_JPEG,
            'PNG' => IMG_PNG,
            'ICO', 'BMP', 'WBMP' => IMG_WBMP,
            'XPM' => IMG_XPM,
            default => false,
        };
	}
	
	
	/**
     * Display the image corresponding to the resource in the desired format
     *
     * @param ressource $im image resource
     * @param string $type output image type
     */
    private static function _getImageFromRessource($im, string $type): bool{
		if( ($imgtype = self::_isSupportedImageType($type)) !== false ){
			header('Content-type:'.image_type_to_mime_type($imgtype).';');
			switch($imgtype){
				case IMG_GIF:
					return imagegif($im);
				case IMG_JPG:
				case IMG_JPEG:
					return imagejpeg($im);
				case IMG_PNG:
					return imagepng($im);
				case IMG_WBMP:
					return imagewbmp($im);
				case IMG_XPM:
					return imagexbm($im);
			}
		}
		header('Content-type:'.image_type_to_mime_type(IMG_PNG).';');
		return imagepng(self::_getErrorRessource('The format "'.$type.'" is not supported.'));
	}
}
?>