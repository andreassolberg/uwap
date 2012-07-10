<?php


class UWAPLogger {

	// Class variables. Singleton instance.
	protected static $instance = null;

	// Object variables
	protected $store, $config, $subid;

	protected static $logLevel = 4;
	protected static $stacktrace = true;

	// Private constructor, called by init()
	private function __construct() {

		$this->store = new UWAPStore();
		$configobj = new Config($subid);
		$conf = $configobj->getGlobalConfig();
		$this->config = isset($conf['logging']) ? $conf['logging'] : array();
		$this->subid = $this->config->getID();

		$this->logLevel   = 4; // Debug and more
		$this->stacktrace = false;
	}

	public static function init() {
		if (!is_null(self::$instance)) return;
		self::$instance = new self();
	}


	// ----- ----- ----- ----- Object methods

	protected function _log($level, $message, $obj = null) {

		if ($level > self::$logLevel) continue;
		if (empty(self::$db)) self::init();

		$logmessage = array(
			'message' => $message,
			'level' => $level,
			'time' => microtime(true),
			'host' => gethostname()
		);

		if (isset($obj)) {
			$logmessage['object'] = $obj;
		}

		// if (self::$stacktrace) {
		// 	$debug = debug_backtrace();
		// 	$obj['_location'] = $debug[2]['function'] . ' (line ' . $debug[2]['line'] . ')';
		// 	// $obj['_stacktrace'] = $debug; Generates a lot of data...	
		// }

		$this->store->store('log', null, $logmessage);
	}


	// ----- ----- ----- ----- Static methods

	protected static function log($level, $message, $obj = null) { 
		self::init();
		self::log($level, $message, $obj); 
	}

	public static function debug($module, $message, $obj = array()) { self::log(4, $message, $obj); }
	public static function info($module, $message, $obj = array()) { self::log(3, $message, $obj); }
	public static function warn($module, $message, $obj = array()) { self::log(2, $message, $obj); }
	public static function error($module, $message, $obj = array()) { self::log(1, $message, $obj); }

	public static function statistics($path) {
		
	}


}

