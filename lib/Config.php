<?php


class Config {
	
	// Static variables
	protected static $instances = array();

	// object variables
	protected $store;
	protected $subid;
	protected $config;


	/**
	 * Private constructor, use Config::getInstance([id]), instead.
	 * @param [type] $id [description]
	 */
	protected function __construct($id) {
		$this->store = new UWAPStore();
		$this->subid = $id;
		$this->config = $this->store->queryOne('appconfig', array("id" => $this->subid));

		if(empty($this->config)) {
			throw new Exception("Could not find configuration for app [" . $id . "]");
		}
	}


	/**
	 * Public static function get to get an config instance for a specific app.
	 * @var [type]
	 */
	public static function getInstanceFromHost($host = null) {

		if ($host === null) {
			$host = Utils::getHost();
		}
		$id = Utils::getSubID($host);	
		if ($id === false) {
			// Lookup external host from appconfig.
			$id = self::getSubIDfromHost($host);
		}

		if (!array_key_exists($id, self::$instances)) {
			self::$instances[$id] = new self($id);
		}
		return self::$instances[$id];
	}

	/**
	 * Public static function get to get an config instance for a specific app.
	 * @var [type]
	 */
	public static function getInstance($id = null, $host = null) {

		if ($host === null) {
			$host = Utils::getHost();
		}
		if ($id === false) throw new Exception('Deprecated use of Config object.');
		if ($id === null) {
			$id = Utils::getSubID();	
			if ($id === false) {
				$id = self::getSubIDfromHost($host);
			}
		}

		if (!array_key_exists($id, self::$instances)) {
			self::$instances[$id] = new self($id);
		}
		return self::$instances[$id];
	}

	public static function getSubIDfromHost($host) {

		$sid = Utils::getSubID($host);

		$store = new UWAPStore();
		$res = $store->queryOne('appconfig', array('externalhost' => $host));
		if (!empty($res)) {
			return $res['id'];
		}
		throw new Exception('Application configuration does not yet exists for this domain.');
	}




	public function getAppPath($path = '/') {
		return Utils::getPath('apps/' . $this->subid . $path);
	}

	public function getDavCredentials($userid = null) {

		if (empty($userid)) {
			$userid = $this->config['uwap-userid'];
		}

		$credentials = array(
			'url' => GlobalConfig::scheme() . '://dav.' . GlobalConfig::hostname() . '/' . $this->subid
		);

		$lookup = $this->store->queryOne('davcredentials', array("uwap-userid" => $userid));

		if(empty($lookup)) {
			$this->generateDavCredentials($userid);
		}
		$lookup = $this->store->queryOne('davcredentials', array("uwap-userid" => $userid));

		$credentials['username'] = $lookup['username'];

		UWAPLogger::debug('config', 'Got DAVcredentials. (hidden password)', $credentials);

		$credentials['password'] = $lookup['password'];
		return $credentials;
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
		UWAPLogger::info('core-dev', 'Generating new DAV credentials for ' . $username);
		$this->store->store('davcredentials', null, $credentials);
	}

	public function getOAuthClientConfig() {
	
		$id = $this->config['id'];

		$redirect_uri = GlobalConfig::scheme() . '://' . $id . '.' . GlobalConfig::hostname() . '/';
		if (!empty($this->config['externalhost'])) {
			$redirect_uri = GlobalConfig::scheme() . '://' . $this->config['externalhost'] . '/';
		} 

		// echo "REDIRECT URL"  . $redirect_uri; exit;
		
		$scopes = array("app_" . $this->config['id'] . "_user", "userinfo");

		if(!empty($this->config['scopes'])) {
			$scopes = array_merge($scopes, $this->config['scopes']);
		}
		
		return array(
			"client_id" => "app_" . $id,
			"client_name" => $this->config['name'],
			"uwap-userid" => $this->config['uwap-userid'],
			"redirect_uri" => $redirect_uri,
			"scopes" => $scopes,
		);
	}


	public static function human_filesize($bytes, $decimals = 2) {
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}

