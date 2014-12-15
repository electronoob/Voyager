<?php
Interface iLog {
	public function stderror ($errno, $message);
	public function stdinfo ($message);
	public function stdwarn ($message);
}
Class Log implements iLog {
	public function stderror ($errno, $message) {
		// only writes to STDOUT for now
		echo "[error] ($errno) $message\n";
	}
	public function stdinfo ($message) {
		echo "[info] $message\n";
	}
	public function stdwarn ($message) {
		echo "[warn] $message\n";
	}
}
