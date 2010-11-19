<?php
class zm_dal
{
	protected static $instance;
	
	public $db_link;
	
	protected function __construct()
	{
		$this->db_link = mysql_connect(ZM_DB_HOST, ZM_DB_USER, ZM_DB_PASS, true);
		mysql_select_db(ZM_DB_NAME, $this->db_link);
	}
	
	function __destruct()
	{
	}
	
	public static function instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new zm_dal();
		}
		
		return self::$instance;
	}
		
	public static function instance_new()
	{
		self::$instance = new zm_dal();
		return self::$instance;
	}
	
	public function new_user($user_options)
	{
		$columns = '';
		$values = '';
		$ip = zm_util::get_client_ip();
		$now = time();
		
		foreach($user_options as $option => $value) {
			$columns.= "$option, ";
			$values.= "'$value', ";
		}
		
		$qry = "INSERT INTO zm_user ($columns ip, datefirstlogin, datelastlogin) VALUES ($values '$ip', $now, $now)";
		
		mysql_query($qry);
	}
	
	public function get_user_by_email($email)
	{
		$qry = "SELECT * FROM zm_user WHERE email = '$email'";
		
		$r = mysql_query($qry);
		
		return mysql_fetch_assoc($r);
	}
	
	public function update_user($user)
	{
		$updates = array();
		
		foreach($user as $option => $value) {
			if($option != 'userid') {
				$updates[] = "$option = '$value'";
			}
		}
		
		$qry = "UPDATE zm_user SET ".implode(',', $updates)." WHERE userid = ".$user['userid'];
		mysql_query($qry);
	}
	
	public function new_location($userid, $lat, $lng, $zoom, $type)
	{
		$userid = $this->clean_input($userid);
		$lat = $this->clean_input($lat);
		$lng = $this->clean_input($lng);
		$now = time();
		
		$qry = "INSERT INTO zm_location (userid, lat, lng, zoom, type, dateadded) VALUES ($userid, $lat, $lng, $zoom, '$type', $now)";
		
		$this->delete_locations($userid, $type);
		mysql_query($qry);
//		echo $qry;
	}
	
	public function get_locations($lat_max, $lng_max, $lat_min, $lng_min, $type)
	{
		//Fiji is somewhere in the view
		if($lng_max < $lng_min) {
			$where = "(lat > $lat_min AND lat < $lat_max) AND (lng > $lng_min AND lng < 180) OR ";
			$where.= "(lat > $lat_min AND lat < $lat_max) AND (lng > -180 AND lng < $lng_max)";
		}
		else {
			$where = "(lat > $lat_min AND lat < $lat_max) AND (lng > $lng_min AND lng < $lng_max)";
		}
		
		$qry = "SELECT * FROM zm_location l INNER JOIN zm_user u ON l.userid = u.userid WHERE $where AND type = '$type'";
		
		$r = mysql_query($qry);
		
		return $this->fetch_array($r);
	}
	
	public function get_user_locations($userid)
	{
		$qry = "SELECT * FROM zm_location WHERE userid = $userid";
		
		return $this->fetch_array(mysql_query($qry), '', 'type');
	}
	
	public function delete_locations($userid, $type)
	{
		$qry = "DELETE FROM zm_location WHERE userid = $userid and type = '$type'";
		
		mysql_query($qry);
	}
	
	public function new_broadcast($userid, $nelat, $nelng, $swlat, $swlng, $title, $text)
	{
		$userid = $this->clean_input($userid);
		$nelat = $this->clean_input($nelat);
		$nelng = $this->clean_input($nelng);
		$swlat = $this->clean_input($swlat);
		$swlng = $this->clean_input($swlng);
		$title = $this->clean_input($title);
		$now = time();
		
		$qry = "INSERT INTO zm_broadcast (userid, nelat, nelng, swlat, swlng, title, text, dateposted) VALUES ($userid, $nelat, $nelng, $swlat, $swlng, '$title', '$text', $now)";
		
		mysql_query($qry);
//		echo $qry;
//		$broadcast = $this->get_last_user_broadcast($userid);
//		$this->new_post($userid, $bc['broadcast_id'], $text);
	}
	
	public function get_broadcasts($nelat, $nelng, $swlat, $swlng)
	{
		$qry = "SELECT * FROM zm_broadcast WHERE ";
		$qry.= "((swlat <= $nelat AND swlng <= $nelng) AND (swlat >= $swlat AND swlng >= $swlng)) OR "; //sw corner
		$qry.= "((nelat >= $swlat AND nelng >= $swlng) AND (nelat <= $nelat AND nelng <= $nelng)) OR "; //ne corner
		
		$qry.= "((swlat <= $nelat AND nelng >= $swlng) AND (swlat >= $swlat AND nelng <= $nelng)) OR "; //se corner
		$qry.= "((nelat >= $swlat AND swlng <= $nelng) AND (nelat <= $nelat AND swlng >= $swlng)) OR "; //nw corner
		
		$qry.= "((swlat <= $swlat AND swlng <= $swlng) AND (nelat >= $nelat AND nelng >= $nelng))"; // view port within the broadcast
		
		$r = mysql_query($qry);
		
		return $this->fetch_array($r);
	}
	
	public function get_broadcast($broadcastid)
	{
		$qry = "SELECT * FROM zm_broadcast WHERE broadcastid = $broadcastid";
		
		return mysql_fetch_assoc(mysql_query($qry));
	}
	
//	public function get_last_user_broadcast($userid)
//	{
//		$qry = "SELECT * FROM starcraft_broadcast WHERE user_id = $userid ORDER BY broadcast_id DESC LIMIT 1";
//		
//		return mysql_fetch_assoc(mysql_query($qry));
//	}
	
	public function get_posts($broadcastid)
	{
		$qry = "SELECT * FROM zm_post p INNER JOIN zm_user u on p.userid = u.userid WHERE broadcastid = $broadcastid ORDER BY postid ASC";
		
		return $this->fetch_array(mysql_query($qry));
	}
	
	public function new_post($userid, $broadcastid, $text)
	{
		$userid = $this->clean_input($userid);
		$broadcastid = $this->clean_input($broadcastid);
		$text = $this->clean_input($text);
		$now = time();
		
		$qry = "INSERT INTO zm_post (userid, broadcastid, text, dateposted) VALUES ($userid, $broadcastid, '$text', $now)";
		
		mysql_query($qry);
		echo $qry;
	}
	
	public function clean_input($input)
	{
		return mysql_real_escape_string(trim($input));
	}
	
	public function fetch_array($resource, $filter = '', $key = '')
	{
		$result = array();
		
		if(!$resource) {
			return $result;
		}
		
		while($row = mysql_fetch_assoc($resource)) {
			if(!empty($filter)) {
				if(!empty($key)) {
					$result[$row[$key]] = $row[$filter];
				}
				else {
					$result[] = $row[$filter];
				}
			}
			else {
				if(!empty($key)) {
					$result[$row[$key]] = $row;
				}
				else {
					$result[] = $row;
				}
			}
		}
		return $result;
	}
}