	public function bootstrap($template) {

		$td = Utils::getPath('bootstrap/' . $template);
		$ad = Utils::getPath('apps/' . $this->subid);

		if (!is_dir($td)) throw new Exception('Could not find bootstrap dir');
		if (!is_dir($ad)) throw new Exception('Could not find application dir');

		$cmd = 'cp -ruT ' . $td . ' ' . $ad;

		$ret = null;
		$output = null;
		exec($cmd, &$output, &$ret);

		UWAPLogger::info('core-dev', 'Bootstrapping application ', array(
			'command' => $cmd,
			'output' => $output,
			'returnvalue' => $ret,
		));
		
		return ($ret === 0);
	}

	public function getFilestats() {
		$M = (1024*1024);
		$stat = array();
		$stat['capacity'] = ceil(25 * $M);
		$stat['capacityH'] = self::human_filesize($stat['capacity']);

		$f = Utils::getPath('apps/' . $this->subid);
		$io = popen ( '/usr/bin/du -sb ' . $f, 'r' );
		$size = fgets ( $io, 4096);
		// $size = substr ( $size, 0, strpos ( $size, ' ' ) );
		if (preg_match('/^([0-9]+)\s/', $size, $matches)) {
			$size = intval($matches[1], 10);
		} else {
			$size = 0;
		}
		pclose ( $io );

		// echo 'io: ' . $size; exit;

		$stat['size'] = $size;
		$stat['sizeH'] = self::human_filesize($size);
		$stat['usage'] = max(1, min(100, floor(100*$stat['size'] / $stat['capacity'])));
		return $stat;
		// echo 'Directory: ' . $f . ' => Size: ' . $size;
	}

	public function getUserStats() {
		$stats = array(
			'count' => $this->store->count('consent', array('app' => $this->subid))
		);
		UWAPLogger::debug('core-dev', 'Get user statistics ', $stats);
		return $stats;
	}

	public function getStats() {
		$stat = $this->store->getStats($this->subid);
		if (!empty($stat)) {
			$M = (1024*1024);
			$stat['capacity'] = ceil(0.3 * $M);
			$stat['capacityH'] = self::human_filesize($stat['capacity']);

			if (isset($stat['storageSize'])) {
				$stat['storageSizeH'] = self::human_filesize($stat['storageSize']);
			}
			if (isset($stat['size'])) {
				$stat['sizeH'] = self::human_filesize($stat['size']);
				$stat['usage'] = min(100, floor(100*$stat['size'] / $stat['capacity']));

			}
		}
		UWAPLogger::debug('core-dev', 'Get statistics for app ', $stat);
		return $stat;
	}


	public function hasStatus($statuses) {

		if (empty($statuses)) return true;
		if (empty($this->config['status'])) return false;
		foreach($statuses AS $s) {
			if (!in_array($s, $this->config['status'])) return false;
		}
		return true;
	}

	public function updateStatus($update, $userid = null) {

		$current = $this->getConfig();
		$new = array();

		foreach($current['status'] AS $candidate) {
			if (!array_key_exists($candidate, $update)) {
				$new[] = $candidate;
			} else if ($update[$candidate] === true) {
				unset($update[$candidate]);
				$new[] = $candidate;
			} else if ($update[$candidate] === false) {
				unset($update[$candidate]);
			} else {
				throw new Exception('Invalid status update defintion.');
			}
		}
		foreach($update AS $k => $v) {
			if ($v === true) {
				$new[] = $k;
			}
		}

		$this->config['status'] = $new;
		$criteria = array('id' => $this->config['id']);

		UWAPLogger::info('core-dev', 'Updating application status configuration', array(
			'criteria' => $criteria,
			'new_status' => $new,
		));

		$ret = $this->store->update('appconfig',  $userid, $criteria, array('status' => $new));
		if (empty($ret)) {
			throw new Exception('Empty response from update() on storage. Indicates an error occured. Check logs.');;
		}
		return true;
	}



