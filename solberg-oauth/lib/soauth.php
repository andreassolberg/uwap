<?php

/*
 * This file is part of Solberg-OAuth
 * Read more here: https://github.com/andreassolberg/solberg-oauth
 */

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 1);
assert_options(ASSERT_QUIET_EVAL, 0);


function http_parse_headers( $header, $hdrs ) {
	$key = null;
	$value = null;

	$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
	foreach( $fields as $field ) {
	    if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
	        $key = strtolower(preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1]))));
	        $value = trim($match[2]);

	        if (isset($key)) {
	        	if (!isset($hdrs[$key])) {
	        		$hdrs[$key] = array();
	        	}
	        	$hdrs[$key][] = $value;
	        }

	    }
	}

}


class So_ExpiredToken extends Exception {}
class So_AuthorizationRequired extends Exception {
	public $scopes;
	public $client_id;
}
class So_InsufficientScope extends Exception {}

/*
 * Log to MongoDB 
 */
class So_log {
	protected static $db;
	
	// Logged error messages beyond this level, will not 
	// be logged
	protected static $logLevel = 4;
	protected static $stacktrace = true;
	
	private static function init($logLevel = null, $stacktrace = null) {
		if ($logLevel !== null) {
			self::$logLevel = $logLevel;
		}
		if ($stacktrace !== null) {
			self::$stacktrace = $stacktrace;
		}
		if (empty(self::$db)) {
			// $m = new Mongo();
			// self::$db = $m->oauth;
		}
	}

	public static function debug($message, $obj = null) { 
		UWAPLogger::debug('oauth2-lib', $message, $obj);
	}
	public static function info($message, $obj = null) {
		UWAPLogger::info('oauth2-lib', $message, $obj);
	}
	public static function warn($message, $obj = null) {
		UWAPLogger::warn('oauth2-lib', $message, $obj);
	}
	public static function error($message, $obj = null) {
		UWAPLogger::error('oauth2-lib', $message, $obj);
	}
}

/**
 * Persistent Storage. Pluggable.
 */
abstract class So_Storage {
	function __construct() {
	}
	public abstract function getClient($client_id);
}

/**
 * A MongoDB implementation of the Storage API is 
 */
class So_StorageMongo extends So_Storage {
	protected $db;
	function __construct() {
		parent::__construct();
		$m = new Mongo();
		$this->db = $m->oauth;
	}
	private function extractOne($collection, $criteria) {
		$cursor = $this->db->{$collection}->find($criteria);
		if ($cursor->count() < 1) return null;
		return $cursor->getNext();
	}

	private function extractList($collection, $criteria) {
		$cursor = $this->db->{$collection}->find($criteria);
		if ($cursor->count() < 1) return null;
		
		$result = array();
		foreach($cursor AS $element) $result[] = $element;
		return $result;
	}

	/*
	 * Return an associated array or throws an exception.
	 */
	public function getClient($client_id) {
		$result = $this->extractOne('clients', array('client_id' => $client_id));
		if ($result === null) throw new So_Exception('invalid_client', 'Unknown client identifier');
		return $result;
	}

	/*
	 * Return an associated array or throws an exception.
	 */
	public function getProviderConfig($provider_id) {
		$result = $this->extractOne('providers', array('provider_id' => $provider_id));
		if ($result === null) throw new Exception('Unknown provider identifier');
		return $result;
	}
	
	public function getAuthorization($client_id, $userid) {
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


	
	public function putAccessToken($id, $userid, So_AccessToken $accesstoken) {
		$obj = $accesstoken->getObj();
		$obj['id'] = $id;
		$obj['userid'] = $userid;
		$this->db->tokens->insert($obj);

		// $this->db->tokens->insert(array(
		// 	'provider_id' => $provider_id,
		// 	'userid' => $userid,
		// 	'token' => $accesstoken->getObj()
		// ));
	}
	
	/*
	 * Returns null or an array of So_AccessToken objects.
	 */
	public function getTokens($id, $userid) {
		$result = $this->extractList('tokens', array('id' => $id, 'userid' => $userid));
		if ($result === null) return null;
		
		$objs = array();
		foreach($result AS $res) {
			$objs[] = So_AccessToken::fromObj($res);
		}
		return $objs;
	}
	
	/*
	 * Returns null or a specific access token.
	 */
	public function getToken($token) {
		error_log('Storage › getToken(' . $token . ')');
		$result = $this->extractOne('tokens', array('access_token' => $token));
		if ($result === null) throw new Exception('Could not find the specified token.');
		
		return So_AccessToken::fromObj($result);
	}
		
	public function putCode(So_AuthorizationCode $code) {
		$this->db->codes->insert($code->getObj());
	}
	public function getCode($client_id, $code) {
		$result = $this->extractOne('codes', array('client_id' => $client_id, 'code' => $code));
		if ($result === null) throw new So_Exception('invalid_grant', 'Invalid authorization code.');
		$this->db->codes->remove($result, array("safe" => true));
		return So_AuthorizationCode::fromObj($result);
	}
	
	public function putState($state, $obj) {
		$obj['state'] = $state;
		$this->db->states->insert($obj);
	}
	public function getState($state) {
		$result = $this->extractOne('states', array('state' => $state));
		if ($result === null) throw new So_Exception('invalid_grant', 'Invalid authorization code.');
		$this->db->states->remove($result, array("safe" => true));
		return $result;
	}

}


class So_RedirectException extends Exception {
	protected $url;
	function __construct($url) {
		$this->url = $url;
	}
	function getURL() {
		return $this->url;
	}
}

class So_Client {
	
