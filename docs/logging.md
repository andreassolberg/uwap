# About Logging


Logging is performed using:

	UWAP_Logger::debug(module, message, [object]);

Log interface:

	public static function debug($message, $obj = array()) { self::log(4, $message, $obj); }
	public static function info($message, $obj = array()) { self::log(3, $message, $obj); }
	public static function warn($message, $obj = array()) { self::log(2, $message, $obj); }
	public static function error($message, $obj = array()) { self::log(1, $message, $obj); }


