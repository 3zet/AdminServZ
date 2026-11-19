<?php

/**
* Class for AdminServ's UI
*/
class AdminServUI {
	
	/**
	* Get the application title
	*
	* @param string $type -> Return "str" or "html"
	* @return string
	*/
	public static function getTitle($type = 'str'){
		$out = null;
		$title = AdminServConfig::TITLE;
		if(!$title){
			$title = 'Admin,Serv';
		}
		
		if($type == 'html'){
			if( strstr($title, ',') ){
				$out = str_replace(',', '<span class="title-color">', $title).'</span>';
			}
			else{
				$out = $title;
			}
		}
		else{
			$out = str_replace(',', '', $title);
		}
		
		return $out;
	}
	
	
	/**
	* Check if there's a theme config
	*/
	public static function hasTheme(){
		$out = false;
		
		if( class_exists('ExtensionConfig') && isset(ExtensionConfig::$THEMES) && count(ExtensionConfig::$THEMES) > 0 ){
			$out = true;
		}
		
		return $out;
	}
	
	
	/**
	* Get the current theme or
	*
	* @param string $setTheme -> Use a different theme
	* @return $_SESSION['theme']
	*/
	public static function theme($setTheme = null): string{
		$saveCookie = false;
		
		if($setTheme){
			$_SESSION['theme'] = $setTheme;
			$saveCookie = true;
		}
		else{
			if( !isset($_SESSION['theme']) ){
				if( isset($_COOKIE['adminserv_user']) ){
					$_SESSION['theme'] = Utils::readCookieData('adminserv_user', 0);
				}
				else{
					if(AdminServConfig::DEFAULT_THEME){
						$_SESSION['theme'] = AdminServConfig::DEFAULT_THEME;
					}
					else{
						$_SESSION['theme'] = 'red';
					}
					$saveCookie = true;
				}
			}
		}
		
		if($saveCookie){
			$cookieData = [$_SESSION['theme'], self::lang(), Utils::readCookieData('adminserv_user', 2), Utils::readCookieData('adminserv_user', 3)];
			
			Utils::addCookieData('adminserv_user', $cookieData, AdminServConfig::COOKIE_EXPIRE);
		}
		
		if($setTheme){
			if(USER_PAGE == 'index'){
				Utils::redirection();
			}
			else{
				Utils::redirection(false, '?p='.USER_PAGE);
			}
		}
		
		return strtolower((string) $_SESSION['theme']);
	}
	
	
	/**
	* Get the color defined in the theme
	*
	* @param int $indexColor -> Index of the color to retrieve
	* @return string code hexa
	*/
	public static function getThemeColor($indexColor = 0){
		$out = null;
		
		if( self::hasTheme() && ($theme = self::theme()) ){
			if( isset(ExtensionConfig::$THEMES[$theme]) && isset(ExtensionConfig::$THEMES[$theme][$indexColor]) ){
				$out = ExtensionConfig::$THEMES[$theme][$indexColor];
			}
		}
		
		return $out;
	}
	
	
	/**
	* Get the list of themes
	*/
	public static function getThemeList($currentTheme = [] ){
		$out = null;
		$list = [];
		if( self::hasTheme() ){
			$list = ExtensionConfig::$THEMES;
		}

		// Current page
		if(USER_PAGE && USER_PAGE != 'index'){
			$param = '?p='.USER_PAGE .'&amp;th=';
		}
		else{
			$param = '?th=';
		}

		// Group into light/dark pairs (e.g. 'blue' + 'blue-dark')
		$pairs = [];
		foreach($list as $name => $color){
			if( str_ends_with($name, '-dark') ) continue;
			$pairs[$name] = $color;
		}

		if( count($pairs) > 0 ){
			$activeName = defined('USER_THEME') ? USER_THEME : '';
			$out .= '<ul>';

			foreach($pairs as $name => $color){
				$darkName  = $name.'-dark';
				$hasDark   = isset($list[$darkName]);
				$darkColor = $hasDark ? $list[$darkName] : null;

				$isActiveLight = ($activeName === $name);
				$isActiveDark  = ($activeName === $darkName);

				$labelLight    = Utils::t( ucfirst($name) );
				$activeLight   = $isActiveLight ? ' theme-color-active' : '';

				$out .= '<li class="theme-pair">';
				$out .= '<a tabindex="-1" class="theme-color'.$activeLight.'"'
					.' style="background-color:'.$color[0].';"'
					.' href="'.$param.$name.'"'
					.' title="'.$labelLight.'"></a>';

				if( $hasDark ){
					$labelDark  = Utils::t( ucfirst($name) ).' dark';
					$activeDark = $isActiveDark ? ' theme-color-active' : '';
					$halfStyle  = 'background:linear-gradient(135deg,'.$darkColor[0].' 50%,#111 50%)';

					$out .= '<a tabindex="-1" class="theme-color theme-color-dark'.$activeDark.'"'
						.' style="'.$halfStyle.';"'
						.' href="'.$param.$darkName.'"'
						.' title="'.$labelDark.'"></a>';
				}

				$out .= '</li>';
			}

			$out .= '</ul>';
		}

		return $out;
	}

