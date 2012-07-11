<?php


class UWAPLogger {

	// Class variables. Singleton instance.
	protected static $instance = null;

	// Object variables
	protected $store, $config, $subid;

	protected $logLevel = 4;
	protected $stacktrace = true;

	// Private constructor, called by init()
	protected function __construct() {

		$this->store = new UWAPStore();

		$this->config = GlobalConfig::getValue('logging', array());

		// $this->config = isset($conf['logging']) ? $conf['logging'] : array();
		$this->subid = Utils::getSubID();

		$this->logLevel   = 4; // Debug and more
		$this->stacktrace = false;
	}


	public static function init() {
		if (is_null(self::$instance)){
			self::$instance = new self();	
		}
		
		return self::$instance;
	}


	// ----- ----- ----- ----- Object methods

	public function _log($level, $module, $message, $obj = null) {

		if ($level > $this->logLevel) continue;

		$logmessage = array(
			'message' => $message,
			'level' => $level,
			'time' => microtime(true),
			'host' => gethostname(),
			'module' => $module,
			'subid' => $this->subid,
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

	protected static function log($level, $module, $message, $obj = null) { 
		error_log("Static logger [level " . $level . "] module [" . $module . "]: " . $message);
		$l = self::init();
		$l->_log($level, $module, $message, $obj); 
	}

	public static function debug ($module, $message, $obj = array()) { self::log(4, $module, $message, $obj); }
	public static function info  ($module, $message, $obj = array()) { self::log(3, $module, $message, $obj); }
	public static function warn  ($module, $message, $obj = array()) { self::log(2, $module, $message, $obj); }
	public static function error ($module, $message, $obj = array()) { self::log(1, $module, $message, $obj); }

	public static function statistics($path) {
		
	}


}

