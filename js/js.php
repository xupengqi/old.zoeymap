<?php
if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");

header("Content-Type: text/javascript");

switch($_REQUEST['section']) {
	case 'header':
		require_once('header.js');
		require_once('item.js');
		require_once('broadcast.js');
		require_once('satisfaction_header.js');
		break;
	case 'footer':
		require_once('satisfaction.js');
		break;
}
?>