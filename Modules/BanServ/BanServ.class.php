<?php

Class StaffServ {
	static $cache = Array ();
	function __loaded () {
		return Array (
			'name'    => "StaffServ",
			'version' => "0.1",
			'author'  => "TheHypnotist",
			'date'    => "2012-2-28",
			'info'    => "This module is for official Staff management."
		);
	}
	function OnServerConnect ($a,$b,$c) {
		// we don't care about params
		// 	public static function create_client ($nick, $ident, $host, $server, $realname)
		$server = Config::get_parameter_pair ('bindserver');
		Protocol::create_client ("StaffServ", "Staff", "staff.ukchatters.co.uk", $server, "UKChatters Staff Facility");
		Protocol::send_join ("StaffServ", "#staff");
		Protocol::send_privmsg ("StaffServ", "#staff", "StaffServ is reporting for duty.");
	}
	// events
	function OnPRIVMSG  ($source, $destination, $message='') {
		
	}
	
	private static function cache_update ($source) {
		self::$cache[$source] = time ();
	}
	private static function cache_read ($source) {
		if (array_key_exists($source, self::$cache)) {
			return self::$cache[$source];
		} else {
			return 0;
		}
	}
}
