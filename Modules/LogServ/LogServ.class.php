<?php

Class LogServ {
	function __loaded () {
		return Array (
			'name'    => "LogServ",
			'version' => "0.3",
			'author'  => "TheHypnotist",
			'date'    => "2013-4-17",
			'info'    => "This module is for logging channels."
		);
	}
        function OnServerConnect ($a,$b,$c) {
                // we don't care about params
                //      public static function create_client ($nick, $ident, $host, $server, $realname)
                $server = Config::get_parameter_pair ('bindserver');
                Protocol::create_client ("LogServ", "Log", "logserv.ukchatters.co.uk", $server, "UKChatters Logging Bot");
                Protocol::send_join ("LogServ", "#staff");
        }
	private static function insert ($source, $destination, $message='', $target='', $event) {
		$assoc = Array(
//			'id' => 'NULL',
//			'sqltimestamp' => 'CURRENT_TIMESTAMP',
			'phptime'      => time (),
			'source'       => $source,
			'target'       => $target,
			'destination'  => $destination,
			'message'      => $message,
			'event'        => $event
		);
		$table = 'log_room';
		Database::insert($table, $assoc);
	}
//                $query = "SELECT * FROM  `$table` WHERE  `phptime` > $timestamp AND  `destination` LIKE  '$channel' ORDER BY  `$table`.`id` ASC ";

        private static function select_room_log_15m ($source, $destination) {
                $assoc = Array(
                        'phptime'      => strtotime("-15 minute"),
                        'destination'  => $destination,
			'source'	=> $source
                );
                $table = 'log_room';
                return Database::select($table, $assoc);
        }

	// events
	function OnPRIVMSG  ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'PRIVMSG');
		echo time() ."| $source privmsg $destination with $message\n";
		// if it's a staff-room query strtotime("-15 minute")
                $tok = explode (' ', $message);
                if ( (strcasecmp($destination, "#staff") == 0) ) {
                        // make sure we have all params needed - doesn't do sanity checking yet
                        if ( (strcasecmp($tok[0], ":!log") == 0) ) {
				if (!isset($tok[1])) {
					Protocol::send_privmsg ("LogServ", $destination, "Syntax for Log viewing is: !log #roomname\n");
					return;
				}
				if ($tok[1][0] == '#') {
					Protocol::send_privmsg ("LogServ", $destination, "Sending log to you privately $source.\n");
					$temp_log = self::select_room_log_15m ($source, $tok[1]);
					Protocol::send_privmsg ("LogServ", $source, "// Begin 15 minutes of $tok[1] log output//\n");
					foreach ( $temp_log as $row ) {
						if ($row['event'] == 'PRIVMSG') {
							Protocol::send_privmsg ("LogServ", $source, ' <'.  $row['source']  .'>  '. $row['message']. "\n");
						}
						if ($row['event'] == 'NOTICE') {
							Protocol::send_privmsg ("LogServ", $source, ' !'.  $row['source']  .'!  '. $row['message']. "\n");
						}
						if ($row['event'] == 'JOIN') {
							Protocol::send_privmsg ("LogServ", $source, ' *** '.  $row['source']  ." joined the room \n");
						}
                                                if ($row['event'] == 'PART') {
                                                        Protocol::send_privmsg ("LogServ", $source, ' *** '.  $row['source']  ." left the room \n");
                                                }
                                                if ($row['event'] == 'QUIT') {
                                                        Protocol::send_privmsg ("LogServ", $source, ' *** '.  $row['source']  ." quit from chat \n");
                                                }
                                                if ($row['event'] == 'KICK') {
                                                        Protocol::send_privmsg ("LogServ", $source, ' *** '.  $row['source']  ." has kicked ".$row['target']." from the room \n");
                                                }
						//Protocol::send_privmsg ("LogServ", '#staff', $row['source'] ." event> ". $row['event'] ."\n");

					}
					Protocol::send_privmsg ("LogServ", $source, "//End of log output//\n");
				} else {
					Protocol::send_privmsg ("LogServ", $destination, "Syntax for Log viewing is: !log #roomname\n");
				}
			}
		}
	}
	function OnNOTICE   ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'NOTICE');
		echo time() ."| $source notice $destination with $message\n";
	}
        function OnClientConnect ($source, $destination, $message) {
                // $message = hostname/ip
                // $destination == irc server
                // $source == nick
                //Protocol::send_notice ("NameServ", 'TheHypnotist', "$source ($host) has connected to $server.");
		self::insert ($source, $destination, $message, '', 'CONNECT');
		echo time() ."| $source connected to $destination with ip/hostname $message\n";
		/*
			1366165290|:irc.ukchatters.co.uk SPYFORWARD ADD TheHypnotist cariad * *
			1366165297|:irc.ukchatters.co.uk SPYFORWARD DEL TheHypnotist cariad *
		*/
		$server = Config::get_parameter_pair ('bindserver');
		Protocol::send_raw (":$server SPYFORWARD ADD LogServ $source * *\n");
	}
	function OnJOIN     ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'JOIN');
		echo time() ."| $source join $destination\n";
	}
	function OnPART     ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'PART');
		echo time() ."| $source part $destination with $message\n";
	}
	function OnQUIT     ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'QUIT');
		echo time() ."| $source quit $destination with $message\n";
                /*
                        1366165290|:irc.ukchatters.co.uk SPYFORWARD ADD TheHypnotist cariad * *
                        1366165297|:irc.ukchatters.co.uk SPYFORWARD DEL TheHypnotist cariad *
                */
                $server = Config::get_parameter_pair ('bindserver');
                Protocol::send_raw (":$server SPYFORWARD DEL LogServ $source *\n");
	}
	function OnNICK     ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'NICK');
		echo time() ."| $source nick $destination\n";
	}
	function OnKICK     ($source, $destination, $message='') {
		$message = explode (" ", $message);
		$target = $message[0];
		unset ($message[0]);
		$message = implode (" ", $message);
		self::insert ($source, $destination, $message, $target, 'KICK');
		echo time() ."| $source kick $target from $destination with $message\n";
	}
	function OnTOPIC    ($source, $destination, $message='') {
		echo time() ."| TODO: Topic event LogServ\n";
	}
	function OnKILL     ($source, $destination, $message='') {
		self::insert ($source, $destination, $message, '', 'KILL');
		echo time() ."| $source KILL $destination with $message\n";
                /*
                        1366165290|:irc.ukchatters.co.uk SPYFORWARD ADD TheHypnotist cariad * *
                        1366165297|:irc.ukchatters.co.uk SPYFORWARD DEL TheHypnotist cariad *
                */
                $server = Config::get_parameter_pair ('bindserver');
                Protocol::send_raw (":$server SPYFORWARD DEL LogServ $source *\n");
	}
        function OnSVSKILL ($source, $destination, $message='') {
                self::insert ($source, $destination, $message, '', 'SVSKILL');
                echo time() ."| $source SVSKILL $destination with $message\n";
                /*
                        1366165290|:irc.ukchatters.co.uk SPYFORWARD ADD TheHypnotist cariad * *
                        1366165297|:irc.ukchatters.co.uk SPYFORWARD DEL TheHypnotist cariad *
                */
                $server = Config::get_parameter_pair ('bindserver');
                Protocol::send_raw (":$server SPYFORWARD DEL LogServ $source *\n");
        }

}
