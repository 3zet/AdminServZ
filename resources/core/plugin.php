<?php

/**
* Class for plugin management
*/
class AdminServPlugin {
	
	/**
	* Try to get a plugin config from another file
	*/
	public static function setPluginsList(): void{
		$otherPluginsList = AdminServConfig::PLUGINS_LIST;
		
		if($otherPluginsList){
			if( file_exists($otherPluginsList) ){
				include_once $otherPluginsList;
				
				if( isset($PLUGINS) ){
					if(AdminServConfig::PLUGINS_LIST_TYPE == 'add'){
						ExtensionConfig::$PLUGINS = array_merge(ExtensionConfig::$PLUGINS, $PLUGINS);
					}
					else{
						ExtensionConfig::$PLUGINS = $PLUGINS;
					}
				}
				else{
					AdminServ::error( Utils::t('Variable "$PLUGINS" not found.') );
				}
			}
			else{
				AdminServ::error( Utils::t('Cannot include another plugins config file.') );
			}
		}
	}
	
	
	/**
	* Determine if at least one plugin is available
	*
	* @param string $pluginName -> Test a specific plugin
	* @return bool
	*/
	public static function hasPlugin($pluginName = null){
		$out = false;
		
		if( class_exists('ExtensionConfig') ){
			if( isset(ExtensionConfig::$PLUGINS) && count(ExtensionConfig::$PLUGINS) > 0 ){
				foreach(ExtensionConfig::$PLUGINS as $plugin){
					$pluginConfig = self::getConfig($plugin);
					if( ($pluginConfig['game'] == SERVER_VERSION_NAME || $pluginConfig['game'] == 'all') && AdminServAdminLevel::isType($pluginConfig['adminlevel']) ){
						if($pluginName){
							if($pluginName == $plugin){
								$out = true;
							}
						}
						else{
							$out = true;
						}
					}
				}
			}
		}
		
		return $out;
	}
	
	
	/**
	* Get the current plugin
	*
	* @return pluginName
	*/
	public static function getCurrent(): ?string{
		$out = null;
		
		if( defined('USER_PAGE') ){
			$pageEx = explode('-', (string) USER_PAGE);
			if( count($pageEx) > 0 && isset($pageEx[0]) && $pageEx[0] == 'plugins' ){
				if( isset($pageEx[1]) && $pageEx[1] != 'list' ){
					$out = $pageEx[1];
				}
			}
		}
		
		return $out;
	}
	
	
	/**
	* Get the plugin config from the config.ini file
	*
	* @param string $pluginName  -> The plugin folder name
	* @param string $returnField -> Return a specific field
	* @return array or string if the 2nd parameter is specified
	*/
	public static function getConfig($pluginName = null, $returnField = null){
		$out = null;
		if($pluginName === null){
			$pluginName = USER_PLUGIN;
		}
		$path = AdminServConfig::$PATH_PLUGINS .$pluginName.'/config.ini';
		
		if( file_exists($path) ){
			$ini = parse_ini_file($path);
			if($returnField && isset($ini[$returnField]) ){
				$out = $ini[$returnField];
			}
			else{
				$out = $ini;
			}
		}
		
		return $out;
	}
	
	
	/**
	* Return an html list for the plugins menu
	*
	* @return html
	*/
	public static function getMenuList(){
		$out = null;
		$pluginsList = [];
		if( count(ExtensionConfig::$PLUGINS) > 0 ){
			foreach(ExtensionConfig::$PLUGINS as $plugin){
				$pluginInfos = self::getConfig($plugin);
				if( ($pluginInfos['game'] == 'all' || $pluginInfos['game'] == SERVER_VERSION_NAME) && AdminServAdminLevel::isType($pluginInfos['adminlevel']) ){
					$pluginsList[$plugin] = $pluginInfos;
				}
			}
		}
		
		if( count($pluginsList) > 0 ){
			$out = '<nav class="vertical-nav">'
				.'<ul>';
					foreach($pluginsList as $plugin => $infos){
						$out .= '<li><a '; if(self::getCurrent() == $plugin){ $out .= 'class="active" '; } $out .= 'href="?p=plugins-'.$plugin.'" title="'.Utils::t('Version').' : '.$infos['version'].'">'.$infos['name'].'</a></li>';
					}
				$out .= '</ul>'
			.'</nav>';
		}
		
		return $out;
	}
	
	
	/**
     * Count the number of installed plugins
     */
    public static function countPlugins(): array{
		$out = [];
		$pluginsList = [];
		if( self::hasPlugin() ){
			foreach(ExtensionConfig::$PLUGINS as $plugin){
				$pluginInfos = self::getConfig($plugin);
				if($pluginInfos['game'] == 'all' || $pluginInfos['game'] == SERVER_VERSION_NAME){
					$pluginsList[] = $plugin;
				}
			}
		}
		
		$out['count'] = count($pluginsList);
		if($out['count'] > 1){
			$out['title'] = Utils::t('plugins installed');
		}
		else{
			$out['title'] = Utils::t('plugin installed');
		}
		
		return $out;
	}
	
	
	/**
     * Include the files for rendering a plugin
     *
     * @param string $pluginName -> The plugin folder name
     */
    public static function renderPlugin($pluginName = null): void {
		global $client, $translate, $args;
		if($pluginName === null){
			$pluginName = USER_PLUGIN;
		}
		
		// Try to get plugins from another config
		self::setPluginsList();
		
		// Build the plugin render
		$pluginPath = AdminServConfig::$PATH_PLUGINS .$pluginName.'/';
		$scriptFile = $pluginPath.'script.php';
		$viewFile = $pluginPath.'view.php';
		if (file_exists($scriptFile) && file_exists($viewFile)) {
			// Process
			require_once $scriptFile;
			
			// Terminate client
			if (isset($client) && $client->socket != null) {
				$client->Terminate();
			}
			
			AdminServUI::getHeader();
			
			// Content
			echo '<section class="plugins hasMenu">'
				.'<section class="cadre left menu">'
					.self::getMenuList()
				.'</section>'
				
				.'<section class="cadre right">'
					.'<h1>'.self::getConfig($pluginName, 'name').'</h1>';
					require_once $viewFile;
				echo '</section>'
			.'</section>';
			
			AdminServUI::getFooter();
			
			AdminServLogs::add('access', 'Plugin: '.$pluginName);
		}
		else {
			AdminServ::error( Utils::t('Plugin error: script.php or view.php file is missing.') );
			AdminServUI::getHeader();
			AdminServUI::getFooter();
		}
	}
	
	
	/**
	* Get the plugin folder path
	*
	* @param string $pluginName -> The plugin folder name
	* @return string
	*/
	public static function getPluginPath($pluginName = null){
		$out = null;
		if($pluginName === null){
			$pluginName = USER_PLUGIN;
		}
		$path = AdminServConfig::$PATH_PLUGINS;
		
		if($path && $pluginName){
			$out = $path.$pluginName.'/';
		}
		
		return $out;
	}
}
?>