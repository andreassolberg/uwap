<?php


class Config {
	
	protected $subid;
	protected $config;
	protected $store;
	protected $basepath;
	
	function __construct($id = null) {

		$this->basepath = '/var/www/appengine/apps';
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
		$listing = $this->store->queryList('appconfig', array(), $fields);
		return $listing;
	}

	public function getAppPath() {
		return $this->basepath . '/' . $this->subid . '/';
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
		$listing = $this->store->queryList('appconfig', array("uwap-userid" => $userid), $fields);

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
		$listing = $this->store->queryList('appconfig', array(), $fields);

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
			'url' => 'https://dav.uwap.org/app/' . $this->subid,
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

	public function getFilestats() {
		$M = (1024*1024);
		$stat = array();
		$stat['capacity'] = ceil(25 * $M);
		$stat['capacityH'] = self::human_filesize($stat['capacity']);

		$f = '/var/www/appengine/apps/' . $this->subid;
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

	public function validateAppConfig($app) {

		if (empty($app['id'])) throw new Exception('Missing parameter [id]');
		if (empty($app['name'])) throw new Exception('Missing parameter [id]');
		if (empty($app['type'])) throw new Exception('Missing parameter [type]');
		if (!in_array($app['type'], array('app', 'proxy'))) throw new Exception('Invalid app type.');

	}

	public function store($config, $userid) {
		$this->validateAppConfig($config);
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
		$obj = $this->config;
		$obj['url'] = 'https://' . $this->subid . '.uwap.org';
		return $obj;
	}

	public function getValue($key, $default = null) {
		if (isset($this->config[$key])) return $this->config[$key];
		return $default;
	}

	public function getHandlerConfig($handler) {

		// echo "getHandlerConfig($handler)"; print_r($this->config);
		if (empty($this->config["handlers"])) return null;
		if (!isset($this->config["handlers"][$handler])) return null;

		$pc = $this->config["handlers"][$handler];
		if ($pc["type"] === "oauth2") {
			$pc["client_credentials"]["redirect_uri"] = 'http://' . $this->subid . '.app.bridge.uninett.no/_/api/callbackOAuth2.php';
		}
		return $pc;
	}
	
}

