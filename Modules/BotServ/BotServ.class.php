<?php

Class BotServ {
	function __loaded () {
		return Array (
			'name'    => "BotServ",
			'version' => "0.1",
			'author'  => "TheHypnotist",
			'date'    => "2013-2-19",
			'info'    => "This module is for official bot management."
		);
	}
	function OnServerConnect ($a,$b,$c) {
		// we don't care about params
		// 	public static function create_client ($nick, $ident, $host, $server, $realname)
		$server = $database = Config::get_parameter_pair ('bindserver');
		Protocol::create_client ("BotServ", "BotServ", "botserv.ukchatters.co.uk", $server, "UKChatters BotServ");
	}
	// events
	function OnPRIVMSG  ($source, $destination, $message='') {

	}
	function OnNOTICE   ($source, $destination, $message='') {
		echo "$source notice $destination with $message\n";
	}
	function OnJOIN     ($source, $destination, $message='') {
		echo "$source join $destination\n";
	}
	function OnPART     ($source, $destination, $message='') {
		echo "$source part $destination with $message\n";
	}
	function OnQUIT     ($source, $destination, $message='') {
		echo "$source quit $destination with $message\n";
	}
	function OnNICK     ($source, $destination, $message='') {
		echo "$source nick $destination\n";
	}
	function OnKICK     ($source, $destination, $message='') {
		$message = explode (" ", $message);
		$target = $message[0];
		unset ($message[0]);
		$message = implode (" ", $message);
		echo "$source kick $target from $destination with $message\n";
	}
	function OnTOPIC    ($source, $destination, $message='') {
		echo "TODO: Topic event LogServ\n";
	}
	function OnKILL     ($source, $destination, $message='') {
		echo "$source KILL $destination with $message\n";
	}

}
