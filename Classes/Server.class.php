<?php
Interface iServer {
	public function start_server();
	public function add_client($fd);
	public function get_client();
	public function del_client($index);
}

Class Server extends Socket implements iServer {
	private $clients;
	private $running = false;
	private $index = 0;
	private $heartbeat = 0;
	public function start_server() {
		if (class_exists('Log')) Log::stdinfo("attempting start_server()");

		$this->set_fd ( socket_create(AF_INET, SOCK_STREAM, SOL_TCP) );
		socket_set_option($this->get_fd(), SOL_SOCKET, SO_REUSEADDR, 1);
		socket_bind($this->get_fd(), Config::get_parameter_pair('bindip'));
		$result = socket_connect(
				$this->get_fd(),
				Config::get_parameter_pair('connip'),
				Config::get_parameter_pair('connport')
		);

		if(!$result) { die ("Unable to connect to remote host specified in Config\n"); }
		// let's ya know do stuffs like connect as a client temp solution
/*
Config::set_parameter_pair (array ('connport', 7121) );
Config::set_parameter_pair (array ('connip', '176.67.167.42') );
Config::set_parameter_pair (array ('bindip', '0.0.0.0') );
Config::set_parameter_pair (array ('linkpassword', 'spicyf00') );
Config::set_parameter_pair (array ('bindserver', 'spicy.ukchatters.co.uk') );
Config::set_parameter_pair (array ('ircserver', 'irc.ukchatters.co.uk') );
*/
/*
		$this->write ("HELP\n");
		$this->write ("PASS :". Config::get_parameter_pair ('linkpassword')) ."\n";
		$this->write ("SERVER spicy.ukchatters.co.uk 1 :Special Awesome\n");
*/
		$sendpass = Config::get_parameter_pair ('linkpassword');
		$sendserver = Config::get_parameter_pair ('bindserver');
		$sendid = Config::get_parameter_pair ('bindid');
		$senddescription = Config::get_parameter_pair ('binddescription');
		$recvpass = ''; //not implemented yet.
		$this->write ("PASS :$sendpass\n");
		$this->write ("SERVER $sendserver $sendid :$senddescription\n");
		$this->send ();
		$this->running = true;
		$this->main_loop();
	}
	private function get_socket_array() {
		// inject main socket for listen event
		$temp[] = $this->get_fd();
		if(count($this->clients)>0)
			foreach ($this->clients as $array)
				$temp[] = $array->get_fd();
		return $temp;
	}
	// for now the granular control is at 1 second per heartbeat.
	private function heartbeat () {
		if ($this->heartbeat < time()) {
			$this->heartbeat = time();
			Protocol::heartbeat ();
		}
	}
	private function main_loop() {
		if (class_exists('Log')) Log::stdinfo("Entering main_loop()");
		while ($this->running) {
			$this->heartbeat ();
			// reduce server load without introducing too much lag
			usleep(5);
			// run through our client objects
			$read = $this->get_socket_array();
			// copy $this->$clients to preserve
			// if no sockets are ready to be read start loop again
			if(($changes = socket_select($read, $write = NULL, $except = NULL, 0)) < 1)
			continue;

			// read data in from socket
			foreach($read as $read_socket) {
				// $x will contain bytes read count
				$x = $this->read();
				if ($x === false) {
					// socket_read error
					die("Enable to read from socket - likely expired \n");
				}
				if ($x >0) {
					// testing
					//echo $this->get_buffer_read();
					// need to make sure we have at least 1 full line of protocol to send
					// to the protocol engine
					$pos = strpos($buffer_temp = $this->get_buffer_read(), "\n");
					if ($pos === 0) {
						//if (class_exists('Log')) Log::stdinfo("no line feed char on read buffer");
					} else {
						// let's see if the end of the buffer is a new line character or not
						// if so I think it's safe to assume that we have a full line
						if ( substr($buffer_temp, -1) == "\n") {
							//if (class_exists('Log')) Log::stdinfo("we have a line feed at end of buffer");
							// clear the buffer for this socket
							$this->clear_buffer_read();
							$result_temp = explode("\n", $buffer_temp);
							foreach($result_temp as $line) {
								$proto_temp = Protocol::parse_line($line);
								file_put_contents ("logging.proto-write.txt", $proto_temp, FILE_APPEND);
								$this->write ($proto_temp);
								$this->send ();
							}
						} else {
							//if (class_exists('Log')) Log::stdinfo("I think we have an incomplete buffer");
							// as a temp cop-out I will just wait for it to become full etc
						}
					}
				}
			}
			/* This will be used to read through the write queues. */
			//$this->process_buffers();
		}
	}
	private function process_buffers() {
	//	$this->write("testing\n");
	//	$this->send();
				//$client->write("lalalalaal\n");
				//$client->send();
	}
	private function get_client_index_by_fd($fd) {
		foreach($this->clients as $client) {
			if ($fd == $client->get_fd()) {
				return $client->get_id();
			}
		}
	}
	// client operations
	public function add_client($fd) {
		$client = new Client();
		$this->index++;
		$client->set_id($this->index);
		$client->set_fd($fd);
		// setting state as zero here tells server buffer parsing code
		// that the socket hasnt completed a hadnshake yet with websocket
		// client
		$client->set_state(0);
		$this->clients[$this->index] = $client;
		unset($client);
		return $this->index;
	}
	public function get_client() {
		return -1;
	}
	public function del_client($index) {
		unset($this->clients[$index]);
		echo "client disconnected and removed from pool\n";
		return 1;
	}
}
