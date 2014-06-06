<?php


/**
 * This class is used by the API handler to check for a valid OAuth token,
 * and extract all needed information associated with the token.
 * 
 */
class APIAuthenticator {
	
	protected $store;
	protected $user;
	public $token = null;

	function __construct() {
		$this->store = new UWAPStore();
	}


	/**
	 * Extract the Bearer token string from the Authorization header
	 * if present. If not present, return null.
	 * @return [type] [description]
	 */
	protected function getBearerToken() {
		$hdrs = getallheaders();
		foreach($hdrs AS $h => $v) {
			if ($h === 'Authorization') {
				if (preg_match('/^Bearer\s(.*?)$/i', $v, $matches)) {
					return trim($matches[1]);
				}
			}
		}
		return null;
	}


	/**
	 * Require valid token
	 * @return [type] [description]
	 */
	public function reqToken() {

		$tokenstr = $this->getBearerToken();
		if ($tokenstr === null) {
			throw new Exception('No token provided. Required...');
		}

		$obj = array(
			"access_token" => $tokenstr
		);
		$result = $this->store->queryOne('oauth2-server-tokens', $obj);
		if ($result === null) throw new Exception('Could not find the specified access token [].');
		
		$this->token = So_AccessToken::fromObj($result);
		// $this->token = new AuthenticatedToken($t);
		// error_log('Token is ' . var_export($this->token, true));

		return $this;
	}

	/**
	 * Require a valid token with a set of scopes...
	 * All provided scopes MUST be present.
	 * @param  array  $scopes [description]
	 * @return [type]         [description]
	 */
	public function reqScopes(array $scopes) {

		$this->reqToken();
		if (!$this->token->gotScopes($scopes)) {
			throw new So_UnauthorizedRequest('unauthorized_client', 'Insufficient scope on provided token. Missing [' . join(',', $scopes) . ']');		
		}

		return $this;
	}

	public function getUser() {

		$this->reqToken();

		if (!empty($this->user)) return $this->user;
		$this->user = User::getByID($this->token->userdata['userid']);
		return $this->user;

	}

	public function getClient() {
		// $this->client = Client::getByID($this->token->client_id);
	}


	
}