	protected $store;

	function __construct($store = null) {
		if ($store === null) {
			$this->store = new So_StorageMongo();
		} else {
			$this->store = $store;
		}
	}
	
	// function __construct($clientconfig, $providerconfig) {
	// 	$this->clientconfig = $clientconfig;
	// 	$this->providerconfig = $providerconfig;
	// 	
	// 	$this->clientconfig['redirect_uri'] = So_Utils::geturl();
	// }
	
	
	/*
	 * Get a one token, if cached.
	 */
	function getToken($provider_id, $user_id, $scope = null) {
		$tokens = $this->store->getTokens($provider_id, $user_id);
		
		// error_log('getToken() returns ' . var_export($tokens, true));
		
		if ($tokens === null) return null;
		
		foreach($tokens AS $token) {
			if (!$token->isValid()) {
				error_log('Skipping invalid token: ' . var_export($token, true));
				continue;
			} 
			if (!$token->gotScopes($scope)) {
				error_log('Skipping token because of scope: ' . var_export($token, true));
				continue;
			}
			return $token;
		}
		return null;
	}

	function wipeToken($provider_id, $token) {
		So_log::debug('Wiping token!', array('args' => func_get_args()));
		$this->store->wipeToken($provider_id, $token);
	}
	
	
	function checkForToken($provider_id, $user_id, $scope = null) {
		$providerconfig = $this->store->getProviderConfig($provider_id);
		$token = $this->getToken($provider_id, $user_id, $scope);
		return ($token !== null);
	}
	
	function getHTTP($provider_id, $user_id, $url, array $requestScope = null, array $requireScope = null, $allowRedirect = true, $returnTo = null) {
		
		So_log::debug('getHTTP', array('args' => func_get_args()));
		
		$providerconfig = $this->store->getProviderConfig($provider_id);
		$token = $this->getToken($provider_id, $user_id, $requireScope);
		
		if ($token === null) {
			// Redirects if applicable...
			$redirectURL = $this->authreq($providerconfig, $requestScope, $allowRedirect, $provider_id, $returnTo);
			throw new So_RedirectException($redirectURL);
		}
		
		So_log::debug('Found a matching access token to use', array('token' => $token));
		
		// error_log('Header: ' . $token->getAuthorizationHeader());
		$httpopts = array(
	        'method'  => 'GET',
	        'header'  => $token->getAuthorizationHeader()
	    );
		$opts = array('http' => $httpopts);
		$context  = stream_context_create($opts);
		

		if (isset($providerconfig["tokentransport"])) {
			if ($providerconfig["tokentransport"] === "query") {
				$url = SimpleSAML_Utilities::addURLparameter($url, array("access_token" => $token->getValue()));
			}
		}

		// $url .= '?access_token=' . $token->access_token ;
		error_log("Getting data from url: " . $url);
		error_log("   Using header:  " . $token->getAuthorizationHeader());
		$result = @file_get_contents($url, false, $context);


		if ($result === false) {

			if (empty($http_response_header)) {
				throw new Exception('http_response_header is empty');
			}

			list($version, $status_code, $msg) = explode(' ', $http_response_header[0], 3);
			$headers = array();
			foreach($http_response_header AS $hdr) {
				http_parse_headers($hdr, &$headers);
			}
			if ($status_code !== 200) {
				So_log::debug('Status code was (not 200)', array('status' => $status_code, 'msg' => $msg, 'Headers' => $headers));
			}
			if ($status_code === '400') {
				if (isset($headers["www-authenticate"])) {
					if (strpos($headers["www-authenticate"][0],
						'OAuth "Facebook Platform" "invalid_token"'
						) !== false) {

						$this->wipeToken($provider_id, $token);

						// Facebook 
						throw new So_ExpiredToken("Access Token seems to be expired [facebook].");

					} else if (strpos($headers["www-authenticate"][0],
						'OAuth "Facebook Platform" "invalid_request"'
						) !== false) {

						$this->wipeToken($provider_id, $token);

						// Facebook 
						throw new So_ExpiredToken("Access Token seems to be expired [facebook].");
					}
				}
			} else if ($status_code === '401') {
				if (isset($headers["www-authenticate"])) {
					if (strpos($headers["www-authenticate"][0],
						'"invalid_token"'
						) !== false) {

						$this->wipeToken($provider_id, $token);

						// Not facebook. Standard compliant. 
						throw new So_ExpiredToken("Access Token seems to be expired.");
					}
				}
			} else if ($status_code === '403') {
				$msg = '403. Insufficient scope?';
				if (isset($headers["www-authenticate"])) {
					$msg = $headers["www-authenticate"][0];
				}
				throw new So_InsufficientScope($msg);
			}
			// print_r($http_response_header); 
			// print_r($headers); 
			// echo "status_code: " . $status_code;
			// exit;


			throw new Exception('Error (' . $status_code . ') when accessing data endpoint.');
		}


		error_log("Getting data : " . $result);

		return $result;
	}
	
