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

		/*
		 * Now get clients that are owner by the user.
		 */
		$fields = array(
			'client_id' => true,
			'client_name' => true,
			'scopes' => true,
			'scopes_requested' => true,
			'uwap-userid' => true,
		);
		$query = array(
			"uwap-userid" => $userid
		);
		$listing = $this->store->queryList('oauth2-server-clients',$query, $fields);
		foreach($listing AS $k => $v) {
			$v['type'] = 'client';
			$v['name'] = $v['client_name'];
			$v['id'] = $v['client_id'];
			$sorted['client'][] = $v;
		}


		return $sorted;
	}

	public function queryApps($query, $userid) {

		$fields = array(
			'id' => true,
			'name' => true,
			'descr' => true,
			'type' => true,
			'uwap-userid' => true,
			'proxy' => true,
		);

		$search = $query['search'];

		$query = array(
			"type" => "proxy",
			'$or' => array(
				array("name" => array('$regex' => $search)),
				array("descr" => array('$regex' => $search))
			),
			"status" => array(
				'$ne' => "pendingDelete"
			)
		);

		$listing = $this->store->queryList('appconfig',$query, $fields);

		foreach($listing AS $k => $item) {
			if (isset($listing[$k]['proxy'])) {
				if (isset($listing[$k]['proxy']['scopes'])) {
					$listing[$k]['scopes'] = $listing[$k]['proxy']['scopes'];
				}
				unset($listing[$k]['proxy']);
			}
		}

		return $listing;

	}


	public function getClient($appid, $userid) {

		$fields = array(
			'client_id' => true,
			'client_name' => true,
			'scopes' => true,
			'scopes_requested' => true,
			'uwap-userid' => true,
		);
		$query = array(
			"client_id" => $appid,
			"uwap-userid" => $userid
		);
		return $this->store->queryOne('oauth2-server-clients', $query, array());

	}


	public static function array_add_unique(&$array, $newelement) {
		$foundElement = false;
		$uniques = array($newelement => 1);
		foreach($array AS $i) {
			$uniques[$i] = 1;
		}
		return array_keys($uniques);
	}


	public function addClientScopes($appid, $userid, $scopes) {
		$fields = array(
			'client_id' => true,
			'client_name' => true,
			'scopes' => true,
			'scopes_requested' => true,
			'uwap-userid' => true,
		);

		$appscope = "rest_" . $appid;
		$appregexmatch = "rest_" . $appid . "($|[_])";
		$query = array(
			"client_id" => $appid,
			"uwap-userid" => $userid
		);
		$client = $this->store->queryOne('oauth2-server-clients', $query, array());

		if (empty($client)) throw new Exception('Cannot find client' . var_export($query, true));


		$updates = array('scopes' => array(), 'scopes_requested' => array());
		if (isset($client['scopes'])) $updates['scopes'] = $client['scopes'];
		if (isset($client['scopes_requested'])) $updates['scopes_requested'] = $client['scopes_requested'];

		foreach($scopes AS $scope) {

			$autoaccept = array('userinfo' => 1, 'longterm' => 1);

			if (preg_match('/rest_([a-z0-9\-]+)(_([a-z0-9\-]+))?/', $scope, $matches)) {

				$appid = $matches[1];
				Utils::validateID($appid);
				$proxy = Config::getInstance($appid);

				$s = null;
				if (isset($matches[2])) {
					$s = $matches[2];
				}
				
				if ($proxy->scopePolicyAccept($s)) {
					$updates['scopes'] = self::array_add_unique($updates['scopes'], $scope);
				} else {
					$updates['scopes_requested'] = self::array_add_unique($updates['scopes_requested'], $scope);
				}
				

			} else if (preg_match('/app_([a-z0-9\-]+)_user/', $scope, $matches)) {

				$updates['scopes_requested'] = self::array_add_unique($updates['scopes_requested'], $scope);

			} else {

				if (isset($autoaccept[$scope])) {
					$updates['scopes'] = self::array_add_unique($updates['scopes'], $scope);
				} else {
					$updates['scopes_requested'] = self::array_add_unique($updates['scopes_requested'], $scope);
				}

			}


		}
		$ret = $this->store->update('oauth2-server-clients',  null, $query, $updates);



		return $this->store->queryOne('oauth2-server-clients', $query, array());

	}


	public function removeClientScopes($appid, $userid, $removeScopes) {
		$fields = array(
			'client_id' => true,
			'client_name' => true,
			'scopes' => true,
			'scopes_requested' => true,
			'uwap-userid' => true,
		);


		$query = array(
			"client_id" => $appid,
			"uwap-userid" => $userid
		);
		$client = $this->store->queryOne('oauth2-server-clients', $query, array());
		if (empty($client)) throw new Exception('Cannot find client' . var_export($query, true));


		$updates = array('scopes' => array(), 'scopes_requested' => array());

		$scopesI = array();
		if (isset($client['scopes'])) {
			foreach($client['scopes'] AS $s) {
				$scopesI[$s] = 1;
			}
		}
		foreach($removeScopes AS $s) {
			if (isset($scopesI[$s])) { unset($scopesI[$s]); }
		}
		$updates['scopes'] = array_keys($scopesI);


		$rscopesI = array();
		if (isset($client['scopes_requested'])) {
			foreach($client['scopes_requested'] AS $s) {
				$rscopesI[$s] = 1;
			}
		}
		foreach($removeScopes AS $s) {
			if (isset($rscopesI[$s])) unset($rscopesI[$s]);
		}
		$updates['scopes_requested'] = array_keys($rscopesI);
		


		$ret = $this->store->update('oauth2-server-clients',  null, $query, $updates);


		
		return $this->store->queryOne('oauth2-server-clients', $query, array());

	}

	public function getClients($appid, $userid) {

		/**
		 * TODO
		 * The queries that pulls scopes that starts with should later be optimalized..
		 * May be add a list of relevant apps in an appconfig of clients, both requested and accepted scope references.
		 *
		 * Also, this now only pulls data for REST apps. Should also pull data from real apps.
		 */

		$fields = array(
			'client_id' => true,
			'client_name' => true,
			'scopes' => true,
			'scopes_requested' => true,
			'uwap-userid' => true,
		);

		$appscope = "rest_" . $appid;
		$appregexmatch = "rest_" . $appid . "($|[_])";
		$query = array(
			'$or' => array(
				array(
					"scopes" => array('$regex' => $appregexmatch)
				),
				array(
					"scopes_requested" => array('$regex' => $appregexmatch)
				),
			),

		);
		$listing = $this->store->queryList('oauth2-server-clients', $query, $fields);

		// Pulling applications that have reqested scope for this REST is not yet available...
		/*
		$query = array(
			"type" => "app",
			"status" => array(
				'$ne' => "pendingDelete",
			),
			'$or' => array(
				array(
					"scopes" => array('$regex' => $appregexmatch)
				),
				array(
					"scopes_requested" => array('$regex' => $appregexmatch)
				),
			),

		);
		 */

		foreach($listing AS $i => $item) {
			if(isset($item['scopes'])) {
				$listing[$i]['scopes'] = self::filterScopeWithPrefix($appscope, $item['scopes']);
			}
			if(isset($item['scopes_requested'])) {
				$listing[$i]['scopes_requested'] = self::filterScopeWithPrefix($appscope, $item['scopes_requested']);
			}
		}


		$data = array('clients' => $listing);
		// foreach($listing AS $item) {
		// 	if (isset($item['scopes']) && in_array($appscope, $item['scopes'])) {
		// 		$data['clients'][] = $item;
		// 	} else {
		// 		$data['clients-pending'][] = $item;
		// 	}
		// }

		return $data;

		// $sorted = array("app" => array(), "proxy" => array(), "client" => array());
		// foreach($listing AS $e) {
		// 	if (isset($e['type']) && isset($sorted[$e['type']])) {
		// 		$sorted[$e['type']][] = $e;
		// 	}
		// }
		// return $sorted;
	}

	/*
	 * Takes an array of scope strings as input and returns only the subset of
	 * scopes that matches a given prefix.
	 */
	public static function filterScopeWithPrefix($prefix, $scopes) {
		$results = array();
		if (empty($scopes)) return $results;
		foreach($scopes AS $scope) {
			if (!strncmp($scope, $prefix, strlen($prefix))) {
				$results[] = $scope;
			}
		}
		return $results;
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


	public static function validateClientConfig(&$app) {
		if (empty($app['client_id'])) throw new Exception('Missing parameter [id]');
		if (empty($app['client_name'])) throw new Exception('Missing parameter [client_name]');
		if (empty($app['type'])) throw new Exception('Missing parameter [type]');

		$app['type'] = 'client';

		$allowedFields = array(
			'client_id', 'client_name', 'client_secret', 'type', 'descr', 'redirect_uri'
		);

		foreach($app AS $k => $v) {
			if (!in_array($k, $allowedFields)) {
				unset($app[$k]);
			}
		}

	}

	public static function validateAppConfig(&$app) {

		if (empty($app['id'])) throw new Exception('Missing parameter [id]');
		if (empty($app['name'])) throw new Exception('Missing parameter [name]');
		if (empty($app['type'])) throw new Exception('Missing parameter [type]');

		if (!in_array($app['type'], array('app', 'proxy'))) throw new Exception('Invalid app type.');

		$allowedFields = array(
			'id', 'name', 'type', 'descr', 'proxy', 'client_secret'
		);

		foreach($app AS $k => $v) {
			if (!in_array($k, $allowedFields)) {
				unset($app[$k]);
			}
		}

		if (isset($app['proxy'])) {
			// if (!is_array($app['proxies'])) throw new Exception('proxies properties needs to be an object (array)');
			// foreach($app['proxies'] AS $key => $proxy) {
			// 	Utils::validateID($key);
			// 	if (!is_array($proxy)) throw new Exception('Proxy config must be an object in new proxy config.');
			// }

		}

	}

	public function authorizeClient($appid, $clientid, $authz) {



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
		return $this->store->store('appconfig', $userid, $config);

	}

	public function storeClient($config, $userid) {

		self::validateClientConfig(&$config);

		$config['status'] = array('operational');

		$id = $config["client_id"];

		// if ($this->exists($id)) {
		// 	throw new Exception('Application ID already exists, cannot create new app with this ID.');
		// }

		UWAPLogger::info('core-dev', 'Store application configuration', array(
			'userid' => $userid,
			'id' => $id,
			'config' => $config,
		));
		return $this->store->store('oauth2-server-clients', $userid, $config);

	}
	
}

