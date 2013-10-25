<?php

class Client extends StoredModel {
	
/*
			'client_id' => true,
			'client_name' => true,
			'scopes' => true,
			'scopes_requested' => true,
			'uwap-userid' => true,
 */

	protected static $collection = 'clients';
	protected static $primaryKey = 'id';

	protected static $validProps = array(
		'id', 'name', 'descr', 'type', 'uwap-userid', 'handlers', 'status', 'scopes', 'scopes_requested', 'logo',
		'redirect_uri', 
		);


	public function __construct($properties) {

		if (!isset($properties['status'])) {
			$properties['status'] = array('operational');
		}

		if (!isset($properties['id'])) {
			$properties['id'] = Utils::genID();
		}

		if (!isset($properties['client_secret'])) {
			$properties['client_secret'] = Utils::genID();
		}

		parent::__construct($properties);

	}

	/**
	 * updateStatus adds and removes statuses
	 * @param  [type] $update This parameter, is a set of properties, 
	 *                        with true for add and false for remove
	 *                        {
	 *                        	"pendingDelete": true,
	 *                        	"operational": false
	 *                        }
	 * @return [type]         [description]
	 */
	public function updateStatus($update) {

		$currentStatus = $this->get('status');
		$newStatus = array();

		foreach($currentStatus AS $candidate) {

			// Keep this entry
			if (!array_key_exists($candidate, $update)) {
				$newStatus[] = $candidate;

			// Supposed to add an entry that was already added
			} else if ($update[$candidate] === true) {
				unset($update[$candidate]);
				$newStatus[] = $candidate;

			// Removing an entry that was present.
			} else if ($update[$candidate] === false) {
				unset($update[$candidate]);

			// Throw an exception if value is not a boolean.
			} else {
				throw new Exception('Invalid status update defintion.');
			}
		}

		// Add the rest of the entries.
		foreach($update AS $k => $v) {
			if ($v === true) {
				$newStatus[] = $k;
			}
		}

		
		$this->set('status', $newStatus);
		$this->store(array('status'));
	}



	/**
	 * Update an authorization handler with a given ID
	 * If not exists from before, add a new with this id.
	 * 
	 * @param  [type] $id  [description]
	 * @param  [type] $obj [description]
	 * @return [type]      [description]
	 */
	public function updateAuthzHandler($id, $obj) {
		

		$allowedFields = array(
			'id', 'title', 'type', 
			'authorization', 'token', 'request', 'authorize', 'access', 'client_id', 
			'client_user', 'client_secret', 'token_hdr', 'token_val',
			'defaultscopes', 'defaultexpire', 'tokentransport'
		);
		foreach($obj AS $k => $v) {
			if (!in_array($k, $allowedFields)) {
				unset($obj[$k]);
			}
		}

		$currentHandlers = $this->get('handlers', array());

		// echo "About to update handlers " . $id;
		// print_r($obj);
		// print_r($currentHandlers);


		Utils::validateID($id);
		$currentHandlers[$id] = $obj;

		$this->set('handlers', $currentHandlers);

		UWAPLogger::info('core-dev', 'Updating authorization handler', array(
			'id' => $id,
			'obj' => $obj,
		));

		$this->store(array('handlers'));

		// print_r($currentHandlers);

		return $currentHandlers;
	}


	public function deleteAuthzHandler($id) {

		$currentHandlers = $this->get('handlers', array());

		if (!isset($currentHandlers[$id])) {
			return true;
		}

		unset($currentHandlers[$id]);

		$this->set('handlers', $currentHandlers);

		UWAPLogger::info('core-dev', 'Deleting authorization handler', array(
			'id' => $id,
		));

		$this->store(array('handlers'));

		return $currentHandlers;
	}

	protected function addArrValue($key, $value) {
		$curArr = $this->get($key, array());

		$indexed = array();
		foreach($curArr AS $c) {
			$indexed[$c] = 1;
		}
		$indexed[$key] = 1;

		$curArr = array_keys($indexed);
		$this->set($key, $curArr);
	}

