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

		// header('Content-Type: text/plain'); print_r($properties); exit;

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


	/**
	 * Get generic access policy- true or false for whether to accept new clients.
	 * @return [type] [description]
	 */
	protected function getPolicy() {
		if (isset($this->properties['policy']) && isset($this->properties['policy']['auto'])) {
			return $this->properties['policy']['auto'];
		}
		return false;
	}

	/**
	 * Get a boolean response to a local scope $scope, which cannot be nulll.
	 * @param  [type] $scope [description]
	 * @return [type]        [description]
	 */
	protected function getScopePolicy($scope) {
		if (isset($this->properties['proxy']) && 
			isset($this->properties['proxy']['scopes']) && 
			isset($this->properties['proxy']['scopes'][$scope]) &&
			isset($this->properties['proxy']['scopes'][$scope]['policy']) && 
			isset($this->properties['proxy']['scopes'][$scope]['policy']['auto'])
			) {

			return $this->properties['proxy']['scopes'][$scope]['policy']['auto'];
		}
		return false;
	}

	/**
	 * Get a boolean response to whether the localScope $scope is automatically accepted or not
	 * If [null] then generic scope, like rest_studweb is implicit.
	 * @param  [type] $scope [description]
	 * @return [type]        [description]
	 */
	public function scopePolicyAccept($scope = null) {
		if ($scope === null) return $this->getPolicy();
		return $this->getScopePolicy($scope);
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

		if (isset($opts['type']) && $opts['type'] === 'basic') {
			if (isset($this->properties['proxy']) && isset($this->properties['proxy']['scopes'])) {
				$ret['proxy'] = array(
					'scopes' => $this->properties['proxy']['scopes'],
				);
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