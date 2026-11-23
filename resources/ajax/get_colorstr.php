<?php
	// JSON endpoint: PHP notices must never reach the response body (they still go to the error log)
	ini_set('display_errors', '0');
	// INCLUDES
	require_once '../class/tmnick.class.php';
	
	// ISSET
	if( isset($_GET['t']) ){ $text = stripslashes((string) $_GET['t']); }else{ $text = null; }
	
	// DATA
	$out = null;
	if($text != null){
		$out['str'] = TmNick::toHtml( nl2br($text), 10, false, false, '#666');
	}
	
	// OUT
	echo json_encode($out);
?>