	public static function hasLang(){
		$out = false;
		
		if( class_exists('ExtensionConfig') && isset(ExtensionConfig::$LANG) && count(ExtensionConfig::$LANG) > 0 ){
			$out = true;
		}
		
		return $out;
	}
	
	
	/**
	* Get the current language
	*
	* @param string $setLang -> Force use of the language
	* @return $_SESSION['lang']
	*/
	public static function lang($setLang = null): string{
		global $translate;
		$saveCookie = false;

		if($setLang){
			$_SESSION['lang'] = $setLang;
			$saveCookie = true;
		}
		else{
			if( !isset($_SESSION['lang']) ){
				if( isset($_COOKIE['adminserv_user']) ){
					$_SESSION['lang'] = Utils::readCookieData('adminserv_user', 1);
				}
				else{
					if( AdminServConfig::DEFAULT_LANGUAGE == 'auto' ){
						$_SESSION['lang'] = 'en';
						$autoLangCode = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr((string) $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : 'en';
						if( self::hasLang() ){
							if( in_array($autoLangCode, ExtensionConfig::$LANG) ){
								$_SESSION['lang'] = $autoLangCode;
							}
						}
					}
					else{
						if(AdminServConfig::DEFAULT_LANGUAGE){
							$_SESSION['lang'] = AdminServConfig::DEFAULT_LANGUAGE;
						}
						else{
							$_SESSION['lang'] = 'en';
						}
					}
					$saveCookie = true;
				}
			}
		}
		
		if($saveCookie){
			$cookieData = [self::theme(), $_SESSION['lang'], Utils::readCookieData('adminserv_user', 2), Utils::readCookieData('adminserv_user', 3)];
			
			Utils::addCookieData('adminserv_user', $cookieData, AdminServConfig::COOKIE_EXPIRE);
		}
		
		if($setLang){
			if(USER_PAGE == 'index'){
				Utils::redirection();
			}
			else{
				Utils::redirection(false, '?p='.USER_PAGE);
			}
		}
		
		$langCode = strtolower((string) $_SESSION['lang']);
		$langFile = AdminServConfig::$PATH_RESOURCES .'lang/'.$langCode.'.php';
		if( file_exists($langFile) ){
			require_once $langFile;
		}
		
		return $langCode;
	}
	
	
	/**
	* Get the list of languages
	*/
	public static function getLangList($currentLang = [] ){
		$out = null;
		$list = [];
		if( self::hasLang() ){
			$list = ExtensionConfig::$LANG;
		}
		$countList = count($list);
		
		// Current page
		if(USER_PAGE && USER_PAGE != 'index'){
			$param = '?p='.USER_PAGE .'&amp;lg=';
		}
		else{
			$param = '?lg=';
		}
		
		// Active language from USER_LANG or from $currentLang
		$activeLangCode = defined('USER_LANG') ? USER_LANG : '';
		if( count($currentLang) > 0 ){ $activeLangCode = key($currentLang); }
		
		// All languages side by side, active one with the active class
		if( $countList > 0 ){
			$out .= '<ul class="lang-list">';
			foreach($list as $code => $name){
				$activeClass = ($code === $activeLangCode) ? ' active' : '';
				$out .= '<li><a tabindex="-1" class="lang-flag'.$activeClass.'" style="background-image: url('. AdminServConfig::$PATH_RESOURCES .'images/lang/'.$code.'.png);" href="'.$param.$code.'" title="'.$name.'"></a></li>';
			}
			$out .= '</ul>';
		}
		
		return $out;
	}
	
	
	/**
	* Get the site's header/footer
	*/
	public static function getHeader(): void{
		global $args;
		
		if( !isset($GLOBALS['body_class']) ){
			$GLOBALS['body_class'] = null;
		}
		if(USER_PAGE != 'servers-online-config' && !self::isPageType('config') ){
			if( defined('SERVER_NAME') ){
				$GLOBALS['page_title'] = SERVER_NAME;
				$GLOBALS['body_class'] .= ' not-front';
			}
			else{
				$GLOBALS['body_class'] .= ' front';
			}
		}
		else{
			$GLOBALS['body_class'] .= ' config';
		}
		if( defined('USER_PAGE') && USER_PAGE ){
			$GLOBALS['body_class'] .= ' section-'.USER_PAGE;
		}
		if( defined('CURRENT_PLUGIN') && CURRENT_PLUGIN ){
			$GLOBALS['body_class'] .= ' plugin-'.CURRENT_PLUGIN;
		}
		$GLOBALS['body_class'] = trim($GLOBALS['body_class']);
		
		require_once AdminServConfig::$PATH_RESOURCES . 'templates/header.tpl.php';
	}
	public static function getFooter(): void{
		require_once AdminServConfig::$PATH_RESOURCES . 'templates/footer.tpl.php';
	}
	
	
	/**
	* Get the site's CSS/JS
	*/
	public static function getCss(): string{
		$path = AdminServConfig::$PATH_RESOURCES .'css/';
		$out = '<link rel="stylesheet" href="'.$path.'fileuploader.css" />'."\n\t\t"
		.'<link rel="stylesheet" href="'.$path.'global.css" />'."\n\t\t"
		.'<!--[if IE]><link rel="stylesheet" href="'.$path.'ie.css" /><![endif]-->'."\n\t\t";

		if( defined('USER_THEME') && USER_THEME ){
			$isDark      = str_ends_with(USER_THEME, '-dark');
			// jQuery UI: blue-dark → blue.css
			$jquiTheme   = $isDark ? substr(USER_THEME, 0, -5) : USER_THEME;
			if( file_exists($path.'jqueryui/'.$jquiTheme.'.css') ){
				$out .= '<link rel="stylesheet" href="'.$path.'jqueryui/'.$jquiTheme.'.css" />'."\n\t\t";
			}
			$out .= '<link rel="stylesheet" href="'.$path.'theme.php?th='.USER_THEME.'" />'."\n\t\t";
			if( $isDark ){
				$out .= '<link rel="stylesheet" href="'.$path.'dark.css" />'."\n\t\t";
			}
		}

		return $out . ('<link rel="stylesheet" media="screen and (max-width: 1000px) and (min-width: 335px)" href="' . $path . 'mobile.css" />' . "\n");
	}

	public static function getJS(): string{
		$path = AdminServConfig::$PATH_RESOURCES .'js/';
		
		return '<script src="'.$path.'jquery.js"></script>'."\n\t\t"
		.'<script src="'.$path.'jquery-ui.js"></script>'."\n\t\t"
		.'<script src="'.$path.'colorpicker.js"></script>'."\n\t\t"
		.'<script src="'.$path.'fileuploader.js"></script>'."\n\t\t"
		.'<script src="'.$path.'adminserv_funct.js"></script>'."\n\t\t"
		.'<script src="'.$path.'adminserv_event.js"></script>'."\n\t\t"
		.'<script src="'.$path.'settings-popover.js"></script>'."\n";
	}
	
	
	/**
	* Get the list of configured servers
	*
	* @return string
	*/
	public static function getServerList(){
		$out = null;
		
		if( class_exists('ServerConfig') && AdminServServerConfig::hasServer() ){
			if( isset($_GET['server']) && $_GET['server'] != null ){
				$currentServerId = intval($_GET['server']);
			}
			else{
				$currentServerId = Utils::readCookieData('adminserv', 0);
			}
			
			foreach(ServerConfig::$SERVERS as $server => $values){
				$selected = (AdminServServerConfig::getServerId($server) == $currentServerId) ? ' selected="selected"' : null;
				$out .= '<option value="'.$server.'"'.$selected.'>'.$server.'</option>';
			}
		}
		else{
			$out = '<option value="null">'.Utils::t('No server available').'</option>';
		}
		
		return $out;
	}
	
	
	/**
	* Get the list of game modes
	*
	* @param int $currentGameMode -> The game mode to select
	* @return string
	*/
	public static function getGameModeList($currentGameMode = null){
		$out = null;
		
		if( class_exists('ExtensionConfig') && isset(ExtensionConfig::$GAMEMODES) && count(ExtensionConfig::$GAMEMODES) > 0 ){
			foreach(ExtensionConfig::$GAMEMODES as $gameModeId => $gameModeName){
				// Cup nie istnieje w TmForever/TmUnited/TmClassic (potwierdzone zywym testem XML-RPC:
				// GetCupPointsLimit/GetCupNbWinners/GetCupWarmUpDuration/GetRoundCustomPoints - wszystkie -506)
				if( $gameModeName === 'Cup' && !isMpApi() ){ continue; }
				$selected = ($gameModeId == $currentGameMode) ? ' selected="selected"' : null;
				$out .= '<option value="'.$gameModeId.'"'.$selected.'>'.$gameModeName.'</option>';
			}
		}
		else{
			$out = '<option value="null">'.Utils::t('No game mode available').'</option>';
		}
		
		return $out;
	}
	
	
	/**
	* Get the form for a field
	*
	* @param array  $gameinfos -> Current and next game info
	* @param string $name      -> The field name shown in a label
	* @param string $id        -> The field id in the GameInfos array
	* @return string HTML
	*/
	public static function getGameInfosField($gameinfos, $name, $id): void{
		global $data;
		$data['gameInfosField'] = ['gameInfos' => $gameinfos, 'name' => $name, 'id' => $id];
		
		self::getTemplate('gameinfos-field');
		unset($data['gameInfosField']);
	}
	
	
	/**
	* Get the list of players
	*
	* @param string $currentPlayerLogin -> The player login to select
	* @return string
	*/
	public static function getPlayerList($currentPlayerLogin = null){
		global $client;
		$out = '<option value="null">'.Utils::t('No player available').'</option>';
		
		if( !$client->query('GetPlayerList', AdminServConfig::LIMIT_PLAYERS_LIST, 0, 1) ){
			AdminServ::error();
		}
		else{
			$playerList = $client->getResponse();
			if( count($playerList) > 0 ){
				$out = null;
				foreach($playerList as $player){
					if($currentPlayerLogin == $player['Login']){ $selected = ' selected="selected"'; }
					else{ $selected = null; }
					$out .= '<option value="'.$player['Login'].'"'.$selected.'>'.TmNick::toText($player['NickName']).'</option>';
				}
			}
		}
		
		return $out;
	}
	
	
	/**
	* Return the menu for the maps pages
	*
	* @return template html
	*/
	public static function getMapsMenuList(): void{
		global $data, $args;
		$list = ExtensionConfig::$MAPSMENU;
		$excludeLocalPage = ['maps-local', 'maps-matchset', 'maps-creatematchset'];
		if( !IS_LOCAL ){
			foreach($excludeLocalPage as $page){
				unset($list[$page]);
			}
		}
		
		$data += ['menuList' => $list];
		self::getTemplate('maps-menu');
	}
	
	
	/**
	* Get the list of folders in the "Maps" directory
	*
	* @require class "Folder"
	*
	* @param string $path        -> Path of the "Maps" folder
	* @param string $currentPath -> The path relative to "Maps"
	* @param bool   $showOptions -> Show options (new, rename, move, delete)
	* @return template maps-directorylist
	*/
	public static function getMapsDirectoryList($directory, $currentPath = null, $showOptions = true): void{
		global $data;
		
		if (class_exists('Folder')) {
			$showOptions = $showOptions && defined('IS_LOCAL_WRITABLE') && IS_LOCAL_WRITABLE;
			$data += ['folders' => [], 'currentPath' => $currentPath, 'parentPath' => null, 'showOptions' => $showOptions];
			
			// List of folders
			if (is_array($directory)) {
				// Parent folder
				if ($currentPath) {
					$params = null;
					$parentPathEx = explode('/', $currentPath);
					array_pop($parentPathEx);
					array_pop($parentPathEx);
					if (!empty($parentPathEx)) {
						$parentPath = null;
						foreach ($parentPathEx as $part) {
							$parentPath .= $part.'/';
						}
						$data['parentPath'] = $parentPath;
					}
				}
				
				if (!empty($directory['folders'])) {
					$data['folders'] = $directory['folders'];
				}
				
				// Template
				self::getTemplate('maps-directorylist');
			}
			else{
				AdminServ::error($directory);
			}
		}
		else{
			AdminServ::error('Class "Folder" not exists');
		}
	}
	
	
	/**
	* Initialize a back-office page
	*/
	public static function initBackPage(): void {
		global $client, $data, $args;
		
		// Pages list
		$pagesList = ['general', 'results', 'srvopts', 'gameinfos', 'chat', 'plugins-list', 'guestban'];
		$pagesList = array_merge($pagesList, array_keys(ExtensionConfig::$MAPSMENU) );
		$firstPage = array_shift($pagesList);
		
		// Render page
		if (in_array(USER_PAGE, $pagesList)) {
			$pageKey = array_search(USER_PAGE, $pagesList);
			if (AdminServAdminLevel::hasAccess($pagesList[$pageKey])) {
				self::renderPage($pagesList[$pageKey]);
			}
			else {
				$data = ['errorTitle' => Utils::t('Page access error'), 'errorMessage' => Utils::t('You do not have the required permissions to access this page. Please contact your administrator.')];
				self::renderPage('page-error');
			}
		}
		else {
			if (self::isPageType('config')) {
				session_unset();
				session_destroy();
				Utils::redirection(false, './config/');
			}
			elseif (USER_PLUGIN) {
				AdminServPlugin::renderPlugin();
			}
			else {
				self::renderPage($firstPage);
			}
		}
	}
	
	
	/**
	* Initialize a front-office page
	*/
	public static function initFrontPage(): void {
		// Render page
		if (self::isPageType('config')) {
			$pageTitle = 'Configuration';
			$pageName = USER_PAGE;
		}
		else {
			$pageTitle = 'Connection';
			$pageName = 'connection';
		}
		$GLOBALS['page_title'] = Utils::t($pageTitle);
		self::renderPage($pageName);
	}
	
	
	/**
	* Include the files for rendering a page
	*/
	public static function renderPage(string $pageName): void {
		global $client, $data, $args;
		
		// Preprocess
		if (strstr($pageName, '-')) {
			$pageNameEx = explode('-', $pageName);
			$pagePreprocess = AdminServConfig::$PATH_RESOURCES.'process/'.$pageNameEx[0].'.preprocess.php';
			if (file_exists($pagePreprocess)) {
				require_once $pagePreprocess;
			}
		}
		
		// Process
		$pageProcess = AdminServConfig::$PATH_RESOURCES.'process/'.$pageName.'.php';
		if (file_exists($pageProcess)) {
			require_once $pageProcess;
		}
		
		// Terminate client
		if (isset($client) && $client->socket != null) {
			$client->Terminate();
		}
		
		self::getHeader();
		
		self::getTemplate($pageName);
		
		self::getFooter();
		
		AdminServLogs::add('access', $GLOBALS['page_title'] ?? $pageName);
	}
	
	
	/**
	* Check if the page is of the requested type
	*
	* @param string $pageType -> Page type to test (config, plugin)
	* @return bool
	*/
	public static function isPageType($pageType, $pageName = USER_PAGE){
		$out = false;
		
		if (strstr((string) $pageName, '-')) {
			$pageNameEx = explode('-', (string) $pageName);
			if ($pageNameEx[0] === $pageType) {
				$out = true;
			}
		}
		
		return $out;
	}
	
	
	/**
	* Include a template file
	*
	* @param string $templateName -> Template file name without extensions
	* @return template HTML or null if the template doesn't exist
	*/
	public static function getTemplate(string $templateName): void{
		global $data, $args;
		
		$tplFile = AdminServConfig::$PATH_RESOURCES.'templates/'.$templateName.'.tpl.php';
		if (file_exists($tplFile)) {
			require $tplFile;
		}
	}
}
?>
