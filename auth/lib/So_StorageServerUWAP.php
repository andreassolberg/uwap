<?php

/*
Example configurations:

App: 

	{
		client_id: "app_appname",
		client_name: "Name of app",
		redirect_uri: "http://appname.uwap.org/_/oauth/callback",
		scopes: ["app_"]
	}
 */


class So_StorageServerUWAP extends So_Storage {

	protected $store, $subid;

	function __construct($subid = null) {
		parent::__construct();

		$this->store = new UWAPStore();

		// $this->config = new Config($subid);
		// $this->subid = $this->config->getID();
	}

	public function getClient($client_id) {
		return Client::getByID($client_id);
	}

	// /*
	//  * Return an associated array or throws an exception.
	//  */
	// public function getClient($client_id) {
	// 	// notimplemented();

	// 	// TODO: Add impliccit app clients, with redirect_uri...
	// 	if (preg_match('/^app_([a-z0-9]+)/', $client_id, $matches)) {
	// 		$app = $matches[1];
	// 		$config = Config::getInstance($app);


	// 		return $config->getOAuthClientConfig();
	// 		// echo "app was " . $app; exit;
	// 	}

	// 	$query = array(
	// 		"client_id" => $client_id,
	// 		// "app" => $this->subid
	// 	);

	// 	// print_r("Query");
	// 	// print_r($query);
	// 	// exit;

	// 	$result = $this->store->queryOne("oauth2-server-clients", $query);

	// 	if ($result === null) throw new So_Exception('invalid_client', 'Unknown client identifier [' . $client_id . ']');
	// 	return $result;
	// }




	public function authorizeClient($clientid, $appid, $userid, $authz) {

		$client = $this->getClient($clientid);
		$updates = array('scopes' => array(), 'scopes_requested' => array());

		if (!empty($client['scopes'])) {
			foreach($client['scopes'] AS $scope) {

				// Access control, if not a match
				if (isset($authz[$scope]) && !$authz[$scope]) {
					// Remove existing scope
				} else {
					// Keep existing scope
					$updates['scopes'][] = $scope;
				}
			}
		}

		if (!empty($client['scopes_requested'])) {
			foreach($client['scopes_requested'] AS $scope) {
				if (isset($authz[$scope])) {
					if ($authz[$scope]) {
						// Remove existing scope
						$updates['scopes'][] = $scope;
					} else {
						// Rejected requested scope, not add to scope and not kept in requested..
					}
				} else {
					// No change to requested scope, stay as requested.
					$updates['scopes_requested'][] = $scope;
				}
			}
		}

		// return array(
		// 	'client' => $client,
		// 	'authz' => $authz,
		// 	'updates' => $updates
		// );
		// return $updates;

		$criteria = array('client_id' => $clientid);

		UWAPLogger::info('core-dev', 'Updating client authorization (scopes)', array(
			'criteria' => $criteria,
			'updates' => $updates,
			'userid' => $userid
		));

		$ret = $this->store->update('oauth2-server-clients', null, $criteria, $updates);
		if (empty($ret)) {
			throw new Exception('Empty response from update() on storage. Indicates an error occured. Check logs.');;
		}
		return true;

	}






	public function getAuthorization($client_id, $userid) {
		// notimplemented();

		$query = array(
			"client_id" => $client_id,
			"userid" => $userid
		);
		$result = $this->store->queryOne('oauth2-server-authorization', $query);

		error_log('Extracting authz ' . var_export($result, true));
		if ($result === null) return null;
		return So_Authorization::fromObj($result);
	}

	public function setAuthorization(So_Authorization $auth) {
		// notimplemented();

		$obj = $auth->getObj();
		// $obj["app"] = $this->subid;


		$query = array(
			"client_id" => $obj["client_id"],
			// "app" => $this->subid,
			"userid" => $obj["userid"]
		);
		$oldone = $this->store->queryOne('oauth2-server-authorization', $query);

		if ($oldone === null) {
			$this->store->store("oauth2-server-authorization", null, $obj);
		} else {
			
			foreach($obj AS $k => $v) {
				$oldone[$k] = $v;
			} 
			// echo '<pre>About to update an object: '; print_r($oldone); echo '</pre>';

			$this->store->store("oauth2-server-authorization", null, $oldone);
		}



		// if ($auth->stored) {
		// 	// UPDATE
		// 	error_log('update obj auth ' . var_export($auth->getObj(), true) );
		// 	$this->db->authorization->update(
		// 		array('userid' => $auth->userid, 'client_id' => $auth->client_id),
		// 		$auth->getObj()
		// 	);
		// } else {
		// 	// INSERT
		// 	error_log('insert obj auth ' . var_export($auth->getObj(), true) );
		// 	$this->db->authorization->insert($auth->getObj());
		// }
	}