	function callback($userid) {
		
		So_log::debug('Access callback page');
		
		if (!isset($_REQUEST['code'])) {
			throw new Exception('Did not get [code] parameter in response as expeted');
		}
		
		$authresponse = new So_AuthResponse($_REQUEST);
		$stateobj = $this->store->getState($authresponse->state);
		$provider_id = $stateobj["provider_id"];


		if (empty($provider_id)) throw new Exception("could not find provider_id in state array. Internal error. should not happen.");

		$providerconfig = $this->store->getProviderConfig($provider_id);



		So_log::debug('Got an Authorization Response', array('params' => $_REQUEST));

		
		$opts = array();
		if (isset($providerconfig['redirect_uri'])) {
			$opts['redirect_uri'] = $providerconfig['redirect_uri'];
		}

		So_log::debug('Provider config', $providerconfig);

		$tokenrequest = $authresponse->getTokenRequest($opts);
		$tokenrequest->setClientCredentials($providerconfig['client_id'], $providerconfig['client_secret']);
		
		$tokenresponseraw = $tokenrequest->post($providerconfig['token']);
		
		
		$tokenresponse = new So_TokenResponse($tokenresponseraw);
		
		// Todo check for error response.

		$accesstoken = So_AccessToken::fromObj($tokenresponseraw);

		if (empty($accesstoken->scope)) {
			if (!empty($stateobj['requestedScopes'])) {
				$accesstoken->scope = $stateobj['requestedScopes'];
			}
		}

		if (empty($accesstoken->client_id)) {
			if (!empty($stateobj['client_id'])) {
				$accesstoken->client_id = $stateobj['client_id'];
			}
		}

		if (empty($accesstoken->validuntil)) {
			if (!empty($providerconfig['defaultexpire'])) {
				$accesstoken->validuntil = time() + $providerconfig['defaultexpire'];
			}
		}
		if (empty($accesstoken->issued)) {
			$accesstoken->issued = time();
		}

		// echo '<pre>'; 
		// echo 'state object';
		// print_r($stateobj); 
		// echo 'provider config';
		// print_r($providerconfig);
		// echo 'tokenresponse';
		// print_r($accesstoken);
		// echo '</pre>';
		// exit;
		
		$this->store->putAccessToken($provider_id, $userid, $accesstoken);
		
		if (!empty($stateobj['redirect_uri'])) {
			// echo '<pre>Ready to redirect back to ' . $stateobj['redirect_uri']; exit; echo '</pre>';
			header('Location: ' . $stateobj['redirect_uri']);
			exit;
		}
		throw new Exception('I got the token and everything, but I dont know what do do next... State lost.');
	}

	
	private function authreq($providerconfig, $scope = null, $allowRedirect = true, $providerID, $returnTo = null) {
		So_log::debug('Initiating a new authorization request');
		
		if ($returnTo === null) $returnTo = SimpleSAML_Utilities::selfURL();

		$state = So_Utils::gen_uuid();
		$stateobj = array(
			'redirect_uri' => $returnTo,
			'provider_id' => $providerID,
			'requestedScopes' => $scope,
			'client_id' => $providerconfig['client_id'],
			'appid' => $this->store->getAppID(),
		);
		error_log("Storing a new state object: " . $state . "  " . json_encode($stateobj));
		$this->store->putState($state, $stateobj);

		UWAPLogger::debug('oauth2', 'Provider data loaded', $providerconfig);

		$requestdata = array(
			'response_type' => 'code',
			'client_id' => $providerconfig['client_id'],
			'state' => $state,
			'redirect_uri' => $providerconfig['redirect_uri'],
		);
		if (!empty($scope)) $requestdata['scope'] = join(' ', $scope);

		$request = new So_AuthRequest($requestdata);
		So_log::debug('Redirecting to ', $providerconfig['authorization']);
		
		if ($allowRedirect) {
			$request->sendRedirect($providerconfig['authorization']);
		} else {
			return $request->getRedirectURL($providerconfig['authorization']);
		}

	}
	
}

class So_Server {
	
