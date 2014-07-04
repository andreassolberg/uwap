<?php

class App extends HostedService {

/*
	'id' => true,
	'name' => true,
	'descr' => true,
	'logo' => true,
	'type' => true,
	'owner-userid' => true,
	'owner' => true,
	'name' => true,
 */


	protected static $validProps = array(
		'id', 'name', 'descr', 'type', 'uwap-userid', 'handlers', 'status', 'scopes', 'scopes_requested',
		'path',
		'externalhost'
		);


	public function __construct($properties, $stored = false) {

		if (!isset($properties['status'])) {
			$properties['status'] = array('pendingDAV');
		}

		parent::__construct($properties, $stored);

	}


	public function get($key, $default = '____NA') {
		if ($key === 'redirect_uri') {
			return Utils::getScheme() . '://' . $this->getHost() . '/';
		}
		return parent::get($key, $default);
	}



	public function hasStatus($statuses) {
		if (empty($statuses)) return true;
		$s = $this->get('status', array());
		// echo '<pre>';
		// print_r($this);
		// echo " Has these statuses "; print_r($s);
		// echo " requires these statuses "; print_r($statuses);

		foreach($statuses AS $checkStatus) {
			if (!in_array($checkStatus, $s)) return false;
		}
		return true;
	}

	public function getAppPath($path = '/') {


		$pathindexes = array(
			'user' => 'userapps/',
			'core' => 'appengine/apps/',
		);

		$pathindex = $this->get('path', 'user');
		if (!array_key_exists($pathindex, $pathindexes)) {
			throw new Exception('Could not locate the location of this app. Local path index [' . $pathindex . '] is not valid.');
		}


		return Utils::getPath($pathindexes[$pathindex] . $this->get('id') . $path);
	}

	public function getJSON($opts = array()) {

		$props = self::$validProps;
		if (isset($opts['type']) && $opts['type'] === 'basic') {
			$props = array('id', 'name', 'type', 'descr', 'uwap-userid');
		}

		$ret = array();
		foreach($props AS $p) {
			if (isset($this->properties[$p])) {
				$ret[$p] = $this->properties[$p];
			}
		}


		// Fill-ins 
		
		if (isset($opts['appinfo']) && $opts['appinfo']) {
			$hostname = $this->getHost();
			$ret['url'] = Utils::getScheme() . '://' . $hostname . '/';
		}


		return $ret;
	}






}