<?php
class zm_util
{
	public static function get_client_ip()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
		{
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
		{
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$ip=$_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	public static function get_date_ago($timestamp)
	{
		date_default_timezone_set('America/Los_Angeles');

		$result = '';
		$interval = time() - $timestamp;
		$days = floor($interval / 86400);
		$hours = floor($interval / 3600);
		$minutes = floor(($interval % 3600) / 60);
		$seconds = $interval % 60;
		
		if($days > 1) {
			$result = "$days days ago";
			return $result;
		}
		else if ($days > 0) {
			$result = "$days day ago";
			return $result;
		}
		
		if($hours > 1) {
			$result = "$hours hours ago";
			return $result;
		}
		else if ($hours > 0) {
			$result = "$hours hour ago";
			return $result;
		}
	
		if($minutes > 1) {
			$result.= "$minutes minutes ago";
			return $result;
		}
		else if ($minutes > 0) {
			$result.= "$minutes minute ago";
			return $result;
		}
	
		if($seconds > 1) {
			$result.= "$seconds seconds ago";
			return $result;
		}
		else if ($seconds > 0) {
			$result.= "$seconds second ago";
			return $result;
		}
		
		return $result;
	}

	public static function login($user)
	{
		global $zm_config;
		
		$_SESSION['zm'] = array();
		$_SESSION['zm']['user'] = $user;
		
		header('Location: '.$zm_config['url']['home']);
		exit;
	}
	
	public static function logout()
	{
		global $zm_config;
		
		setcookie('fbs_'.$zm_config['facebook']['appid'], '', time()-1, '/', $zm_config['domain']);
		unset($_SESSION['zm']);
		
		header('Location: '.$zm_config['url']['home']);
		exit;
	}
	
	public static function is_loggedin()
	{
		return isset($_SESSION['zm']['user']);
	}
}