<?php

Class Protocol {
	private static $server_instance;
	// accepts one single line of IRC protocol
	public static function set_server_instance ($instance) {
		self::$server_instance = $instance;
	}
	public static function get_server_instance () {
		return self::$server_instance;
	}
	function parse_line($line) {
		$buffer = '';
		$EOS_count = 0;
		// I think regex is ugly. switch is pretty and logical.
		//////////////////////////////////////////////////////
		$line = str_replace("\r", '', $line); // if there are any \r they need purging
                // some lame level of logging for now as i work on this
		if(strlen($line) > 0)
                file_put_contents("logging.proto.txt", time() . "|" . $line . "\n", FILE_APPEND);
		//echo "[$line]\n";
		//
		// break into tokens-- split by spaces
		$tok = explode(' ', $line);
		// handles first token in line. list follows:
		// PING
		switch ($tok[0]) {
			case "PING":
				$buffer .= "PONG $tok[1]\n";
				break;
		}
		if(sizeof ($tok) >2) {
			// specific event 'NICK' for when a client connects to the network
			if ($tok[0] == 'NICK') {
				$source = $tok[1];
				$message = $tok[5];
				$destination = $tok[6];
				// yep Cheaky cheaky
				$event = 'ClientConnect';
			} else {
	                	$source = str_replace(':', '', $tok[0]);
	                	$destination = $tok[2];
        	        	$message = $tok;
                		unset ($message[0]);
       	         		unset ($message[1]);
	                	unset ($message[2]);
                		$message = implode(" ", $message);
				$event = $tok[1];
			}
			$buffer .= Module::dispatch_event ($event, $source, $destination, $message);
		}
		if(sizeof ($tok) >1)
		switch ($tok[1]) {
			// EOS was fail -- :irc.ukchatters.co.uk EOS
			case "EOS":
				if ( (strcasecmp($tok[0], ":irc.ukchatters.co.uk") == 0) ) {
					$EOS_count++;
					if ($EOS_count >1) return;
					echo "*** we appear to be connected to IRC server, received end of sync for irc.ukchatters.co.uk\n";
					$time_temp = time();
					$buffer .= "NICK Voyager 1 ".time ()." Spicy bot.ukchatters.co.uk spicy.ukchatters.co.uk 1 :Ariel2.0 UKChatters Robot\n";
					$buffer .= "NICK NickServ2 1 ".time ()." Spicy bot.ukchatters.co.uk spicy.ukchatters.co.uk 1 :Ariel2.0 UKChatters NickServ\n";
					$buffer .= self::join_rooms("Voyager");
					$buffer .= ":Voyager PRIVMSG NickServ :identify spicyvoyager\n";
					$buffer .= Module::dispatch_event ("ServerConnect", '', '', '');
					// this is an attempt at dealing with the harmony module.
					$buffer .= "SPYFORWARD CONNECT\nSPYCHFORWARD CONNECT\n";
				}
				break;
		}
		return $buffer;
	}
	public function heartbeat () {
		Module::dispatch_event ('Pulse', NULL, NULL, 'HEARTBEAT');
	}
	private static function join_rooms($botnick){
		$buffer = '';
		$channels = Config::get_parameter_pair('channels');
		foreach ($channels as $channel) {
			$buffer .= ":".$botnick." JOIN #$channel\n";
			$buffer .= ":spicy.ukchatters.co.uk MODE #$channel +oaq ".$botnick." ".$botnick." ".$botnick."\n";
		}
		return $buffer;
	}
	public static function send_raw ($raw) {
		// dangerous hacks only really will use this as I would anticipate anyway.
		//
		$server_instance = self::get_server_instance ();
		$server_instance->write ($raw);
	        $server_instance->send ();
	}
	public static function send_mode ($source, $destination, $modes) {
		$proto = ":$source MODE $destination $modes\n";
		self::send_raw ($proto);
	}


	public static function send_privmsg ($source, $destination, $message) {
		$proto = ":$source PRIVMSG $destination :$message\n";
		self::send_raw ($proto);
	}
	public static function send_notice ($source, $destination, $message) {
                $proto = ":$source NOTICE $destination :$message\n";
		self::send_raw ($proto);
	}
	public static function send_join ($source, $destination) {
                $proto = ":$source JOIN $destination\n";
		self::send_raw ($proto);
	}
	public static function send_part ($source, $destination, $message) {
                $proto = ":$source PART $destination :$message\n";
		self::send_raw ($proto);
        }
	public static function send_kick ($source, $destination, $message) {
                $proto = ":$source KICK $destination :$message\n";
                self::send_raw ($proto);
	}
	public static function send_quit ($source, $message) {
                $proto = ":$source QUIT :$message\n";
                self::send_raw ($proto);
        }
	//$buffer .= "NICK Voyager 1 ".time ()." Spicy bot.ukchatters.co.uk spicy.ukchatters.co.uk 1 :Ariel2.0 UKChatters Robot\n";
	public static function create_client ($nick, $ident, $host, $server, $realname)
	{
		$proto = "NICK $nick 1 ".time ()." $ident $host $server 1 :$realname\n";
		self::send_raw ($proto);
	}
	public static function send_svsmode ($source, $destination, $mode) {
		$proto = ":$source SVSMODE $destination $mode ".time ()."\n";
		self::send_raw ($proto);
	}

}
