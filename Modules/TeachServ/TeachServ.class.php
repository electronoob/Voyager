<?php

Class TeachServ {
	private static $cache = Array();
	function __loaded () {
		return Array (
			'name'    => "TeachServ",
			'version' => "0.1",
			'author'  => "TheHypnotist",
			'date'    => "2013-3-15",
			'info'    => "This module is for cloning channel traffic for staff training."
		);
	}
        function OnServerConnect ($a,$b,$c) {
                // we don't care about params
                //      public static function create_client ($nick, $ident, $host, $server, $realname)
                $server = Config::get_parameter_pair ('bindserver');
                Protocol::create_client ("TeachServ", "Teach", "teach.ukchatters.co.uk", $server, "UKChatters Training Bot");
                Protocol::send_privmsg ("TeachServ", "#monitoring", "TeachServ is reporting for duty.");
                Protocol::send_privmsg ("TeachServ", "#monitoring", chr(1)."ACTION is currently in development and relaying #ukchatters room by default.".chr(1));
		
        }
	private static function insert ($source, $destination, $message='', $target='', $event) {
		// remember joins and parts can be done on many rooms at same time.
		$rooms = explode(',', $destination);

		$room_to_relay = "#ukchatters";

		if ( 0 === array_search(strtolower($room_to_relay), array_map('strtolower', $rooms)) ) {
			// check the cache for existence of source of event - if not valid create even if it's a quit.
			if ( !isset(self::$cache[self::rename($source)]) ) {
				self::cache_add_nick ( self::rename ( $source ));
			}
			if ( ($event == 'PART') || ($event == 'QUIT') || ($event == 'KICK') ) {
				self::cache_remove_nick ( self::rename ( $source ) );
				return 1;
			}
			if ( ($event == 'JOIN')) {
				//
	                        if ( !isset(self::$cache[self::rename($source)]) ) {
					self::cache_add_nick ( self::rename ( $source ));
				} else {
					Protocol::send_join ($nick, '#monitoring');

				}
				return 1;
			}
			if ( ($event == 'PRIVMSG') ) {
				Protocol::send_privmsg (self::rename($source), "#monitoring", $message);
                        	return 1;
			}
                        if ( ($event == 'NOTICE') ) {
                                Protocol::send_notice (self::rename($source), "#monitoring", $message);
                        	return 1;
			}
		}

	}
	private static function cache_add_nick ($nick) {
               $server = Config::get_parameter_pair ('bindserver');

		//                Protocol::create_client ("TeachServ", "Teach", "teach.ukchatters.co.uk", $server, "UKChatters Training Bot");
		self::$cache[$nick] = "cloned"; // can support meta data but not written code for that yet.
		Protocol::create_client ($nick, "Teach", "teach.ukchatters.co.uk", $server, "UKChatters Training Bot");
		Protocol::send_join ($nick, '#monitoring');
	}
	private static function cache_remove_nick ($nick) {
        //public static function send_quit ($source, $message) {
                Protocol::send_part ( $nick , '#monitoring', "this training clone has left the room" );
		Protocol::send_quit ( $nick , "this training clone has left the network" );
		unset ( self::$cache[$nick] );
		return 1;
	}
	private static function rename ($source) {
		return $source . '_[TS]';
	}
	// events
	function OnPRIVMSG  ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'PRIVMSG');
		//echo "$source privmsg $destination with $message\n";
	}
	function OnNOTICE   ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'NOTICE');
		//echo "$source notice $destination with $message\n";
	}
	function OnJOIN     ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'JOIN');
		//echo "$source join $destination\n";
	}
	function OnPART     ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'PART');
		//echo "$source part $destination with $message\n";
	}
	function OnQUIT     ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'QUIT');
		//echo "$source quit $destination with $message\n";
	}
	function OnNICK     ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'NICK');
		//echo "$source nick $destination\n";
	}
	function OnKICK     ($source, $destination, $message='') {
		$message = explode (" ", $message);
		$target = $message[0];
		unset ($message[0]);
		$message = implode (" ", $message);
		self::insert ($source, $destination, $message, $target, 'KICK');
		//echo "$source kick $target from $destination with $message\n";
	}
	function OnTOPIC    ($source, $destination, $message='') {
		//echo "TODO: Topic event TeachServ\n";
	}
	function OnKILL     ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'KILL');
		//echo "$source KILL $destination with $message\n";
	}
}
