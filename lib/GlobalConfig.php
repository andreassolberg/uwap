<?php


class GlobalConfig {

	protected static $instance = null;
	protected $config;

	private function __construct() {
		global $UWAP_BASEDIR;
		$this->config = json_decode(file_get_contents($UWAP_BASEDIR . '/config/config.json'), true);
	}

	// ------ ------ ------ ------ Object methods
	public function get() {
		return $this->config;
	}

	public function _getValue($key, $default = null, $required = false) {
		if (isset($this->config[$key])) return $this->config[$key];
		if ($required === true) throw new Exception('Missing required global configuration property [' . $key . ']');
		return $default;
	}



	// public static function getAppID() {


	// 	return Config::getSubIDfromHost($host);
	// }




	// ------ ------ ------ ------ Class methods

	public static function getApp($host = null) {

		$id = Utils::getSubID($host);

		if (empty($host)) {
			$host = Utils::getHost();	
		}

		if ($id === null) {
			$id = ClientDirectory::getSubIDfromHost( $host );
		}

		if ($id === null) {
			throw new Exception('Could not obtain an app configuration for the current host [' . $host . ']');
		}

		return Client::getByID($id);
	}


	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new GlobalConfig();
		}
		return self::$instance;
	}

	public static function getValue($key, $default = null, $required = false) {
		$config = self::getInstance();
		return $config->_getValue($key, $default, $required);
	}

	public static function hostname() {
		return self::getValue('mainhost', null, true);
	}


	
}

