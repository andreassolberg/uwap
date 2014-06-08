<?php



class So_AccessToken {
	public $issued, $validuntil, $client_id, $userid, $access_token, $token_type, $refresh_token, $scope, $userdata, $clientdata;
	
	function __construct() {
	}
	static function generate($client_id, $userid, $userdata, $scope = null, $refreshtoken = true, $expires_in = 3600) {
		$n = new So_AccessToken();

		$n->client_id = $client_id;
		$n->userid = $userid;
		$n->userdata = $userdata;
		$n->issued = time();
		$n->validuntil = time() + $expires_in;
		$n->access_token = So_Utils::gen_uuid();

		$n->clientdata = array(
			'client_id' => $client_id
		);

		if ($refreshtoken) {
			$n->refresh_token = So_Utils::gen_uuid();			
		}

		$n->token_type = 'bearer';
		
		if ($scope) {
			$n->scope = $scope;
		}
		return $n;
	}
	function getScope() {
		return join(' ', $this->scope);
	}
	
	function getAuthorizationHeader() {
		return 'Authorization: Bearer ' . $this->access_token . "\r\n";
	}
	
	function gotScopes($gotscopes) {
		if ($gotscopes === null) return true;
		if (empty($gotscopes)) return true;
		if ($this->scope === null) return false;
		
		assert('is_array($gotscopes)');
		assert('is_array($this->scope)');
		
		foreach($gotscopes AS $gotscope) {
			if (!in_array($gotscope, $this->scope)) return false;
		}
		return true;
	}
	
	// Is the token valid 
	function isValid() {
		if ($this->validuntil === null) return true;
		if ($this->validuntil > (time() + 2)) return true; // If a token is valid in less than two seconds, treat it as expired.
		return false;
	}
	
	function requireValid($scope) {
		if (!$this->isValid()) throw new So_ExpiredToken('Token expired');
		if (!$this->gotScopes($scope)) throw new Exception('Token did not include the required scopes.');
	}
	
	function getObj() {
		$obj = array();
		foreach($this AS $key => $value) {
			if (in_array($key, array())) continue;
			if ($value === null) continue;
			$obj[$key] = $value;
		}
		return $obj;
	}
	
	static function fromObj($obj) {
		$n = new So_AccessToken();
		if (isset($obj['issued'])) $n->issued = $obj['issued'];
		if (isset($obj['validuntil'])) $n->validuntil = $obj['validuntil'];
		if (isset($obj['client_id'])) $n->client_id = $obj['client_id'];
		if (isset($obj['userid'])) $n->userid = $obj['userid'];
		if (isset($obj['userdata'])) $n->userdata = $obj['userdata'];
		if (isset($obj['clientdata'])) $n->clientdata = $obj['clientdata'];
		if (isset($obj['access_token'])) $n->access_token = $obj['access_token'];
		if (isset($obj['token_type'])) $n->token_type = $obj['token_type'];
		if (isset($obj['refresh_token'])) $n->refresh_token = $obj['refresh_token'];
		if (isset($obj['scope'])) $n->scope = $obj['scope'];


		return $n;
	}
	function getValue() {
		return $this->access_token;
	}
	function getToken() {
		$result = array();
		$result['access_token'] = $this->access_token;
		$result['token_type'] = $this->token_type;
		if (!empty($this->validuntil)) {
			$result['expires_in'] = $this->validuntil - time();
		}
		if (!empty($this->refresh_token)) {
			$result['refresh_token'] = $this->refresh_token;
		}
		if (!empty($this->scope)) {
			$result['scope'] = $this->getScope();
		}
		return $result;
	}
}
