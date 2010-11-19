<?php

$zm_config = array();
$zm_config['domain'] = '.zoeymap.com';
$zm_config['url']['home'] = 'http://www'.$zm_config['domain'].'/r/';
$zm_config['google']['consumer_key'] = 'www.zoeymap.com';
$zm_config['google']['consumer_secret'] = 'vgc0B+KaduiogulIMjggFMaw';
$zm_config['facebook']['appid'] = '154316887933772';
$zm_config['facebook']['secret'] = 'c7b762e5450ea54910c8a468f1480042';


date_default_timezone_set('America/Los_Angeles');
session_start();

require_once("zm_config.php");
require_once("zm_util.php");
require_once("zm_dal.php");
require_once("zm_ctrl.php");

$zm_dal = zm_dal::instance();

if(isset($_REQUEST['logout'])) {
	zm_util::logout();
}

//if(isset($_GET['session'])) {
//	header('Location: /r/');
//}

if(!zm_util::is_loggedin()) {
	require_once('zm_login_google.php');
	zm_login_google::init();
	zm_login_google::try_login();
	switch(@$_REQUEST['openid_mode']) {
	  case 'cancel':
	//    debug('Sign-in was cancelled.');
	    break;
	}
			
	
	
	require_once('zm_login_facebook.php');
	zm_login_facebook::init();
	zm_login_facebook::try_login();

}
?>