	protected $store;
	
	function __construct($store = null) {
		if ($store === null) {
			$this->store = new So_StorageMongo();
		} else {
			$this->store = $store;
		}
	}

	
	public function info() {
		
		$base =(!empty($_SERVER['HTTPS'])) ? 
			"https://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'] : 
			"http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
			
		$meta = array(
			'authorization' => $base . '/authorization',
			'token' => $base . '/token',
		);
		
		echo '<!DOCTYPE html>

		<html lang="en">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<title>OAuth information endpoint</title>
		</head>
		<body>
			
			<h1>OAuth information</h1>
			
			<p>Here are some neccessary information in order to setup OAuth connectivity with this OAuth Provider:</p>
			
			<pre>' . var_export($meta, true) . '</pre>

		</body>
		</html>
		';
	}
	
	
	private function getAuthorizationHeader() {
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
	
	
	private function getProvidedToken() {
		$authorizationHeader = $this->getAuthorizationHeader();
		// echo '<pre>'; print_r($authorizationHeader); exit;
		if ($authorizationHeader !== null) return $authorizationHeader;
		
		if (!empty($_REQUEST['access_token'])) return $_REQUEST['access_token'];
		
		throw new Exception('Could not get provided Access Token');
	}
	
	
	public function getToken() {
		$tokenstr = $this->getProvidedToken();
		$token = $this->store->getToken($tokenstr);
		return $token;
	}

	/*
	 * Check a token provided by a user, through the authorization header.
	 */
	function checkToken($scope = null) {
		
		$tokenstr = $this->getProvidedToken();
		$token = $this->store->getToken($tokenstr);
		$token->requireValid($scope);
		return $token;
	}
	
	
	private function validateRedirectURI(So_AuthRequest $request, $clientconfig) {


		if (is_array($clientconfig['redirect_uri'])) {
			if (empty($request->redirect_uri)) {
				// url not specified in request, returning first entry from config
				return $clientconfig['redirect_uri'][0];
				
			} else {
				// url specified in request, returning if is substring match any of the entries in config
				foreach($clientconfig['redirect_uri'] AS $su) {
					if (strpos($request->redirect_uri, $su) === 0) {
						return $request->redirect_uri;
					}
				}
			}
		} else if (!empty($clientconfig['redirect_uri'])) {
			if (empty($request->redirect_uri)) {
				// url not specified in request, returning the only entry from config
				return $clientconfig['redirect_uri'];
				
			} else {
				// url specified in request, returning if is substring match the entry in config
				if (strpos($request->redirect_uri, $clientconfig['redirect_uri']) === 0) {
					return $request->redirect_uri;
				}	
			}
		}
		
		throw new So_Exception('invalid_request', 'Not able to resolve a valid redirect_uri for client');
	}
	

	public function setAuthorization($client_id, $userid, $scopes) {

		error_log('setAuthorization($client_id, $userid, $scopes) ' . "($client_id, $userid, $scopes)");
		$clientconfig = $this->store->getClient($client_id);
		$acceptedScopes = array_intersect($scopes, $clientconfig['scopes']);

		$authorization = $this->store->getAuthorization($client_id, $userid);
		if ($authorization === null) {
			$authorization = new So_Authorization($userid, $client_id, $scopes);	
		}

		if (!empty($scopes)) {
			$authorization->addScopes($scopes);
		}
		$this->store->setAuthorization($authorization);
	}


	public function authorization($userid, $userdata = null) {
		
		$request = new So_AuthRequest($_REQUEST);
		$clientconfig = $this->store->getClient($request->client_id);
		$url = $this->validateRedirectURI($request, $clientconfig);
		
		$authorization = $this->store->getAuthorization($request->client_id, $userid);

		$scopes = $clientconfig['scopes'];
		if (!empty($request->scope)) {
			// Only consider scopes that the client is authorized to ask for.
			$scopes = array_intersect($request->scope, $clientconfig['scopes']);
		}

		// echo '<pre>';
		// print_r($request); print_r($clientconfig['scopes']);





		if ($authorization === null || !$authorization->includeScopes($scopes)) {

			$remainingScopes = $scopes;
			if ($authorization === null) {
				error_log("Authorization object not found,");
			} else if  (!$authorization->includeScopes($scopes)) {
				error_log("scope not satisfied.,");
				$remainingScopes = $authorization->remainingScopes($scopes);
			}

			$e = new So_AuthorizationRequired();
			// echo '<pre>Remaiing scopes: '; print_r($scopes); print_r($remainingScopes); exit;
			$e->scopes = $remainingScopes;
			$e->client_id = $request->client_id;
			throw $e;
		}


		$expires_in = 3600*8; // 8 hours
		if (in_array('longterm', $scopes)) {
			$expires_in = 3600*24*680; // 180 days
		}


		// Handle the various response types. code or token
		if ($request->response_type === 'token') {


			$accesstoken = So_AccessToken::generate($clientconfig['client_id'], $userid, $userdata, $scopes, false, $expires_in);
			$this->store->putAccessToken($request->client_id, $userid, $accesstoken);
			error_log('Ive generated a token: ' . var_export($accesstoken->getToken(), true));
			$tokenresponse = new So_TokenResponse($accesstoken->getToken());
			if ($request->state) {
				$tokenresponse->state = $request->state;
			}
			
			$tokenresponse->sendRedirect($url, true);
			return;


		} else if ($request->response_type === 'code') {

			$authcode = So_AuthorizationCode::generate($request->client_id, $userid, $userdata, $scopes, $expires_in);
			$this->store->putCode($authcode);
			
			$response = $request->getResponse(array('code' => $authcode->code));
			$response->sendRedirect($url);
			return;

		} else {
			throw new Exception('Unsupported response_type in request. Only supported code and token.');
		}

	}
	
	public function token() {
		$tokenrequest = new So_TokenRequest($_REQUEST);
		$tokenrequest->parseServer($_SERVER);

		error_log('Access token endpoint: ' . var_export($_REQUEST, true));
		error_log("Token request: " . var_export($tokenrequest, true));
		
		
		if ($tokenrequest->grant_type === 'authorization_code') {
			
			$clientconfig = $this->store->getClient($tokenrequest->client_id);
			$tokenrequest->checkCredentials($clientconfig['client_id'], $clientconfig['client_secret']);
			$code = $this->store->getCode($clientconfig['client_id'], $tokenrequest->code);
			$accesstoken = So_AccessToken::generate($clientconfig['client_id'], $code->userid, $code->userdata, $code->scopes, $code->tokenexpiresin);
			$this->store->putAccessToken($clientconfig['client_id'], $code->userid, $accesstoken);
			error_log('Ive generated a token: ' . var_export($accesstoken->getToken(), true));
			$tokenresponse = new So_TokenResponse($accesstoken->getToken());
			
			$tokenresponse->sendBody();
			
		} else {
			throw new So_Exception('invalid_grant', 'Invalid [grant_type] provided to token endpoint.');
		}
		
		return;

	}
	
	public function runToken() {
		$req = $_SERVER['REQUEST_URI'];
		
		if (preg_match('|/token(\?.*)?$|', $req)) {
			self::token();
			return;
		}
		
	}
	
	public function runInfo() {
		$req = $_SERVER['REQUEST_URI'];
		
		if (preg_match('|/info(\?.*)?$|', $req)) {
			self::info();
			return;
		}
		
	}
	
	public function runAuthenticated($userid) {
		$req = $_SERVER['REQUEST_URI'];
		
		if (preg_match('|/authorization(\?.*)?$|', $req)) {
			self::authorization($userid);
			return;
		}
	}
	
}


class So_Authorization {
	public $userid, $client_id, $issued, $scope, $stored = false;
	function __construct($userid = null, $client_id = null, $scope = null, $issued = null) {
		$this->userid = $userid;
		$this->client_id = $client_id;
		$this->scope = $scope;
		$this->issued = $issued;
		
		if ($this->issued === null && $this->stored === false) $this->issued = time();
	}
	public function addScopes($scopes) {
		foreach($scopes AS $scope) {
			if (!in_array($scope, $this->scope)) $this->scope[] = $scope;
		}
	}
	static function fromObj($obj) {
		$n = new So_Authorization();
		if (isset($obj['userid'])) $n->userid = $obj['userid'];
		if (isset($obj['client_id'])) $n->client_id = $obj['client_id'];
		if (isset($obj['scope'])) $n->scope = $obj['scope'];
		if (isset($obj['issued'])) $n->issued = $obj['issued'];
		$n->stored = true;
		return $n;
	}
	function getScope() {
		return join(' ', $this->scope);
	}
	function getObj() {
		$obj = array();
		foreach($this AS $key => $value) {
			if (in_array($key, array('stored'))) continue;
			if ($value === null) continue;
			$obj[$key] = $value;
		}
		return $obj;
	}
	function includeScopes($requiredscopes) {
		if ($requiredscopes === null) return true;
		// echo '<pre>'; print_r($requiredscopes); exit;
		assert('is_array($requiredscopes)');
		foreach($requiredscopes AS $rs) {
			if (!in_array($rs, $this->scope)) return false;
		}
		return true;
	}
	public function remainingScopes($requiredscopes) {
		return array_diff($requiredscopes, $this->scope);
	}
}

class So_AuthorizationCode {
	public $issued, $validuntil, $tokenexpiresin, $code, $userid, $userdata, $client_id, $scope;
	function __construct() {
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
		$n = new So_AuthorizationCode();
		if (isset($obj['issued'])) $n->issued = $obj['issued'];
		if (isset($obj['validuntil'])) $n->validuntil = $obj['validuntil'];
		if (isset($obj['tokenexpiresin'])) $n->tokenexpiresin = $obj['tokenexpiresin'];
		if (isset($obj['code'])) $n->code = $obj['code'];
		if (isset($obj['userid'])) $n->userid = $obj['userid'];
		if (isset($obj['userdata'])) $n->userid = $obj['userdata'];
		if (isset($obj['client_id'])) $n->client_id = $obj['client_id'];
		return $n;
	}
	
