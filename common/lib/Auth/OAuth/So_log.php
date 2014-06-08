<?php

/*
 * Log to MongoDB 
 */
class So_log {
	protected static $db;
	
	// Logged error messages beyond this level, will not 
	// be logged
	protected static $logLevel = 4;
	protected static $stacktrace = true;
	
	private static function init($logLevel = null, $stacktrace = null) {
		if ($logLevel !== null) {
			self::$logLevel = $logLevel;
		}
		if ($stacktrace !== null) {
			self::$stacktrace = $stacktrace;
		}
		if (empty(self::$db)) {
			// $m = new Mongo();
			// self::$db = $m->oauth;
		}
	}

	public static function debug($message, $obj = null) { 
		UWAPLogger::debug('oauth2-lib', $message, $obj);
	}
	public static function info($message, $obj = null) {
		UWAPLogger::info('oauth2-lib', $message, $obj);
	}
	public static function warn($message, $obj = null) {
		UWAPLogger::warn('oauth2-lib', $message, $obj);
	}
	public static function error($message, $obj = null) {
		UWAPLogger::error('oauth2-lib', $message, $obj);
	}
}