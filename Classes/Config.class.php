<?php

Interface iConfig {
	public static function set_parameter_pair($array);
	public static function get_parameter_pair($key);
}
Class Config implements iConfig {
	private static $configuration;
	public static function set_parameter_pair($array) {
		//expects array[0] to be key and [1] value
		self::$configuration[$array[0]] = $array[1];
	}
	public static function get_parameter_pair($key) {
		return self::$configuration[$key];
	}

}
