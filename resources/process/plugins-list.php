<?php
	// Number of plugins
	$data['nbplugins'] = AdminServPlugin::countPlugins();
	if($data['nbplugins']['count'] === 0){
		Utils::redirection();
	}
?>