<?php

// Not currently used for Voyager.
Interface iClient {
	public function set_id($id);
	public function get_id();
	public function set_ident($ident);
	public function get_ident();
	public function set_nick($nick);
	public function get_nick();
	public function set_realname($realname);
	public function get_realname($realname);
	public function set_hostname($hostame);
	public function get_hostname();
	public function set_ip($ip);
	public function get_ip();
	public function set_state($state);
	public function get_state();
}

Class Client implements iClient {
	/*
		$user:
			['id'], ['nick'], ['ident'], ['realname'], ['hostname'], ['ip']
	*/
	private $user = array();
	public function set_id($id) {
		$this->user['id'] = $id;
		return 1;
	}
	public function get_id() {
		return $this->user['id'];
	}
	public function set_ident($ident) {
		$this->user['ident'] = $ident;
		return 1;
	}
	public function get_ident() {
		return $this->user['ident'];
	}
	public function set_nick($nick) {
		$this->user['nick'] = $nick;
		return 1;
	}
	public function get_nick() {
		return $this->user['nick'];
	}
	public function set_realname($realname) {
		$this->user['realname'] = $realname;
		return 1;
	}
	public function get_realname($realname) {
		return $this->user['realname'];
	}
	public function set_hostname($hostame) {
		$this->user['hostname'] = $hostname;
		return 1;
	}
	public function get_hostname() {
		return $this->user['hostname'];
	}
	public function set_ip($ip) {
		$this->user['ip'] = $ip;
		return 1;
	}
	public function get_ip() {
		return $this->user['ip'];
	}
	public function get_state() {
		return $this->user['state'];
	}
	public function set_state($state) {
		$this->user['state'] = $state;
	}
}
