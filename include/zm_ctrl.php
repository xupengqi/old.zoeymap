<?php
class zm_ctrl
{
	public static function form($id, $title, $position, $content, $show = true, $class = '')
	{
		$t_vars = array();
		$t_vars['[[id]]'] = $id;
		$t_vars['[[title]]'] = $title;
		$t_vars['[[position]]'] = self::position($position);
		$t_vars['[[content]]'] = $content;;
		$t_vars['[[display]]'] = self::display($show);
		$t_vars['[[class]]'] = $class;
		
		return self::template('zm_ctrl_form.html', $t_vars);
	}
	
	public static function broadcast($broadcastid)
	{
		global $zm_dal;
		
		$broadcast = $zm_dal->get_broadcast($broadcastid);
		$broadcast_posts = $zm_dal->get_posts($broadcastid);
		
		$posts = self::post($broadcast, true);;
		
		foreach($broadcast_posts as $post) {
			$posts.= self::post($post);
		}
		
		$t_vars['[[posts]]'] = $posts;
		$t_vars['[[save]]'] = '<button type="button" name="new_post" id="new_post" onclick="return new_post('.$broadcastid.');">Save</button>';
		
		if(!isset($_SESSION['zm']['user'])) {
			$t_vars['[[save]]'] = 'Please <a href="' . zm_login_facebook::$facebook->getLoginUrl(array("req_perms" => "publish_stream, email")) . '" style="font-weight: bold;">Login</a> reply to this broadcast.';
		}
				
		
		return self::template('zm_ctrl_broadcast.html', $t_vars);
	}
	
	public static function post($post, $is_op = false)
	{
		$t_vars['[[id]]'] = '';
		if($is_op) {
			$t_vars['[[id]]'] = 'id="bc_op"';
		}
		
		$t_vars['[[username]]'] = $post['userid'];
		$t_vars['[[date_posted]]'] = zm_util::get_date_ago($post['dateposted']);
		$t_vars['[[text]]'] = $post['text'];
		
		return self::template('zm_ctrl_post.html', $t_vars);
	}
	
	public static function new_broadcast()
	{
		$id = 'add_broadcast';
		$title = 'New Broadcast';
		$content = self::template('zm_ctrl_new_broadcast.html', array());
		
		return self::form($id, $title, 'bottom right', $content, false, 'solo');
	}
	
	public static function new_location()
	{
		$id = 'add_location';
		$title = 'New Location';
		$content = self::template('zm_ctrl_new_location.html', array());
		
		return self::form($id, $title, 'bottom right', $content, false, 'solo');
	}
	
	public static function profile_settings()
	{
		$t_vars['[[]]'] = '';
	
		return self::template('zm_profile_settings.html', $t_vars);
	}
	
	public static function home()
	{
		global $zm_dal, $zm_config;
		
		$t_vars['[[menu]]'] = self::menu();
		$t_vars['[[google_login]]'] = '';
		
		if(zm_util::is_loggedin()) {
			$t_vars['[[zm_user]]'] = "zm_user.default_view = '".$_SESSION['zm']['user']['default_view']."';";
			
			$user_locations = $zm_dal->get_user_locations($_SESSION['zm']['user']['userid']);
			if(isset($user_locations['home'])) {
				$t_vars['[[zm_user]]'].= "zm_user.home = new google.maps.LatLng(".$user_locations['home']['lat'].", ".$user_locations['home']['lng'].");";
				$t_vars['[[zm_user]]'].= "zm_user.home_zoom = ".$user_locations['home']['zoom'].";";
			}
			if(isset($user_locations['default_view'])) {
				$t_vars['[[zm_user]]'].= "zm_user.default_location = new google.maps.LatLng(".$user_locations['default_view']['lat'].", ".$user_locations['default_view']['lng'].");";
				$t_vars['[[zm_user]]'].= "zm_user.default_zoom = ".$user_locations['default_view']['zoom'].";";
			}
			
			$t_vars['[[new_location]]'] = self::new_location();
			$t_vars['[[new_broadcast]]'] = self::new_broadcast();
		}
		else {
			$t_vars['[[new_location]]'] = self::login_required('add_location', 'New Location');
			$t_vars['[[new_broadcast]]'] = self::login_required('add_broadcast', 'New Broadcast');
			
			$t_vars_google['[[redirect_to]]'] = '';
			if(isset($_SESSION['redirect_to'])) {
				$t_vars_google['[[redirect_to]]'] =  $_SESSION['redirect_to'];
			}
			
			$openid_params = zm_login_google::get_openid_params();
			$openid_ext = zm_login_google::get_openid_ext();
			$t_vars_google['[[extensions]]'] = json_encode($openid_ext);
			$t_vars_google['[[realm]]'] = $openid_params['openid.realm'];
			$t_vars_google['[[return_to]]'] = $openid_params['openid.return_to'] . '?popup=true';
			$t_vars['[[google_login]]'] = self::template('zm_ctrl_google_login.html', $t_vars_google);
		}
		
		return self::template('zm_index.html', $t_vars);
	}
	
	public static function login_required($id, $title)
	{
		$t_vars['[[login]]'] = self::menu_login();
		$content = self::template('zm_ctrl_login_required.html', $t_vars);
		
		return self::form($id, $title, 'bottom right', $content, false, 'solo');
	}
	
	public static function menu()
	{
		$id = 'menu';
		$content = '';
		
		if(isset($_SESSION['zm']['user'])) {
			$title = self::menu_logout();
			$content.= self::menu_profile();
			$content.= self::menu_my_location();
		}
		else {
			$title = self::menu_login();
			
			$content = self::menu_my_location();
		}
		
		return self::form($id, $title, 'top right', $content);
	}
	
	public static function menu_login()
	{
		$t_vars['[[google_login]]'] = $_SERVER['PHP_SELF'] . '?openid_mode=checkid_setup&openid_identifier=google.com/accounts/o8/id';
		$t_vars['[[fb_login]]'] = zm_login_facebook::$facebook->getLoginUrl(array("req_perms" => "publish_stream, email"));
		return self::template('zm_ctrl_login.html', $t_vars);
	}
	
	public static function menu_logout()
	{
		global $zm_config;
		
		$t_vars['[[username]]'] = '?';
		//$t_vars['[[user_image_url]]'] = 'https://graph.facebook.com/'.$user_fb['id'].'/picture';
		//$t_vars['[[user_url]]'] = $user_fb['link'];
		$t_vars['[[logout_url]]'] = $zm_config['url']['home'].'index.php?logout=1';
		return self::template('zm_ctrl_menu_logout.html', $t_vars);
	}
	
	public static function menu_my_location()
	{
		return self::template('zm_ctrl_menu_my_location.html');
	}
	
	public static function menu_profile()
	{
		return self::template('zm_ctrl_menu_profile.html');
	}
	
	
	
	public static function display($display)
	{
		if($display) {
			return '';
		}
		else {
			return 'display: none;';
		}
	}
	
	protected static function position($position)
	{
		switch($position) {
			case 'bottom right':
				return 'bottom: 5px; right: 5px;';
				break;
			case 'top right':
				return 'top: 5px; right: 5px;';
				break;
		}
	}

	protected static function template($path, $vars = array())
	{
		global $appConfig, $zm_config;
		
		$search = array_keys($vars);
		$replace = array_values($vars);
		$template = file_get_contents($zm_config['url']['home'].'template/'.$path);
		$result = str_replace($search, $replace, $template);
		return $result;
	}
}
?>