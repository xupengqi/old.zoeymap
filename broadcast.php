<?php
require_once('/include/init.php');

switch($_REQUEST['action']) {
	case 'new_broadcast':
		$nelat = $_REQUEST['nelat'];
		$nelng = $_REQUEST['nelng'];
		$swlat = $_REQUEST['swlat'];
		$swlng = $_REQUEST['swlng'];
		$title = $_REQUEST['title'];
		$text = $_REQUEST['text'];
		
		if($_REQUEST['zoom'] < 10) {
			break;
		}
		
		$zm_dal->new_broadcast($_SESSION['zm']['user']['userid'], $nelat, $nelng, $swlat, $swlng, $title, $text);
		echo mysql_error();
		
		break;
	case 'get_broadcasts':
		$broadcasts = $zm_dal->get_broadcasts($_REQUEST['lat_max'],$_REQUEST['lng_max'],$_REQUEST['lat_min'],$_REQUEST['lng_min']);
		$comma = false;
		echo '[';
		foreach($broadcasts as $bc) {
			if($comma) {
				echo ',';
			}
			else { $comma = true; }
	 		echo "[".$bc['nelat'].", ".$bc['nelng'].",".$bc['swlat'].", ".$bc['swlng'].", '".$bc['title']."', '".$bc['broadcastid']."']";
		}
		echo '];';
		break;
	case 'get_broadcast':
		echo zm_ctrl::broadcast($_REQUEST['broadcastid']);
		break;
	case 'new_post':
		$zm_dal->new_post($_SESSION['zm']['user']['user_id'], $_REQUEST['bc_id'], $_REQUEST['text']);
		break;
}
?>