<?php

Class HelperServ {
	static $cache = Array ();
	function __loaded () {
		return Array (
			'name'    => "HelperServ",
			'version' => "0.1",
			'author'  => "TheHypnotist",
			'date'    => "2013-2-27",
			'info'    => "This module is for official #help request management."
		);
	}
	function OnServerConnect ($a,$b,$c) {
		// we don't care about params
		// 	public static function create_client ($nick, $ident, $host, $server, $realname)
		$server = Config::get_parameter_pair ('bindserver');
		Protocol::create_client ("HelperServ", "Help", "help.ukchatters.co.uk", $server, "UKChatters Help Bot");
		Protocol::send_join ("HelperServ", "#help");
		//Protocol::send_join ("HelperServ", "#staff");
	}
	function OnJOIN     ($source, $destination, $message='') {
		if ( (strcasecmp($destination, "#help") == 0) ) {
			Protocol::send_notice ("HelperServ", $source, "Welcome to the HELP room - please type HELP for assistance.\n");
		}
	}
	// events
	function OnPRIVMSG  ($source, $destination, $message='') {
		$tok = explode (' ', $message);
		if ( (strcasecmp($destination, "#help") == 0) ) {
			// make sure we have all params needed - doesn't do sanity checking yet
			if(sizeof($tok) == 1)
				if ( (strcasecmp($tok[0], ":help") == 0) ) {
					// make sure that it's been a while.. 60 seconds at least since last report by this user
					if ( (self::cache_read ($source) + 60) > time () ) {
						Protocol::send_notice ("HelperServ", $source, "Too many subsequent help requests, please try again later.\n");
						return '';
					}

					Protocol::send_privmsg ("HelperServ", "#staff", chr(3)."8,1 *** $source requires assistance in the #help room.\n");
					Protocol::send_notice ("HelperServ", $source, "Thank you $source - A member of Staff will be with you shortly.\n");
					self::cache_update ($source);
				} else {

				}
		}
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