	/*
	 * Return an associated array or throws an exception.
	 */
	public function getProviderConfig($provider_id) {
		notimplemented();

		// $result = $this->config->getHandlerConfig($provider_id);

		error_log("Handler config for Oauth 2.0 server is: " . var_export($result, true));

		// $result = $this->extractOne('providers', array('provider_id' => $provider_id));
		if ($result === null) throw new Exception('Unknown provider identifier [' . $provider_id . ']');
		return $result;
	}
	

	



	// TODO: Make sure access tokens are associated with both client and provider.
	public function putAccessToken($client_id, $userid, So_AccessToken $accesstoken) {
		// notimplemented();

		// echo "<pre>About to put accesstoken "; print_r($accesstoken); echo '</pre>';
		// exit;

		$obj = $accesstoken->getObj();
		// $obj['provider_id'] = $provider_id;
		// $obj['app'] = $this->subid;
		// $obj['userid'] = $userid;
		// $this->db->tokens->insert($obj);


		$this->store->store("oauth2-server-tokens", null, $obj);



		// $this->db->tokens->insert(array(
		// 	'provider_id' => $provider_id,
		// 	'userid' => $userid,
		// 	'token' => $accesstoken->getObj()
		// ));
	}
	
	/*
	 * Returns null or an array of So_AccessToken objects.
	 */
	public function getTokens($provider_id, $userid) {
		notimplemented();

		$query = array(
			"provider_id" => $provider_id,
			"app" => $this->subid
		);

		// $result = $this->extractList('tokens', array('id' => $id, 'userid' => $userid));

		// queryListUser($collection, $userid, $criteria = array()) {
		$result = $this->store->queryListUser("oauth2-client-tokens", $this->userid, $query);
		if ($result === null) return null;
		
		$objs = array();
		foreach($result AS $res) {
			$objs[] = So_AccessToken::fromObj($res);
		}
		return $objs;
	}


	public function wipeToken($provider_id, $token) {
		
		notimplemented();

		$obj = array(
			"provider_id" => $provider_id,
			"app" => $this->subid
		);

		// if (!isset($token['access_token'])) throw new Exception('Trying to delete an access token where accesstoken is empty.');
		$obj['access_token'] = $token->getValue();



		return $this->store->remove("oauth2-client-tokens", $this->userid, $obj);

	}
	
	/*
	 * Returns null or a specific access token.
	 */
	public function getToken($token) {
		// notimplemented();

		$obj = array(
			"access_token" => $token,
			"app" => $this->subid
		);

		error_log('Storage  getToken(' . $token . ')');
		$result = $this->store->queryOne('oauth2-server-tokens', $obj);
		error_log("RESULT: " . $result);
		if ($result === null) throw new Exception('Could not find the specified token.');
		
		return So_AccessToken::fromObj($result);
	}
		
	public function putCode(So_AuthorizationCode $code) {
		$this->store->store("oauth2-server-codes", null, $code->getObj(), 3600);
	}
	public function getCode($client_id, $code) {
		// notimplemented();

		$query = array(
			"code" => $code,
			"client_id" => $client_id
		);

		$result = $this->store->queryOne('oauth2-server-codes', $query);
		if ($result === null) throw new So_Exception('invalid_grant', 'Invalid authorization code.');
		$this->store->remove('oauth2-server-codes', null, $result);
		// $this->db->codes->remove($result, array("safe" => true));
		return So_AuthorizationCode::fromObj($result);
	}
	
	public function putState($state, $obj) {

		notimplemented();

		// notimplemented();
		$obj['state'] = $state;
		// store($collection, $userid, $obj, $expiresin = null) {
		$this->store->store("oauth2-client-states", $this->userid, $obj, 3600);

		// $this->db->states->insert($obj);
	}
	public function getState($state) {
		notimplemented();

		// notimplemented();
		$query = array(
			"state" => $state
		);
		$result = $this->store->queryOneUser("oauth2-client-states", $this->userid, $query);
		if ($result === null) throw new Exception('Could not retrieve state from storage, maybe it has expired? lasts for one hour.');

		$this->store->remove("oauth2-client-states", $this->userid, $result);

		// $result = $this->extractOne('states', array('state' => $state));
		// if ($result === null) throw new So_Exception('invalid_grant', 'Invalid authorization code.');
		// $this->db->states->remove($result, array("safe" => true));
		return $result;
	}
	



}