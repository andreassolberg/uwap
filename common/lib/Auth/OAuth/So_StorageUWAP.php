<?php

function notimplemented() {
	$debug = debug_backtrace();
	$entry = $debug[1];
	
		
	$str = $entry["function"] . " (" . var_export($entry["args"], true) . ")";
	
	
	throw new Exception("Not yet implemented: " . $str);
}


class So_StorageUWAP extends So_Storage {

	protected $store, $userid, $subid, $config;

	function __construct($userid, $appid = null) {
		parent::__construct();

		$this->store = new UWAPStore();
		$this->userid = $userid;
		// $this->handler = $handler;

		$this->config = Config::getInstance($appid);
		$this->subid = $this->config->getID();
	}

	public function getAppID() {
		return $this->subid;
	}

	/*
	 * Return an associated array or throws an exception.
	 */
	public function getClient($client_id) {
		notimplemented();

		$result = $this->extractOne('clients', array('client_id' => $client_id));
		if ($result === null) throw new So_Exception('invalid_client', 'Unknown client identifier [' . $client_id . ']');
		return $result;
	}

	/*
	 * Return an associated array or throws an exception.
	 */
	public function getProviderConfig($provider_id) {
		// notimplemented();

		$result = $this->config->getHandlerConfig($provider_id);

		error_log("Handler config for Oauth 2.0 server is: " . var_export($result, true));

		// $result = $this->extractOne('providers', array('provider_id' => $provider_id));
		if ($result === null) throw new Exception('Unknown provider identifier [' . $provider_id . ']');
		return $result;
	}
	


	public function getAuthorization($client_id, $userid) {
		notimplemented();

		$result = $this->extractOne('authorization', 
			array(
				'client_id' => $client_id,
				'userid' => $userid
			)
		);
		error_log('Extracting authz ' . var_export($result, true));
		if ($result === null) return null;
		return So_Authorization::fromObj($result);
	}
	
	public function setAuthorization(So_Authorization $auth) {
		notimplemented();

		if ($auth->stored) {
			// UPDATE
			error_log('update obj auth ' . var_export($auth->getObj(), true) );
			$this->db->authorization->update(
				array('userid' => $auth->userid, 'client_id' => $auth->client_id),
				$auth->getObj()
			);
		} else {
			// INSERT
			error_log('insert obj auth ' . var_export($auth->getObj(), true) );
			$this->db->authorization->insert($auth->getObj());
		}
	}


	// TODO: Make sure access tokens are associated with both client and provider.
	public function putAccessToken($provider_id, $userid, So_AccessToken $accesstoken) {
		// notimplemented();

		$obj = $accesstoken->getObj();
		$obj['provider_id'] = $provider_id;
		$obj['app'] = $this->subid;
		// $obj['userid'] = $userid;
		// $this->db->tokens->insert($obj);

		$this->store->store("oauth2-client-tokens", $this->userid, $obj);

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
		// notimplemented();

		$query = array(
			"provider_id" => $provider_id,
			"app" => $this->subid
		);

		// $result = $this->extractList('tokens', array('id' => $id, 'userid' => $userid));


		error_log('Query tokens ' . json_encode($query));

		// queryListUser($collection, $userid, $criteria = array()) {
		$result = $this->store->queryListUser("oauth2-client-tokens", $this->userid, null, $query);
		if ($result === null) return null;
		
		$objs = array();
		foreach($result AS $res) {
			$objs[] = So_AccessToken::fromObj($res);
		}
		return $objs;
	}


	public function wipeToken($provider_id, $token) {
		

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
		notimplemented();

		error_log('Storage › getToken(' . $token . ')');
		$result = $this->extractOne('tokens', array('access_token' => $token));
		if ($result === null) throw new Exception('Could not find the specified token.');
		
		return So_AccessToken::fromObj($result);
	}
		
	public function putCode(So_AuthorizationCode $code) {
		notimplemented();

		$this->db->codes->insert($code->getObj());
	}
	public function getCode($client_id, $code) {
		notimplemented();

		$result = $this->extractOne('codes', array('client_id' => $client_id, 'code' => $code));
		if ($result === null) throw new So_Exception('invalid_grant', 'Invalid authorization code.');
		$this->db->codes->remove($result, array("safe" => true));
		return So_AuthorizationCode::fromObj($result);
	}
	
	public function putState($state, $obj) {

		$obj['state'] = $state;
		// store($collection, $userid, $obj, $expiresin = null) {
		$this->store->store("oauth2-client-states", $this->userid, $obj, 3600);

		// $this->db->states->insert($obj);
	}

	public static function getStateStatic($state) {
		$store = new UWAPStore();
		$query = array(
			"state" => $state
		);
		$result = $store->queryOne("oauth2-client-states", $query);
		return $result;
	}

	public function getState($state) {

		$query = array(
			"state" => $state
		);

		// echo "Query getState: " . var_export($query, true);

		$result = $this->store->queryOneUser("oauth2-client-states", $this->userid, array(), $query);
		if ($result === null) throw new Exception('Could not retrieve state from storage, maybe it has expired? lasts for one hour.');

		$this->store->remove("oauth2-client-states", $this->userid, $result);

		// $result = $this->extractOne('states', array('state' => $state));
		// if ($result === null) throw new So_Exception('invalid_grant', 'Invalid authorization code.');
		// $this->db->states->remove($result, array("safe" => true));
		return $result;
	}
	



}