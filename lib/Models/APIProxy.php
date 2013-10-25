<?php

class APIProxy extends App {
	

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
		'externalhost',
		'proxy'
		);



	public function __construct($properties) {

		if (!isset($properties['status'])) {
			$properties['status'] = array('operational');
		}

		parent::__construct($properties);

	}

	/**
	 * Set the proxy property for the APIProxy object. This contains all the proxyrelated properties of a
	 * APIProxy app. Such as endpoints, scopes, policy, and trust to source API.
	 * @param  [type] $proxy [description]
	 * @return [type]        [description]
	 */
	public function updateProxy($proxy) {


		$allowedFields = array(
			'endpoints', 'scopes', 'token_val', 'token_hdr', 'type', 'user', 'policy'
		);

		foreach($proxy AS $k => $v) {
			if (!in_array($k, $allowedFields)) {
				unset($proxy[$k]);
			}
		}

		$this->set('proxy', $proxy);
		$this->store(array('proxy'));
		return $proxy;
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