<?php
// needed to access pcntl_signal
declare(ticks = 1);

Class NameServ {
	private static $passwords = array();
	// cache is used for tracking who triggered the identify event and to deal with flooding
	private static $cache = Array ();
	// users and meta data will be found in this particular array
	private static $users = Array ();

	function __loaded () {
		return Array (
			'name'    => "NameServ",
			'version' => "0.2",
			'author'  => "TheHypnotist",
			'date'    => "2012-2-28",
			'info'    => "This module is for official nickname management."
		);
	}
	public function OnPulse () {
		// The idea of OnPulse will be to enable queued events based on how many seconds have passed by etc.
		// due to this being an Ariel event Pulse is mostly lowercase to differentiate it from an IRC event of all caps.
	}
	private static function in_password_cache ($source) {
		foreach( self::$passwords as $db_nick => $db_pass) {
			if ( (strcasecmp($db_nick, $source) == 0) ) {
				return 1;
			}
		}
		return 0;
	}
	private static function add_user_cache ($nick, $host, $server) {
		// n == nickname of user
		// h == ip or hostname of user unmasked of course
		// s == server they are connected to
		// r == array of rooms they are in
		// i == bool flag - identified to name server.. true or false :)
		self::$users[] = Array ('n'=>$nick, 'h'=>$host, 's'=>$server, 'r' => Array(), 'i' => 0);
		return 1;
	}
	private static function del_user_cache ($nick) {
                foreach (self::$users as $index => $user) {
                        if ( (strcasecmp($user['n'], $nick) == 0) ) {
				unset(self::$users[$index]);
                                return 1;
                        }
                }

	}
	private static function chgnick_user_cache ($nick, $nick2 ) {
                foreach (self::$users as $index => $user) {
                        if ( (strcasecmp($user['n'], $nick) == 0) ) {
                                self::$users[$index]['n'] = $nick2;
				//no longer identified - sorry :)
				self::$users[$index]['i'] = 0;
				return 1;
                        }
                }

	}
        private static function identify_user_cache ($nick ) {
                foreach (self::$users as $index => $user) {
                        if ( (strcasecmp($user['n'], $nick) == 0) ) {
                                self::$users[$index]['i'] = 1;
                                return 1;
                        }
                }

        }
	private static function is_user_identified ($nick) {
		$userdata = self::get_user_cache ($nick);
		return $userdata['i'];
	}
	private static function get_user_cache ($nick) {
		foreach (self::$users as $index => $user) {
			if ( (strcasecmp($user['n'], $nick) == 0) ) {
				return $user;
			}
		}
		return null;
	}
	private static function add_room_user_cache ($nick, $channels) {
		$user = self::get_user_cache ($nick);
		$chanlist = explode(',', $channels);
                foreach (self::$users as $index => $user) {
                        if ( (strcasecmp($user['n'], $nick) == 0) ) {
				foreach ($chanlist as $chanitem) {
					self::$users[$index]['r'][] = $chanitem;
				}
				return 1;
                        }
                }
		return 0;
	}
        private static function del_room_user_cache ($nick, $channels) {
                $user = self::get_user_cache ($nick);
                $chanlist = explode(',', $channels);

                foreach (self::$users as $index => $user) {
                        if ( (strcasecmp($user['n'], $nick) == 0) ) {
				foreach ($user['r'] as $room_index => $room) {
	                                foreach ($chanlist as $chanitem) {
						if ( (strcasecmp($room, $chanitem) == 0) ) {
							unset(self::$users[$index]['r'][$room_index]);
						}
                	                }
				}
                                return 1;
                        }
                }
		return 0;
        }
	private static function dump_user_cache( $target, $mode = 'all') {
		$total = 0;
		foreach (self::$users as $index => $user) {
			$user_rooms = implode(',', $user['r']);
			if ($mode == 'all') {
				Protocol::send_notice (
					"NameServ",
					$target,
					"User $index # $user[n] |$user[i]| $user[h] $user[s] in rooms: $user_rooms");
			}
                        if ($mode == 'identified') {
				if ($user['i'])
                                Protocol::send_notice (
                                        "NameServ",
                                        $target,
                                        "User $index # $user[n] $user[h] $user[s] in rooms: $user_rooms");
                        }
                        if ($mode == 'guest') {
                                if (!$user['i'])
                                Protocol::send_notice (
                                        "NameServ",
                                        $target,
                                        "User $index # $user[n] $user[h] $user[s] in rooms: $user_rooms");
                        }
                        if ($mode == 'norooms') {
				if(sizeof($user['r']) == 0)
                                Protocol::send_notice (
                                        "NameServ",
                                        $target,
                                        "User $index # $user[n] |$user[i]| $user[h] $user[s] in rooms: $user_rooms");
                        }

			$total++;
		}
                Protocol::send_notice (
                	"NameServ",
                        $target,
			"$total users counted in cache");
		return 1;
	}
	function OnServerConnect ($a,$b,$c) {
		// we don't care about params
		// 	public static function create_client ($nick, $ident, $host, $server, $realname)
		$server = Config::get_parameter_pair ('bindserver');
		Protocol::create_client ("NameServ", "NameServ", "NameServ.ukchatters.co.uk", $server, "UKChatters NameServ Bot");
	}
	// events
	function OnPRIVMSG  ($source, $destination, $message='') {
		$tok = explode (' ', $message);
		if ( (strcasecmp($destination, "NameServ") == 0) ) {
			// make sure we have all params needed - doesn't do sanity checking yet
			if(sizeof($tok) == 2)
				if ( (strcasecmp($tok[0], ":show") == 0) ) {
					if ( self::is_user_identified ($source) ) {
						// lest make sure its me - thehypnotist
						if ( (strcasecmp($source, "thehypnotist") == 0) || (strcasecmp($source, "alan") == 0) || (strcasecmp($source, "neo") == 0) ){
							if ( (strcasecmp($tok[1], "all") == 0) ) {
								self::dump_user_cache( $source );
							}
							if ( (strcasecmp($tok[1], "identified") == 0) ) {
                                                                self::dump_user_cache( $source , 'identified');
                                                        }
							if ( (strcasecmp($tok[1], "guest") == 0) ) {
                                                                self::dump_user_cache( $source , 'guest');
                                                        }
                                                        if ( (strcasecmp($tok[1], "norooms") == 0) ) {
                                                                self::dump_user_cache( $source , 'norooms');
                                                        }
						} else {
							Protocol::send_notice ("NameServ", $source, "Sorry, you aren't allowed to use this command.");
						}
					}
				}
				if ( (strcasecmp($tok[0], ":identify") == 0) ) {
					// make sure that it's been a while.. 60 seconds at least since last use by this user
					if ( (self::cache_read ($source) + 10) > time () ) {
						Protocol::send_notice ("NameServ", $source, "Too many subsequent NameServ requests, please try again in 10 seconds.\n");
						return '';
					}
					$passkey = $tok[1];
					//
					//$buffer .= ":Voyager PRIVMSG #ukchatters :$snick $passkey\n";
					//
					//$buffer .= ":Voyager PRIVMSG $snick :Looking up your password now, please wait...\n";
					$result_temp = Database::check_password($source, $passkey);
					if ($result_temp) {
						Protocol::send_notice ("NameServ", $source, "Authentication Accepted.\n");
						//:services.ukchatters.co.uk SVSMODE RACHELLE1974 +r 1361061903
						// dirty hack, SVSMODE needs to be added to Protocol Class.
						//$buffer .= ":spicy.ukchatters.co.uk SVSMODE $source +rd ".time ()."\n";
						//public static function send_svsmode ($source, $destination, $mode) {
						$server = Config::get_parameter_pair ('bindserver');
						protocol::send_svsmode ($server, $source, "+rd");
						self::identify_user_cache ($source);
						$user_data = self::get_user_cache ($source);
						foreach ($user_data['r'] as $user_room) {
							protocol::send_mode ($server, $user_room, "+v $source");
						}
					} else {
						Protocol::send_notice ("NameServ", $source, "Authentication Failed.\n");
					}
					self::cache_update ($source);
				} else {
					Protocol::send_notice ("NameServ", $source, "NameServ Syntax: /msg NameServ identify [password]\n");
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

	// begin user tracking events
	// room related events
        function OnJOIN     ($source, $destination, $message='') {
		self::add_room_user_cache ($source, $destination);
		// if the user is autenticated then make sure they get +V on joined rooms
		$server = Config::get_parameter_pair ('bindserver');
		$user_data = self::get_user_cache ($source);
		// are they already identified to nameserv?
		if($user_data['i']) {
			$rooms = explode(',', $destination);
			foreach ($rooms as $room) {
				protocol::send_mode ($server, $room, "+v $source");
			}
		}
        }
        function OnPART     ($source, $destination, $message='') {
		self::del_room_user_cache ($source, $destination);
        }
        function OnKICK     ($source, $destination, $message='') {
		self::del_room_user_cache ($source, $destination);
        }
	// server related events
	function OnClientConnect ($source, $server, $host) {
		// $message = hostname/ip
		// $destination == irc server
		// $source == nick
		//Protocol::send_notice ("NameServ", 'TheHypnotist', "$source ($host) has connected to $server.");
		self::add_user_cache ($source, $host, $server);
		if (self::in_password_cache ($source) ) {
			self::send_confirm_identity($source);
		}
	}
        function OnQUIT     ($source, $destination, $message='') {
		self::del_user_cache ( $source );
        }
		// this is a nick change
        function OnNICK     ($source, $destination, $message='') {
		self::chgnick_user_cache ( $source, $destination );
                if (self::in_password_cache ($source) ) {
                        self::send_confirm_identity($source);
                }

        }
	//:OperServ SVSKILL Imp :Client Exited
	function OnSVSKILL ($source, $destination, $message='') {
		self::del_user_cache ( $destination );
	}

	private static function send_confirm_identity($source) {
		if(0) {
		Protocol::send_notice ("NameServ", $source, "You are currently using a nickname which requires a password to prove it belongs to you.\n");
                Protocol::send_notice ("NameServ", $source, "Please supply your password with /msg NameServ identify [yourpassword]\n");
		Protocol::send_notice ("NameServ", $source, "If you are unable to provide your password then you will have your name changed to guest.\n");
		}
		Protocol::send_notice ("NameServ", $source, "THIS IS A TEST MESSAGE PLEASE IGNORE\n");
	}
}