	public function updateProxy($proxy, $userid) {
		$current = $this->getConfig();

		$allowedFields = array(
			'endpoints', 'scopes', 'token_val', 'token_hdr', 'type', 'user', 'policy'
		);
		// foreach($proxies AS $key => $proxy) {
			// if (!preg_match('/^[a-z0-9]+$/', $key)) {
			// 	unset($proxies[$key]); continue;
			// }
			foreach($proxy AS $k => $v) {
				if (!in_array($k, $allowedFields)) {
					unset($proxy[$k]);
				}
			}
		// }

		// if(empty($current['proxies'])) {
		// 	$current['proxies'] = array();
		// }
		$this->config['proxy'] = $proxy;

		$criteria = array('id' => $this->config['id']);

		$updates = array('proxy' => $proxy);

		UWAPLogger::info('core-dev', 'Updating proxy configuration', array(
			'userid' => $userid,
			'obj' => $proxy,
			'updates' => $updates,
		));

		// update($collection, $userid, $criteria, $updates)
		$ret = $this->store->update('appconfig',  $userid, $criteria, $updates);

		if (empty($ret)) {
			throw new Exception('Empty response from update() on storage. Indicates an error occured. Check logs.');;
		}

		return $proxy;
	}

	public function updateAuthzHandler($id, $obj, $userid) {
		$current = $this->getConfig();

		$allowedFields = array(
			'id', 'title', 'type', 
			'authorization', 'token', 'request', 'authorize', 'access', 'client_id', 'client_user', 'client_secret', 'token_hdr', 'token_val',
			'defaultscopes', 'defaultexpire', 'tokentransport'
		);
		foreach($obj AS $k => $v) {
			if (!in_array($k, $allowedFields)) {
				unset($obj[$k]);
			}
		}


		if(empty($current['handlers'])) {
			$current['handlers'] = array();
		}
		$current['handlers'][$id] = $obj;

		$criteria = array('id' => $this->config['id']);

		$updates = array('handlers' => $current['handlers']);

		UWAPLogger::info('core-dev', 'Updating authorization handler', array(
			'id' => $id,
			'userid' => $userid,
			'obj' => $obj,
			'updates' => $updates,
		));

		// update($collection, $userid, $criteria, $updates)
		$ret = $this->store->update('appconfig',  $userid, $criteria, $updates);

		if (empty($ret)) {
			throw new Exception('Empty response from update() on storage. Indicates an error occured. Check logs.');;
		}

		return $current['handlers'];
	}

	public function deleteAuthzHandler($id, $userid) {
		$current = $this->getConfig();
		if(empty($current['handlers'])) {
			$current['handlers'] = array();
		}
		unset($current['handlers'][$id]);
		$criteria = array('id' => $this->config['id']);
		$updates = array('handlers' => $current['handlers']);

		UWAPLogger::info('core-dev', 'Deleting authorization handler', array(
			'criteria' => $criteria,
			'updates' => $updates,
		));

		$this->store->update('appconfig',  $userid, $criteria, $updates);
		return $current['handlers'];
	}



	public static function validateAppConfig(&$app) {

		if (empty($app['id'])) throw new Exception('Missing parameter [id]');
		if (empty($app['name'])) throw new Exception('Missing parameter [name]');
		if (empty($app['type'])) throw new Exception('Missing parameter [type]');
		if (!in_array($app['type'], array('app', 'proxy'))) throw new Exception('Invalid app type.');

		$allowedFields = array(
			'id', 'name', 'type'
		);
		foreach($app AS $k => $v) {
			if (!in_array($k, $allowedFields)) {
				unset($app[$k]);
			}
		}
	}
	
	public static function store($config, $userid) {
		$store = new UWAPStore();
		self::validateAppConfig(&$config);
		$config['status'] = array('pendingDAV');
		$id = $config["id"];
		$lookup = $store->queryOne('appconfig', array("id" => $id));
		if (!empty($lookup)) {
			throw new Exception('Application ID already exists, cannot create new app with this ID.');
		}
		UWAPLogger::info('core-dev', 'Store application configuration', array(
			'userid' => $userid,
			'id' => $id,
 			'config' => $config,
		));
		$store->store('appconfig', $userid, $config);
	}

	// public function storeOld($config, $userid) {
	// 	$this->validateAppConfig(&$config);
	// 	$config['status'] = array('pendingDAV');
	// 	$id = $config["id"];
	// 	$lookup = $this->store->queryOne('appconfig', array("id" => $id));
	// 	if (!empty($lookup)) {
	// 		throw new Exception('Application ID already exists, cannot create new app with this ID.');
	// 	}
	// 	UWAPLogger::info('core-dev', 'Store application configuration', array(
	// 		'userid' => $userid,
	// 		'id' => $id,
 // 			'config' => $config,
	// 	));
	// 	$this->store->store('appconfig', $userid, $config);
	// }


