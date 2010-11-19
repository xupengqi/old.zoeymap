<?php
require_once('/include/init.php');

if(isset($_REQUEST['iframe'])) {
	echo zm_ctrl::profile_settings();
	exit;
}

if(isset($_REQUEST['action'])) {
	switch($_REQUEST['action']) {
		case 'save':
			$_SESSION['zm']['user']['default_view'] = $_REQUEST['view'];
			$zm_dal->update_user($$_SESSION['zm']['user']);
			
			if($_SESSION['zm']['user']['default_view'] == 'current') {
				$zm_dal->new_location($_SESSION['zm']['user']['userid'], $_REQUEST['lat'], $_REQUEST['lng'], $_REQUEST['zoom'], 'default_view');
			}
			break;
	}
	exit;
}
?>