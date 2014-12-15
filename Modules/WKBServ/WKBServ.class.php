<?php

Class WKBServ {
	static $cache = Array ();
	function __loaded () {
		return Array (
			'name'    => "WKBServ",
			'version' => "0.1",
			'author'  => "Neo",
			'date'    => "2013-5-10",
			'info'    => "This module is for official logging moderator actions."
		);
	}
	function OnServerConnect ($a,$b,$c) {
		// we don't care about params
		// 	public static function create_client ($nick, $ident, $host, $server, $realname)
		$server = Config::get_parameter_pair ('bindserver');
		Protocol::create_client ("WKBServ", "WKB", "wkb.ukchatters.co.uk", $server, "UKChatters WKB Bot");
		Protocol::send_join ("WKBServ", "#services");
		Protocol::send_privmsg ("WKBServ", "#services", "WKBServ is reporting for duty.");
	}
	// events
         function OnKICK     ($source, $destination, $message='') {
			$message = explode (" ", $message);
			$target = $message[0];
			unset ($message[0]);
			$message = implode (" ", $message);
			Protocol::send_privmsg ("WKBServ", "#services", "$source kicked $target from $destination for $message\n");
	}
}
