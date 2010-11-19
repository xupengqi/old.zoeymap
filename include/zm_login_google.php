<?php
require_once('/ext/google/OAuth.php');
	
class zm_login_google
{
	public static function init()
	{
		global $zm_config;
		
		$consumer = new OAuthConsumer($zm_config['google']['consumer_key'], $zm_config['google']['consumer_secret']);
	
		if (isset($_REQUEST['popup']) && !isset($_SESSION['redirect_to'])) {
			$query_params = '';
			if($_POST) {
				$kv = array();
				foreach ($_POST as $key => $value) {
					$kv[] = "$key=$value";
				}
				$query_params = join('&', $kv);
			}
			else {
				$query_params = substr($_SERVER['QUERY_STRING'], strlen('popup=true') + 1);
			}
			
			$_SESSION['redirect_to'] = "http://{$zm_config['google']['consumer_key']}{$_SERVER['PHP_SELF']}?{$query_params}";
			echo '<script type = "text/javascript">window.close();</script>';
			exit;
		}
		else if (isset($_SESSION['redirect_to'])) {
			$redirect = $_SESSION['redirect_to'];
			unset($_SESSION['redirect_to']);
			header('Location: ' .$redirect);
		}
	}
	
	public static function try_login()
	{
		global $zm_dal;
		
		if(isset($_REQUEST['openid_mode']) && $_REQUEST['openid_mode'] === 'id_res') {
			$user_google = array();
			$user_google['firstname'] = $_REQUEST['openid_ext1_value_first'];
			$user_google['lastname'] = $_REQUEST['openid_ext1_value_last'];
			$user_google['country'] = $_REQUEST['openid_ext1_value_country'];
			$user_google['lang'] = $_REQUEST['openid_ext1_value_lang'];
			$user = $zm_dal->get_user_by_email($_REQUEST['openid_ext1_value_email']);
			if(!$user) {
				$user = array('email' => $_REQUEST['openid_ext1_value_email'], 'google' => serialize($user_google));
				$zm_dal->new_user($user);
				$user = $zm_dal->get_user_by_email($_REQUEST['openid_ext1_value_email']);
			}
			if(empty($user['google'])) {
				$user['google'] = serialize($user_google);
				$zm_dal->update_user($user);
			}
			zm_util::login($user);
		}
	}
	
	public static function get_openid_params()
	{
		global $zm_config;
		return array(
			'openid.ns'                => 'http://specs.openid.net/auth/2.0',
			'openid.claimed_id'        => 'http://specs.openid.net/auth/2.0/identifier_select',
			'openid.identity'          => 'http://specs.openid.net/auth/2.0/identifier_select',
			'openid.return_to'         => "http://{$zm_config['google']['consumer_key']}{$_SERVER['PHP_SELF']}",
			'openid.realm'             => "http://{$zm_config['google']['consumer_key']}",
			'openid.mode'              => @$_REQUEST['openid_mode'],
			'openid.ns.ui'             => 'http://specs.openid.net/extensions/ui/1.0',
			'openid.ns.ext1'           => 'http://openid.net/srv/ax/1.0',
			'openid.ext1.mode'         => 'fetch_request',
			'openid.ext1.type.email'   => 'http://axschema.org/contact/email',
			'openid.ext1.type.first'   => 'http://axschema.org/namePerson/first',
			'openid.ext1.type.last'    => 'http://axschema.org/namePerson/last',
			'openid.ext1.type.country' => 'http://axschema.org/contact/country/home',
			'openid.ext1.type.lang'    => 'http://axschema.org/pref/language',
			'openid.ext1.required'     => 'email,first,last,country,lang',
			'openid.ns.oauth'          => 'http://specs.openid.net/extensions/oauth/1.0',
			'openid.oauth.consumer'    => $zm_config['google']['consumer_key'],
			'openid.oauth.scope'       => ''
		);
	} 
		
	public static function get_openid_ext()
	{
		global $zm_config;
		return array(
			'openid.ns.ext1'           => 'http://openid.net/srv/ax/1.0',
			'openid.ext1.mode'         => 'fetch_request',
			'openid.ext1.type.email'   => 'http://axschema.org/contact/email',
			'openid.ext1.type.first'   => 'http://axschema.org/namePerson/first',
			'openid.ext1.type.last'    => 'http://axschema.org/namePerson/last',
			'openid.ext1.type.country' => 'http://axschema.org/contact/country/home',
			'openid.ext1.type.lang'    => 'http://axschema.org/pref/language',
			'openid.ext1.required'     => 'email,first,last,country,lang',
			'openid.ns.oauth'          => 'http://specs.openid.net/extensions/oauth/1.0',
			'openid.oauth.consumer'    => $zm_config['google']['consumer_key'],
			'openid.oauth.scope'       => '',
			'openid.ui.icon'           => 'true'
		);
	}

}