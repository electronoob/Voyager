<?php

Class ReportServ {
	static $cache = Array ();
	function __loaded () {
		return Array (
			'name'    => "ReportServ",
			'version' => "0.2",
			'author'  => "TheHypnotist",
			'date'    => "2012-2-27",
			'info'    => "This module is for official complaints management."
		);
	}
	function OnServerConnect ($a,$b,$c) {
		// we don't care about params
		// 	public static function create_client ($nick, $ident, $host, $server, $realname)
		$server = Config::get_parameter_pair ('bindserver');
		Protocol::create_client ("ReportServ", "Report", "report.ukchatters.co.uk", $server, "UKChatters Report Bot");
		//Protocol::send_join ("ReportServ", "#staff");
		Protocol::send_privmsg ("ReportServ", "#staff", "ReportServ is reporting for duty.");
	}
	// events
	function OnPRIVMSG  ($source, $destination, $message='') {
		$tok = explode (' ', $message);
		if ( (strcasecmp($destination, "ReportServ") == 0) ) {
			// make sure we have all params needed - doesn't do sanity checking yet
			if(sizeof($tok) == 3)
				if ( (strcasecmp($tok[0], ":report") == 0) ) {
					// make sure that it's been a while.. 60 seconds at least since last report by this user
					if ( (self::cache_read ($source) + 60) > time () ) {
						Protocol::send_privmsg ("ReportServ", $source, "Too many subsequent report requests, please try again later.\n");
						return '';
					}

//					Protocol::send_notice ("ReportServ", "#staff", "*** Alert Staff Abuse Report Received ***\n");
					Protocol::send_privmsg ("ReportServ", "#staff", chr(3)."8,1 *** $source reports trouble in ".$tok[2]." caused by ".$tok[1]."\n");
					Protocol::send_privmsg ("ReportServ", $source, "Thank you for your report, if a member of staff is available they will join the room to stop any further rule breaking, but cannot check previous activity\n");
					self::cache_update ($source);
				} else {
					Protocol::send_privmsg ("ReportServ", $source, "Report Syntax: /msg ReportServ report nickname #room\n");
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
