<?php


class GlobalConfig extends Config {

	protected static $instance = null;

	public function __construct($properties) {
		parent::__construct($properties);
	}

	// ------ ------ ------ ------ Object methods




	// ------ ------ ------ ------ Class methods


	public static function getBaseURL($app = 'api') {
		return self::getValue('scheme', 'https') . '://' . $app . '.' . GlobalConfig::hostname() . '/';
	}



	/**
	 * The way to load a global config object.
	 * 
	 * @return [type] [description]
	 */
	public static function getInstance() {

		if (!is_null(self::$instance)) {
			return self::$instance;
		}

		global $UWAP_BASEDIR;
		$configFilename = $UWAP_BASEDIR . '/etc/config.json';
		if (!file_exists($configFilename)) {
			throw new Exception('Could not find config file ' . $configFilename);
		}
		$config = json_decode(file_get_contents($configFilename), true);

		self::$instance = new GlobalConfig($config);
		return self::$instance;
	}



	public static function getApp($host = null, $prefix = null ) {

		$id = Utils::getSubID($host, $prefix);

		// echo "\n\nGet sub id was " . $id . "\n"; exit;

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


	public static function getValue($key, $default = null, $required = false) {
		$config = self::getInstance();
		return $config->get($key, $default, $required);
	}

	public static function hostname() {
		return self::getValue('mainhost', null, true);
	}


	
}