	static function generate($client_id, $userid, $userdata, $scope, $expires_in = 3600) {
		$n = new So_AuthorizationCode();
		$n->userid = $userid;
		$n->client_id = $client_id;
		$n->issued = time();
		$n->validuntil = time() + 3600;
		$n->tokenexpiresin = $expires_in;
		$n->code = So_Utils::gen_uuid();
		$n->scope = $scope;
		return $n;
	}
}

class So_AccessToken {
	public $issued, $validuntil, $client_id, $userid, $access_token, $token_type, $refresh_token, $scope, $userdata;
	
	function __construct() {
	}
	static function generate($client_id, $userid, $userdata, $scope = null, $refreshtoken = true, $expires_in = 3600) {
		$n = new So_AccessToken();
		$n->userid = $userid;
		$n->client_id = $client_id;
		$n->issued = time();
		$n->validuntil = time() + $expires_in;
		$n->access_token = So_Utils::gen_uuid();
		$n->userdata = $userdata;
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


class So_InvalidResponse extends So_Exception {
	public $raw;
}

class So_Exception extends Exception {
	protected $code, $state;
	function __construct($code, $message, $state = null) {
		parent::__construct($message);
		$this->code = $code;
		$this->state = $state;
	}
	function getResponse() {
		$message = array('error' => $this->code, 'error_description' => $this->getMessage() );
		if (!empty($this->state)) $message['state'] = $this->state;
		$m = new So_ErrorResponse();
	}
}




// ---------- // ---------- // ---------- // ----------  MESSAGES




class So_Message {
	function __construct($message) {	
	}
	function asQS() {
		$qs = array();
		foreach($this AS $key => $value) {
			if (empty($value)) continue;
			$qs[] = urlencode($key) . '=' . urlencode($value);
		}
		return join('&', $qs);
	}

	public function getRedirectURL($endpoint, $hash = false) {
		if ($hash) {
			$redirurl = $endpoint . '#' . $this->asQS();
		} else {
			$redirurl = $endpoint . '?' . $this->asQS();
		}
		return $redirurl;
	}
	
	public function sendRedirect($endpoint, $hash = false) {
		$redirurl = $this->getRedirectURL($endpoint, $hash);		
		header('Location: ' . $redirurl);
		exit;
	}
	public function sendBody() {
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($this);
		exit;
	}
	

	
	public function post($endpoint) {
		error_log('posting to endpoint: ' . $endpoint);
		$postdata = $this->asQS();
		
		error_log('Sending body: ' . $postdata);
		
		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded' . "\r\n",
		        'content' => $postdata
		    )
		);
		$context  = stream_context_create($opts);

		$result = file_get_contents($endpoint, false, $context);
		
		$resultobj = json_decode($result, true);
		

		return $resultobj;
	}
}

class So_Request extends So_Message {
	function __construct($message) {
		parent::__construct($message);
	}
}

abstract class So_AuthenticatedRequest extends So_Request {
	public $client_id;
	protected $client_secret;
	function __construct($message) {
		parent::__construct($message);
		$this->client_id		= So_Utils::optional($message, 'client_id');
		$this->client_secret		= So_Utils::optional($message, 'client_secret');
	}
	function setClientCredentials($u, $p) {
		error_log('setClientCredentials ('  . $u. ',' . $p. ')');
		$this->client_id = $u;
		$this->client_secret = $p;
	}
	function getAuthorizationHeader() {
		if (empty($this->client_id) || empty($this->client_secret)) throw new Exception('Cannot authenticate without username and passwd');
		return 'Authorization: Basic ' . base64_encode($this->client_id . ':' . $this->client_secret);
	}
	function checkCredentials($u, $p) {
		if ($u !== $this->client_id) throw new So_Exception('invalid_grant', 'Invalid client credentials');
	}
	function parseServer($server) {
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			$this->client_id = $_SERVER['PHP_AUTH_USER'];
		}
		if (isset($_SERVER['PHP_AUTH_PW'])) {
			$this->client_secret = $_SERVER['PHP_AUTH_PW'];
		}
		error_log('Authenticated request with [' . $this->client_id . '] and [' . $this->client_secret . ']');
	}
	
