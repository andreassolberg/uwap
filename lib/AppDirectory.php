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


	// TODO: Start using this from config.... moved here.
	public function exists($id) {
		$config = $this->store->queryOne('appconfig', array("id" => $id));
		return (!empty($config));
	}



	
}