	protected function removeArrValue($key, $value) {
		$curArr = $this->get($key, array());

		$indexed = array();
		foreach($curArr AS $c) {
			$indexed[$c] = 1;
		}
		if (isset($indexed[$key])) {
			unset($indexed[$key]);
		}

		$curArr = array_keys($indexed);
		$this->set($key, $curArr);
	}

	public function hasScope($scope) {
		$curArr = $this->get('scopes', array());
		foreach($curArr AS $s) {
			if ($s === $scope) return true;
		}
		return false;
	}

	protected function setScope($scope, $accepted = true) {

		// Delete
		if ($accepted === null) {

			$this->removeArrValue('scopes', $scope);
			$this->removeArrValue('scopes_requested', $scope);

		} else if ($accepted === true) {

			$this->addArrValue('scopes', $scope);
			$this->removeArrValue('scopes_requested', $scope);


		} else if ($accepted === false) {

			if ($this->hasScope($scope)) {

				// Already has this scope.

			} else {
				$this->removeArrValue('scopes', $scope);
				$this->addArrValue('scopes_requested', $scope);				

			}


		}

	}


	protected function requestScope($scope) {

		$allowedScopes = array('userinfo' => 1, 'feedread' => 1, 'feedwrite' => 1, 'longterm' => 1);

		if (isset($allowedScopes[$scope]) && $allowedScopes[$scope]) {
			$this->setScope($scope, true);
		} else {
			$this->setScope($scope, false);
		}

	}


	public function requestScopes($scopes) {

		foreach($scopes AS $scope) {
			$this->requestScope($scope);
		}
		$this->store(array('scopes', 'scopes_requested'));

	}

	public function removeScope($scope) {
		$this->setScope($scope, null);
		$this->store(array('scopes', 'scopes_requested'));
	}


	public function getJSON($opts = array()) {

		// echo 'group::getjson <pre>'; print_r($opts);
		// throw new Exception();


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


		return $ret;
	}


	public static function generate($properties, $user) {


		$userproperties = array('id', 'name', 'descr', 'type', 'proxy');

		$requestedScopes = isset($properties['scopes']) ? $properties['scopes'] : array('userinfo');

		foreach($properties AS $k => $v) {
			if (!in_array($k, $userproperties)) {
				unset($properties[$k]);
			}
		}

		if (!isset($properties['type'])) {
			throw new Exception('Client type required');
		}

		$properties['uwap-userid'] = $user->get('userid');

		if (isset($properties['id'])) {
			if(self::exists($properties['id'])) {
				throw new Exception('Selecting an identifier that is used already');
			}
		}


		if ($properties['type'] === 'app') {

			$client = new App($properties);


		} else if ($properties['type'] === 'proxy') {

			$client = new APIProxy($properties);

		} else if ($properties['type'] === 'client') {

			$properties['status'] = array('operational');
			$client = new Client($properties);

		} else {

			throw new Exception('Invalid client type.');

		}

		$client->requestScopes($requestedScopes);
		$client->store();

		// echo "About to return a new client: ";

		return $client;
	}


	public static function getByID($id) {
		$data = self::getRawByID($id);

		// echo "Type " . get_called_class() . "\n\n";

		$obj = null;

		if ($data['type'] === 'client') {
			$obj = new Client($data);

		} else if($data['type'] === 'proxy') {
			$obj = new APIProxy($data);

		} else if($data['type'] === 'app') {
			// echo "CREATING NEW APP with data <pre>"; print_r($data);
			$obj = new App($data);

		} else {
			throw new Exception('Cannot determine type of this Client. Valid options are client,app and proxy');
		}

		if (!is_a($obj, get_called_class())) throw new Exception('The obtained object is of wrong type.');
		return $obj;

	}


}