	protected function getContentType($hdrs) {
		foreach ($hdrs AS $h) {
			if (preg_match('|^Content-[Tt]ype:\s*text/plain|i', $h, $matches)) {
				return 'application/x-www-form-urlencoded';
			} else if (preg_match('|^Content-[Tt]ype:\s*application/x-www-form-urlencoded|i', $h, $matches)) {
				return 'application/x-www-form-urlencoded';
			}
		}
		return 'application/json';
	}
	
	protected function getStatusCode($hdrs) {
		$explode = explode(' ', $hdrs[0]);
		return $explode[1];
	}
	
	public function post($endpoint) {
		
		$postdata = $this->asQS();		
		error_log('Posting typically a token request: ' .var_export(array(
		 		'endpoint' => $endpoint,
				'header' => $this->getAuthorizationHeader(),
				'body' => $postdata,
		 	), true));
		So_log::debug('Posting typically a token request: ',
		 	array(
		 		'endpoint' => $endpoint,
				'header' => $this->getAuthorizationHeader(),
				'body' => $postdata,
		 	));
		
		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n" . 
				// '',
				$this->getAuthorizationHeader() . "\r\n",
		        'content' => $postdata
		    )
		);
		$context  = @stream_context_create($opts);

		error_log("Posting to ednpoint: " . $endpoint);
		$result = @file_get_contents($endpoint, false, $context);
		$statuscode = $this->getStatusCode($http_response_header);
		
