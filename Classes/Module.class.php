<?php
Class Module {
	private static $modules = array();
	function __construct () {}
	private static function loadModule($mod) {
		// only loads one time, not ready for rehash of code
		require_once "./Modules/$mod/$mod.class.php";
		$service = new $mod;
		return $service;
	}
	public function load ($module) {
		$service = self::loadModule($module);
		$meta = $service->__loaded();
		self::addModule ($module, $service, $meta);
		return $meta;
	}
	private static function addModule ($name, $instance, $meta) {
		$object = array (
			'name' => $name,
			'instance' => $instance,
			'meta' => $meta
		);
		// no checking for rehashed code
		self::$modules[] = $object;
	}
	public function list_modules () {
		$list = "";
		foreach (self::$modules as $module) {
			$list .= $module['name'] ." - ". $module['meta']['version'] ."\n";
		}
		return $list;
	}
	public function dispatch_event ($event, $source, $destination, $message='') {
		// I should really stop reusing variables. It caused me a stupid bug.
		$event = "On" . $event;
		foreach (self::$modules as $module) {
			if (method_exists ($module['instance'], $event)) {
				$module['instance']->$event($source, $destination, $message);
			} else {

			}
		}
	}
}
