<?php

/**
 * AppDirectory allows you to get information about applications.
 * Used by dev and store.
 */
class AppDirectory {

	protected $store;
	
	public function __construct() {
		$this->store = new UWAPStore();
	}

	/**
	 * Get a list of all available applications. Does not required authenticated user.
	 * Only a subset of the configuration will be included, intended for public display.
	 * @return array List of app config objects.
	 */
	public function getAppListing() {
		$fields = array(
			'id' => true,
			'name' => true,
			'descr' => true,
			'logo' => true,
			'type' => true,
			'owner-userid' => true,
			'owner' => true,
			'name' => true,
		);
		$listing = $this->store->queryList('appconfig', array('status' => 'listing'), $fields);
		return $listing;
	}

	public function generateDavCredentials($userid) {
		$username = Utils::generateCleanUsername($userid);
		$password = Utils::generateRandpassword();
		// echo 'password: ' . $password; exit;
		$credentials = array(
			'uwap-userid' => $userid,
			'username' => $username,
			'password' => $password
		);
		$this->store->store('davcredentials', null, $credentials);
	}



	public function getMyApps($userid) {
		$fields = array(
			'id' => true,
			'name' => true,
			'descr' => true,
			'type' => true,
			'owner-userid' => true,
			'owner' => true,
			'name' => true,
			'proxies' => true,
		);

		$query = array(
			"uwap-userid" => $userid,
			"status" => array(
				'$ne' => "pendingDelete"
			),
		);

		$listing = $this->store->queryList('appconfig',$query, $fields);

		$sorted = array("app" => array(), "proxy" => array(), "client" => array());
		foreach($listing AS $e) {
			if (isset($e['type']) && isset($sorted[$e['type']])) {
				$sorted[$e['type']][] = $e;
			}
		}
		return $sorted;
	}

	public function getClients($appid, $userid) {
		$fields = array(
			// 'id' => true,
			// 'name' => true,
			// 'descr' => true,
			// 'type' => true,
			// 'owner-userid' => true,
			// 'owner' => true,
			// 'name' => true,
			// 'proxies' => true,
		);

		$query = array(
			"uwap-userid" => $userid,
			"status" => array(
				'$ne' => "pendingDelete"
			),
		);

		$listing = $this->store->queryList('appconfig',$query, $fields);

		$sorted = array("app" => array(), "proxy" => array(), "client" => array());
		foreach($listing AS $e) {
			if (isset($e['type']) && isset($sorted[$e['type']])) {
				$sorted[$e['type']][] = $e;
			}
		}
		return $sorted;
	}

	public function getMyAppIDs($userid) {
		$fields = array(
			'id' => true
		);

		$query = array(
			"uwap-userid" => $userid,
			"status" => array(
				'$ne' => "pendingDelete"
			),
		);

		$listing = $this->store->queryList('appconfig',$query, $fields);

		$res = array();
		foreach($listing AS $e) {
			$res[] = $e['id'];
		}
		return $res;
	}

	public function getAllApps() {
		$fields = array(
			'id' => true,
			'name' => true,
			'descr' => true,
			'type' => true,
			'owner-userid' => true,
			'owner' => true,
			'name' => true,
		);
		$query = array(
			"status" => array(
				'$ne' => "pendingDelete"
			),
		);
		$listing = $this->store->queryList('appconfig', $query, $fields);

		$sorted = array("app" => array(), "proxy" => array(), "client" => array());
		foreach($listing AS $e) {
			if (isset($e['type']) && isset($sorted[$e['type']])) {
				$sorted[$e['type']][] = $e;
			}
		}
		return $sorted;
	}


	// TODO: Start using this from config.... moved here.
	public function exists($id) {
		$config = $this->store->queryOne('appconfig', array("id" => $id));
		return (!empty($config));
	}

	public static function validateAppConfig(&$app) {

		if (empty($app['id'])) throw new Exception('Missing parameter [id]');
		if (empty($app['name'])) throw new Exception('Missing parameter [name]');
		if (empty($app['type'])) throw new Exception('Missing parameter [type]');
		if (!in_array($app['type'], array('app', 'proxy'))) throw new Exception('Invalid app type.');

		$allowedFields = array(
			'id', 'name', 'type', 'descr', 'proxies'
		);
		foreach($app AS $k => $v) {
			if (!in_array($k, $allowedFields)) {
				unset($app[$k]);
			}
		}

		if (isset($app['proxies'])) {
			if (!is_array($app['proxies'])) throw new Exception('proxies properties needs to be an object (array)');
			foreach($app['proxies'] AS $key => $proxy) {
				Utils::validateID($key);
				if (!is_array($proxy)) throw new Exception('Proxy config must be an object in new proxy config.');

			}
		}

	}


	public function store($config, $userid) {

		self::validateAppConfig(&$config);

		if ($config['type'] === 'app') {
			$config['status'] = array('pendingDAV');
		} else {
			$config['status'] = array('operational');
		}

		$id = $config["id"];

		if ($this->exists($id)) {
			throw new Exception('Application ID already exists, cannot create new app with this ID.');
		}

		UWAPLogger::info('core-dev', 'Store application configuration', array(
			'userid' => $userid,
			'id' => $id,
 			'config' => $config,
		));
		$this->store->store('appconfig', $userid, $config);
	}


	
}