		if ((string)$statuscode !== '200') {
			
			So_log::error('When sending a token request, using a provided code, the returned status code was not 200 OK.',
				array(
					'resultdata' => $result,
					'headers' => $http_response_header
				)
			);
			
			throw new Exception('When sending a token request, using a provided code, the returned status code was not 200 OK.');
		}
		$ct = $this->getContentType($http_response_header);
		
		if ($ct === 'application/json') {

			error_log('RESPONSE WAS: '. var_export($result, true));

			$resultobj = json_decode($result, true);
			if ($resultobj === null) {
				$e = new So_InvalidResponse('na', 'Statuscode 200, but content was invalid JSON, on Token endpoint.');
				$e->raw = $result;
				throw $e;
			}
			
		} else if ($ct === 'application/x-www-form-urlencoded') {
			
			$resultobj = array();
			parse_str(trim($result), $resultobj);
			
		} else {
			// cannot be reached, right now.
			throw new Exception('Invalid content type in Token response.');
		}
		error_log("Done. Output was: " . $result );
		So_log::debug('Successfully parsed the Token Response body',array('response' => $resultobj));
		return $resultobj;
	}
	
}

class So_AuthRequest extends So_Request {
	public $response_type, $client_id, $redirect_uri, $scope, $state;
	function __construct($message) {
		parent::__construct($message);
		$this->response_type	= So_Utils::prequire($message, 'response_type', array('code', 'token'), true);		
		$this->client_id 		= So_Utils::prequire($message, 'client_id');
		$this->redirect_uri		= So_Utils::optional($message, 'redirect_uri');
		$this->scope			= So_Utils::spacelist(So_Utils::optional($message, 'scope'));
		$this->state			= So_Utils::optional($message, 'state');
	}
	
