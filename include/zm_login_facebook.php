<?php
require_once('/ext/facebook/src/facebook.php');

class zm_login_facebook
{
	public static $facebook;
	
	public static function init()
	{
		global $zm_config;
		
		Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
		Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;
		
		self::$facebook = new Facebook(array(
		  'appId'  => $zm_config['facebook']['appid'],
		  'secret' => $zm_config['facebook']['secret'],
		  'cookie' => true,
		));
	}
	
	public static function try_login()
	{
		global $zm_dal;
		
		if (self::$facebook->getUser()) {
			try {
				$user_fb = self::$facebook->api('/me');
				$user = $zm_dal->get_user_by_email($user_fb['email']);
				if(!$user) {
					$user = array('email' => $user_fb['email'], 'facebook' => serialize($user_fb));
					$zm_dal->new_user($user);
					$user = $zm_dal->get_user_by_email($user_fb['email']);
				}
				if(empty($user['facebook'])) {
					$user['facebook'] = serialize($user_fb);
					$zm_dal->update_user($user);
				}
				zm_util::login($user);
			} catch (FacebookApiException $e) {
			}
		}
	}
}