	public function getID() {
		return $this->subid;
	}
	
	public function getHostname() {
		if (!empty($this->config['externalhost'])) {
			return $this->config['externalhost'];
		}
		return $this->subid . '.' . GlobalConfig::hostname();
	}

	public function getPolicy() {
		if (isset($this->config['policy']) && isset($this->config['policy']['auto'])) {
			return $this->config['policy']['auto'];
		}
		return false;
	}

	public function getScopePolicy($scope) {
		if (isset($this->config['proxy']) && 
			isset($this->config['proxy']['scopes']) && 
			isset($this->config['proxy']['scopes'][$scope])) {

			return $this->config['proxy']['scopes'][$scope]['auto'];
		}
		return false;
	}

	public function scopePolicyAccept($scope = null) {
		if ($scope === null) return $this->getPolicy();
		return $this->getScopePolicy($scope);
	}

	public function getConfigLimited() {

		$result = array();

		$current = $this->config;
		$hostname = $this->getHostname();
		$current['url'] = GlobalConfig::scheme() . '://' . $hostname;

		if (empty($current['status'])) {
			$current['status'] = array();
		}

		$parameters = array('id', 'type', 'descr', 'name', 'url', 'uwap-userid');


		if ($current['type'] === 'proxy') {

			$result['oauth'] = array(
				'authorization' => GlobalConfig::scheme() . '://core.' . GlobalConfig::hostname() . '/api/oauth/authorization',
				'token' => GlobalConfig::scheme() . '://core.' . GlobalConfig::hostname() . '/api/oauth/token',
			);
		}

		foreach($parameters AS $p) {
			if (isset($current[$p])) $result[$p] = $current[$p];
		}
		if (isset($current['proxy'])) {
			$result['proxy'] = array();
			if (isset($current['proxy']['scopes'])) {
				$result['proxy']['scopes'] = $current['proxy']['scopes'];
			}
			if (isset($current['proxy']['policy'])) {
				$result['proxy']['policy'] = $current['proxy']['policy'];
			}
		}


		return $result;
	}

	public function getConfig() {
		$current = $this->config;
		$hostname = $this->getHostname();
		$current['url'] = GlobalConfig::scheme() . '://' . $hostname;

		if (!empty($current['handlers'])) {
			foreach($current['handlers'] AS $key => $handler) {
				if (isset($handler['type']) && $handler['type'] === 'oauth2') {
					$current['handlers'][$key]['redirect_uri'] = self::oauth2callback($this->getID(), $key);
				}
			}
		}

		if (empty($current['status'])) {
			$current['status'] = array();
		}

		UWAPLogger::debug('config', 'Loaded configuration', $current);

		return $current;
	}

	public static function oauth2callback($id, $handler) {
		return GlobalConfig::scheme() . '://' . $id . '.' . GlobalConfig::hostname() . '/_/oauth2callback/' . $handler;
	} 



	public function getHandlerConfig($handler) {

		// echo "getHandlerConfig($handler)"; print_r($this->config);
		if (empty($this->config["handlers"])) return null;
		if (!isset($this->config["handlers"][$handler])) return null;

		$pc = $this->config["handlers"][$handler];

		if (isset($pc['type']) && $pc['type'] === 'oauth2') {
			$pc['redirect_uri'] = self::oauth2callback($this->getID(), $handler);
		}

		// $pc = $this->config["handlers"][$handler];
		return $pc;
	}


	public function _getValue($key, $default = null, $required = false) {
		if (isset($this->config[$key])) return $this->config[$key];
		if ($required === true) throw new Exception('Missing app config configuration property [' . $key . ']');
		return $default;
	}

	public static function getValue($key, $default = null, $required = false) {
		$config = self::getInstance();
		return $config->_getValue($key, $default, $required);
	}


	public function requireOwner($userid) {
		if ($this->config['uwap-userid'] !== $userid) {
			throw new Exception('Operation not allowed. You are not the owner of this app.');
		}

	}




	
}

