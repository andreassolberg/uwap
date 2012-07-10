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


	// ------ ------ ------ ------ Class methods
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

	// TODO: Start using this from GlobalConfig
	public static function hostname() {
		return self::getValue('mainhost', null, true);
	}

	public static function scheme() {
		if(!array_key_exists('HTTPS', $_SERVER)) {
			/* Not a https-request. */
			return 'http';
		}

		if($_SERVER['HTTPS'] === 'off') {
			/* IIS with HTTPS off. */
			return 'http';
		}

		/* Otherwise, HTTPS will be a non-empty string. */
		if ($_SERVER['HTTPS'] !== '') {
			return 'https';
		}
		return 'http';
	}


	
}

