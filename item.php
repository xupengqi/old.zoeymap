<?php
require_once('/include/init.php');

switch($_REQUEST['action']) {
	case 'new_location':
		$zm_dal->new_location($_SESSION['zm']['user']['userid'], $_REQUEST['lat'], $_REQUEST['lng'], $_REQUEST['zoom'], 'home');
		
		echo mysql_error();
		break;
	case 'get_locations':
		$locations = $zm_dal->get_locations($_REQUEST['lat_max'],$_REQUEST['lng_max'],$_REQUEST['lat_min'],$_REQUEST['lng_min'], 'home');
		$comma = false;
		echo '[';
		foreach($locations as $location) {
			if($comma) {
				echo ',';
			}
			else { $comma = true; }
			
			$username = 'Anonymous';
			if(!empty($location['username'])) {
				$username = $location['username'];
			}
			
	 		echo "[".$location['lat'].", ".$location['lng'].", '$username']";
		}
		echo '];';
		break;
}