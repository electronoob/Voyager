<?php
/*
[21:51] <ChineseChappy> just some suggestions and bug fixes
[21:51] <ChineseChappy> x 1. Fix bold text recognition
[21:51] <ChineseChappy> x 2. Fix issue where multiple players who are recognised as answering the question correctly
[21:51] <ChineseChappy> x 3. Include the answer when acknowledging the person who answered correctly
[21:51] <ChineseChappy> 3b. Include the player's current points total and rank
[21:51] <ChineseChappy> x 4. Apply some colour or boldness to distinguish Trivia bot questions and hints from player's text
[21:51] <ChineseChappy> I'm sure there'll be more
[21:52] <ChineseChappy> it'll give you something to work on
[21:52] <ChineseChappy> :)
*/
Class Trivia {
	private static $questions = Array();
	private static $questions_count;
	private static $question = Array();
	private static $pulse = 0;
	private static $queue = Array();
	function __loaded () {
		// load question file
		$file_path = '/home/ch/wkb_development/voyager/Modules/Trivia/';
		$questions = file_get_contents ($file_path . 'questions.txt');
		$questions = str_replace("\r", '', $questions);
		// we have our questions in ram now hopefully :)
		self::$questions = explode("\n", $questions);
		self::$questions_count = sizeof(self::$questions);

		return Array (
			'name'    => "Trivia",
			'version' => "0.1",
			'author'  => "TheHypnotist",
			'date'    => "2013-3-16",
			'info'    => "This module brings the game of trivia to Ariel-2.0"
		);
	}
	private function blank_hint($string) {
                $len = strlen ($string);
                for($i=0;$i<$len;$i++){
                        // we have to ignore spaces in our calculations as they are always shown in hints
                        if ($string[$i] != ' ') {
				$string[$i] = '_';
			}
                }
                return $string;

	}
	private function count_characters ( $string ) {
		$i=0;
		$len = strlen ($string);
		$count = 0;
		for(;$i<$len;$i++){
			// we have to ignore spaces in our calculations as they are always shown in hints
			if($string[$i] != ' ') $count++;
		}
		return $count;
	}
	private function unmask_hint ( $level ) {
		$answer = self::$question['a'];
		$length = strlen( self::$question['h'] );
		$hint = self::$question['h'];
		$count = self::count_characters ( $hint );
		if ($level == 1) $c = round( $count / 2, 0, PHP_ROUND_HALF_DOWN);
		if ($level == 2) $c = round( $count / 4, 0, PHP_ROUND_HALF_DOWN);
		if ($level == 3) $c = round( $count / 4, 0, PHP_ROUND_HALF_DOWN) - 1;
		// working in reverse - generate a masked hint then put back characters that are missing to show more of the answer
		for($i=1;$i<=$c;$i++){
			// loop around half as many times as we have letters
			// but keeping a track to make sure we don't mess with spaces
			$position = rand(0, $length - 1);
			// if  covered character is found uncover it
			if ($hint[$position] == '_') {
				$hint[$position] = $answer[$position];
				continue;
			}
			// if we detect a space skip it
			if ($hint[$position] == ' ') {
				$i--;
				continue;
			}
			// if we are trying to uncover an already revealed character try again
			if ($hint[$position] == $answer[$position]) {
				$i--;
				continue;
			}
		}
		self::$question['h'] = $hint;
	}
	private function event_queue_pulse () {
		if (sizeof(self::$queue > 0) )  {
			foreach(self::$queue as $id => $event) {
				if ($event['s'] <= time()) {
					switch ($event['t']) {
						case 'new_question':
							Protocol::send_privmsg("Trivia-bot", "#Room101", chr(3) . self::get_random_question());
							self::$question['points'] = 4;
							self::event_queue_add (30, 'timeup', NULL);
							self::event_queue_add (8, 'hint1', NULL);
							self::event_queue_add (16, 'hint2', NULL);
							self::event_queue_add (24, 'hint3', NULL);
							break;
						case 'hint1':
							self::$question['points']--;
							// hint masking of word - new to that myself.
							// preserve spacing and punctuation i believe
							self::unmask_hint ( 1 ) ;
							Protocol::send_privmsg("Trivia-bot", "#Room101",  chr(3) . "5Here's your 1st hint, " . self::$question['h']);
							break;
						case 'hint2':
							self::$question['points']--;
							self::unmask_hint ( 2 ) ;
							Protocol::send_privmsg("Trivia-bot", "#Room101",  chr(3) . "5Here's your 2nd hint, " . self::$question['h']);
							break;
						case 'hint3':
							self::$question['points']--;
							self::unmask_hint ( 3 ) ;
							Protocol::send_privmsg("Trivia-bot", "#Room101",  chr(3) . "5Here's your 3rd hint, " . self::$question['h']);
							break;
						case 'timeup':
							self::$question['points'] = 0;
							Protocol::send_privmsg("Trivia-bot", "#Room101",  chr(3) . "5Time's up! The answer was: ".self::$question['a']);
							self::event_queue_add (4, 'new_question', NULL);
							break;
					}
					// clear particular event from queue
					unset(self::$queue[$id]);
				}
			}
		}
		return;
	}
	private function event_queue_add ($seconds, $type, $data) {
		//self::queue
		$seconds = time() + $seconds;
		self::$queue[] = Array('s' => $seconds, 't' => $type, 'd' => $data);
		//var_dump(self::$queue);
	}
	private function event_queue_del ($id) {

	}
	private function event_queue_list ($destination) {
		foreach (self::$queue as $array) {
			$line = 'Seconds: '.$array['s'] .'. Type: '.$array['t'].' Data: '.$array['d'];
			Protocol::send_privmsg("Trivia-bot", $destination, $line);
		}
	}
        public function OnPulse () {
                // The idea of OnPulse will be to enable queued events based on how many seconds have passed by etc.
                // due to this being an Ariel event Pulse is mostly lowercase to differentiate it from an IRC event of all caps.
		// make it so.. every 10 seconds mention the heartbeat
		self::$pulse++;
		if (self::$pulse == 10) {
			//Protocol::send_privmsg("Trivia-bot", "#Room101", "Heart beat received @ ". time());
			self::$pulse = 0;
		}
		self::event_queue_pulse();
        }
	function get_random_question () {
		$id = mt_rand(0, self::$questions_count -1);

		echo "[$id]\n";
		$entry = self::$questions[$id];
		$parts = explode('*', $entry);
		$question = $parts[0];
		$answer = trim($parts[1]);
		$hint = self::blank_hint ( $answer );
		self::$question = array('q' => $question, 'a' => $answer, 't' => microtime(true), 'h' => $hint);
		return $question;
	}
        function OnServerConnect ($a,$b,$c) {
                // we don't care about params
                //      public static function create_client ($nick, $ident, $host, $server, $realname)
                $server = Config::get_parameter_pair ('bindserver');
                Protocol::create_client ("Trivia-bot", "Trivia-bot", "trivia.ukchatters.co.uk", $server, "UKChatters Trivia Bot");
                Protocol::send_join ("Trivia-bot", "#Room101");
		self::event_queue_add (20, 'new_question', NULL);
		Protocol::send_privmsg("Trivia-bot", "#Room101", "Trivia V0.1 written by TheHypnotist is ready for action. Unlimited game starting in 20 seconds.");
        }
	// events
	function OnPRIVMSG  ($source, $destination, $message='') {
		$message = strtolower ($message);
		$message = str_replace(chr(2), '', $message);
		$tok = explode(' ', $message);
		if (isset(self::$question['a']))
		if ( $message == strtolower (':'.self::$question['a']) ) {
                        $duration =  microtime(true) - self::$question['t'];
                        $duration_round = round($duration, 4);
			$pretty_nick = chr(3) . "8,1" . chr(2). $source . chr(2) . chr(3);
			$points_round = self::$question['points'];
			//.self::$question['a']

                	Protocol::send_privmsg("Trivia-bot", "#Room101", "[$pretty_nick]".chr(3)."5 earns $points_round points for the correct answer $duration_round seconds.");
			Protocol::send_privmsg("Trivia-bot", "#Room101", chr(3)."5The Answer was: ".self::$question['a']);

			// purge answer from memory to stop multiple people answering the same question
			self::$question['a'] = NULL;
			self::$queue = array(); // now that the question has been answered let's purge the event queue to make sure nothing remains unwanted
			self::event_queue_add (5, 'new_question', NULL);
			return;
		}
		if (strcasecmp ("#Room101", $destination) === 0) {
			//get_random_question ()
			switch ($tok[0]) {
				case ':!qa':
					// let's be naive  !qa <seconds> <event> <reason>
					self::event_queue_add (time() + $tok[1], $tok[2], $tok[3]);
					Protocol::send_privmsg("Trivia-bot", "#Room101", 'adding?');
					break;
				case ':!ql':
					self::event_queue_list ($source);
					Protocol::send_privmsg("Trivia-bot", "#Room101", 'listing?');
					break;
				case ':!question':
					Protocol::send_privmsg("Trivia-bot", "#Room101", self::get_random_question());
					break;
				case ':?cheat':
					if (!isset(self::$question['a'])) break;
					Protocol::send_privmsg("Trivia-bot", "#Room101",  "psst! the answer is '" . self::$question['a'] ."'");
					break;
				default:
					break;
			}
		}
	}
	function OnNOTICE   ($source, $destination, $message='') {
	}
	function OnJOIN     ($source, $destination, $message='') {
                // remember joins and parts can be done on many rooms at same time.
                $rooms = explode(',', $destination);
                $room_to_detect = "#Room101";
                if ( 0 === array_search(strtolower($room_to_detect), array_map('strtolower', $rooms)) ) {
			Protocol::send_notice ("Trivia-bot", $source, "Hello $source! Welcome to our new trivia room ran by me.");
        	        Protocol::send_notice ("Trivia-bot", $source, "I currently have no commands available as I'm brand new. Be gentle with me!");
		}
	}
	function OnPART     ($source, $destination, $message='') {
	}
	function OnQUIT     ($source, $destination, $message='') {
	}
	function OnNICK     ($source, $destination, $message='') {
	}
	function OnKICK     ($source, $destination, $message='') {
		$message = explode (" ", $message);
		$target = $message[0];
		unset ($message[0]);
		$message = implode (" ", $message);
	}
	function OnTOPIC    ($source, $destination, $message='') {
	}
	function OnKILL     ($source, $destination, $message='') {
	}
}
