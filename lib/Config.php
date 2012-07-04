<?php


class Config {
	
	protected $subid;
	protected $config;
	protected $store;
	
	protected static $globalConfig = null;

	function __construct($id = null) {


		$this->getGlobalConfig();

		$this->store = new UWAPStore();

		if ($id === false) {
			$this->subid = null;
			return;

		} else if ($id === null) {

			$this->subid = Utils::getSubID();			
		} else {
			$this->subid = $id;
		}

		
		$this->config = $this->store->queryOne('appconfig', array("id" => $this->subid));
		if(empty($this->config)) {
			throw new Exception("Could not find configuration for app.");
		}

		self::initGlobalConfig();

	}

	public static function getPath($path) {
		$base = dirname(dirname(__FILE__));
		return $base . '/' . $path;
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

	public function getAppPath() {
		return self::getPath('apps/' . $this->subid . '/');
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

	public function getDavCredentials($userid = null) {

		if (empty($userid)) {
			$userid = $this->config['uwap-userid'];
		}

		$credentials = array(
			'url' => Config::scheme() . '://dav.' . Config::hostname() . '/' . $this->subid
		);

		$lookup = $this->store->queryOne('davcredentials', array("uwap-userid" => $userid));

		if(empty($lookup)) {
			$this->generateDavCredentials($userid);
		}
		$lookup = $this->store->queryOne('davcredentials', array("uwap-userid" => $userid));

		$credentials['username'] = $lookup['username'];
		$credentials['password'] = $lookup['password'];

		return $credentials;
	}

	/**
	 * Generate random password.
	 * Borrowed from here: http://www.codemiles.com/php-tutorials/generate-password-using-php-t3120.html
	 * @var integer
	 */
	public static function  generateRandpassword($size=12, $power=7) {
	    $vowels = 'aeuy';
	    $randconstant = 'bdghjmnpqrstvz';
	    if ($power & 1) {
	        $randconstant .= 'BDGHJLMNPQRSTVWXZ';
	    }
	    if ($power & 2) {
	        $vowels .= "AEUY";
	    }
	    if ($power & 4) {
	        $randconstant .= '23456789';
	    }
	    if ($power & 8) {
	        $randconstant .= '@#$%';
	    }

	    $Randpassword = '';
	    $alt = time() % 2;
	    for ($i = 0; $i < $size; $i++) {
	        if ($alt == 1) {
	            $Randpassword .= $randconstant[(rand() % strlen($randconstant))];
	            $alt = 0;
	        } else {
	            $Randpassword .= $vowels[(rand() % strlen($vowels))];
	            $alt = 1;
	        }
	    }
	    return $Randpassword;
	}

	public static function generateCleanUsername($userid) {
		$username = preg_replace('/[^a-zA-Z0-9]+/', '_', $userid);
		return $username;
	}

	public function generateDavCredentials($userid) {
		$username = self::generateCleanUsername($userid);
		$password = self::generateRandpassword();
		// echo 'password: ' . $password; exit;
		$credentials = array(
			'uwap-userid' => $userid,
			'username' => $username,
			'password' => $password
		);
		$this->store->store('davcredentials', null, $credentials);
	}

	/**
	 * User to migrate filebased config files to MongoDB Store. Not used anymore...
	 * @param  id $id id of app
	 * @return 
	 */
	public function migrate($id) {
		if ($this->exists($id)) throw new Exception("Already migrated this app config [" . $id . "]");
		$file = dirname(dirname(__FILE__)) . '/config/' . $id . '.js';
		$config = json_decode(file_get_contents($file), true);
		$config["id"] = $id;
		$this->store->store("appconfig", null, $config);
	}

	public static function human_filesize($bytes, $decimals = 2) {
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}

	public function bootstrap($template) {

		$td = Config::getPath('bootstrap/' . $template);
		$ad = Config::getPath('apps/' . $this->subid);

		if (!is_dir($td)) throw new Exception('Could not find bootstrap dir');
		if (!is_dir($ad)) throw new Exception('Could not find application dir');

		$cmd = 'cp -ruT ' . $td . ' ' . $ad;
		error_log("Executing " . $cmd);

		$ret = null;
		$output = null;
		exec($cmd, &$output, &$ret);
		
		return ($ret === 0);
	}

	public function getFilestats() {
		$M = (1024*1024);
		$stat = array();
		$stat['capacity'] = ceil(25 * $M);
		$stat['capacityH'] = self::human_filesize($stat['capacity']);

		$f = Config::getPath('apps/' . $this->subid);
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
		return $stat;
	}

	public function validateAppConfig(&$app) {

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

		$ret = $this->store->update('appconfig',  $userid, $criteria, array('status' => $new));
		if (empty($ret)) {
			throw new Exception('Empty response from update() on storage. Indicates an error occured. Check logs.');;
		}
		return true;
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

		error_log("   updateAuthzHandler --->   id   " . $id);
		error_log("   updateAuthzHandler ---> userid " . $userid);
		error_log("   updateAuthzHandler --->   obj  " . var_export($obj, true));

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
		$this->store->update('appconfig',  $userid, $criteria, $updates);
		return $current['handlers'];
	}

	public function store($config, $userid) {
		$this->validateAppConfig(&$config);
		$config['status'] = array('pendingDAV');
		$id = $config["id"];
		$lookup = $this->store->queryOne('appconfig', array("id" => $id));
		if (!empty($lookup)) {
			throw new Exception('Application ID already exists, cannot create new app with this ID.');
		}
		$this->store->store('appconfig', $userid, $config);
	}

	public function exists($id) {
		$config = $this->store->queryOne('appconfig', array("id" => $id));
		return (!empty($config));
	}

	public function getID() {
		return $this->subid;
	}
	
	public function getConfig() {
		$current = $this->config;
		$current['url'] = Config::scheme() . '://' . $this->subid . '.' . Config::hostname();

		if (!empty($current['handlers'])) {
			foreach($current['handlers'] AS $key => $handler) {
				// if (isset($handler['type']) && $handler['type'] === 'oauth2') {
					$current['handlers'][$key]['redirect_uri'] = Config::oauth2callback($this->getID());
				// }
			}
		}

		if (empty($current['status'])) {
			$current['status'] = array();
		}

		return $current;
	}

	public function getGlobalConfig() {
		self::initGlobalConfig();
		return self::$globalConfig;
	}

	public function getGlobalConfigValue($key, $default = null, $required = false) {
		$config = $this->getGlobalConfig();
		if (isset($config[$key])) return $config[$key];
		if ($required === true) throw new Exception('Missing required global configuration property [' . $key . ']');
		return $default;
	}

	public function getValue($key, $default = null, $required = false) {
		if (isset($this->config[$key])) return $this->config[$key];
		if ($required === true) throw new Exception('Missing app config configuration property [' . $key . ']');
		return $default;
	}

	public static function initGlobalConfig() {
		global $UWAP_BASEDIR;
		if (is_null(self::$globalConfig)) {
			self::$globalConfig = json_decode(file_get_contents($UWAP_BASEDIR . '/config/config.json'), true);
		}
	}

	public static function hostname() {
		self::initGlobalConfig();
		if (!isset(self::$globalConfig['mainhost'])) throw new Exception('Missing global property mainhost');
		return self::$globalConfig['mainhost'];
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

	public static function oauth2callback($id) {
		return Config::scheme() . '://' . $id . '.' . Config::hostname() . '/_/api/callbackOAuth2.php';
	} 

	public function getHandlerConfig($handler) {

		// echo "getHandlerConfig($handler)"; print_r($this->config);
		if (empty($this->config["handlers"])) return null;
		if (!isset($this->config["handlers"][$handler])) return null;

		$pc = $this->config["handlers"][$handler];
		if ($pc["type"] === "oauth2") {
			$pc["redirect_uri"] = self::oauth2callback($this->subid);
		}
		return $pc;
	}
	
}

