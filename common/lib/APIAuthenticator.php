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

		// Optionally, but not reccomended access token may be provided as an query string parameter
		if (isset($_REQUEST['access_token']) && !empty($_REQUEST['access_token'])) {
			return trim($_REQUEST['access_token']);
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
			throw new So_UnauthorizedRequest('unauthorized_client', 'No token provided. Required...');
		}

		$obj = array(
			"access_token" => $tokenstr
		);
		$result = $this->store->queryOne('oauth2-server-tokens', $obj);
		if ($result === null) throw new So_UnauthorizedRequest('unauthorized_client', 'Could not find the specified access token');
		
		$this->token = So_AccessToken::fromObj($result);
		// $this->token = new AuthenticatedToken($t);
		// error_log('Token is ' . var_export($this->token, true));

		return $this;
	}


	public function reqAppScope($proxy) {
		$baseScope = 'rest_' . $proxy->get('id');
		return $this->reqScopes(array($baseScope));
	}

	public function getAppScopes($proxy) {
		$filterPrefix = 'rest_' . $proxy->get('id');
		return $this->filterScopes($filterPrefix);
	}

	public function filterScopes($filterPrefix) {
		$tokenScopes = $this->token->scope;
		$scopes = array();
		// echo '<pre>';
		// echo "looking for " . $filterPrefix;
		// echo "Token is"; print_r($tokenScopes);
		foreach($tokenScopes AS $tokenScope) {
			// echo "check [" . $tokenScope . "]";
			if (strpos($tokenScope, $filterPrefix . '-') === 0) {
				// echo "found [" . $tokenScope . "]";
				$scopes[] = substr($tokenScope, strlen($filterPrefix . '-'));
			}
		}
		// echo "Found "; print_r($scopes); exit;
		return $scopes;
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
		// $this->client = 
		return Client::getByID($this->token->client_id);
	}


	
}