	function asQS() {
		$qs = array();
		foreach($this AS $key => $value) {
			if (empty($value)) continue;
			if ($key === 'scope') {
				$qs[] = urlencode($key) . '=' . urlencode(join(' ', $value));
				continue;
			} 
			$qs[] = urlencode($key) . '=' . urlencode($value);
		}
		return join('&', $qs);
	}
	
	function getResponse($message) {
		$message['state'] = $this->state;
		return new So_AuthResponse($message);
	}
}

class So_TokenRequest extends So_AuthenticatedRequest {
	public $grant_type, $code, $redirect_uri;
	function __construct($message) {
		parent::__construct($message);
		$this->grant_type		= So_Utils::prequire($message, 'grant_type', array('authorization_code', 'refresh_token'));
		$this->code 			= So_Utils::prequire($message, 'code');
		$this->redirect_uri		= So_Utils::optional($message, 'redirect_uri');
	}

}

class So_Response extends So_Message {
	function __construct($message) {
		parent::__construct($message);
	}
}

class So_TokenResponse extends So_Response {
	public $access_token, $token_type, $expires_in, $refresh_token, $scope, $state;
	function __construct($message) {
		
		// Hack to add support for Facebook. Token type is missing.
		if (empty($message['token_type'])) $message['token_type'] = 'bearer';
		
		parent::__construct($message);
		$this->access_token		= So_Utils::prequire($message, 'access_token');
		$this->token_type		= So_Utils::prequire($message, 'token_type');
		$this->expires_in		= So_Utils::optional($message, 'expires_in');
		$this->refresh_token	= So_Utils::optional($message, 'refresh_token');
		$this->scope			= So_Utils::optional($message, 'scope');
		$this->state			= So_Utils::optional($message, 'state');
	}
}

class So_ErrorResponse extends So_Response {
	public $error, $error_description, $error_uri, $state;
	function __construct($message) {
		parent::__construct($message);
		$this->error 				= So_Utils::prequire($message, 'error', array(
			'invalid_request', 'invalid_client', 'invalid_grant', 'unauthorized_client', 'unsupported_grant_type', 'invalid_scope'
		));
		$this->error_description	= So_Utils::optional($message, 'error_description');
		$this->error_uri			= So_Utils::optional($message, 'error_uri');
		$this->state				= So_Utils::optional($message, 'state');
	}
}

class So_AuthResponse extends So_Message {
	public $code, $state;
	function __construct($message) {
		parent::__construct($message);
		$this->code 		= So_Utils::prequire($message, 'code');
		$this->state		= So_Utils::optional($message, 'state');
	}
	function getTokenRequest($message = array()) {
		$message['code'] = $this->code;
		$message['grant_type'] = 'authorization_code';
		return new So_TokenRequest($message);
	}
}




// ---------- // ---------- // ---------- // ----------  Utils

class So_Utils {
	
	
	static function spacelist($arg) {
		if ($arg === null) return null;
		return explode(' ', $arg);
	}
	
	static function geturl() {
		$url = ((!empty($_SERVER['HTTPS'])) ? 
			"https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : 
			"http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
		return $url;
	}
	
	// Found here:
	// 	http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
	static function gen_uuid() {
	    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	        // 32 bits for "time_low"
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

	        // 16 bits for "time_mid"
	        mt_rand( 0, 0xffff ),

	        // 16 bits for "time_hi_and_version",
	        // four most significant bits holds version number 4
	        mt_rand( 0, 0x0fff ) | 0x4000,

	        // 16 bits, 8 bits for "clk_seq_hi_res",
	        // 8 bits for "clk_seq_low",
	        // two most significant bits holds zero and one for variant DCE1.1
	        mt_rand( 0, 0x3fff ) | 0x8000,

	        // 48 bits for "node"
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	    );
	}
	
	public static function optional($message, $key) {
		if (empty($message[$key])) return null;
		return $message[$key];
	}
	public static function prequire($message, $key, $values = null, $multivalued = false) {
		if (empty($message[$key])) {
			throw new So_Exception('invalid_request', 'Message does not include prequired parameter [' . $key . ']');
		}
		if (!empty($values)) {
			if ($multivalued) {
				$rvs = explode(' ', $message[$key]);
				foreach($rvs AS $v) {
					if (!in_array($v, $values)) {
						throw new So_Exception('invalid_request', 'Message parameter [' . $key . '] does include an illegal / unknown value.');
					}					
				}
			}
			if (!in_array($message[$key], $values)) {
				throw new So_Exception('invalid_request', 'Message parameter [' . $key . '] does include an illegal / unknown value.');
			}
		} 
		return $message[$key];
	}
}

