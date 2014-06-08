<?php



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
		return null;
		// throw new Exception('Could not get provided Access Token');
	}
	
	
	public function getToken($required = true) {

		$tokenstr = $this->getProvidedToken();
		try {
			if (empty($tokenstr)) {
				if ($required) {
					throw new Exception('Could not find provided Access token in request');
				} else {
					return null;
				}
			}
			$token = $this->store->getToken($tokenstr);	
		} catch(Exception $e) {
			throw new So_UnauthorizedRequest('unauthorized_client', 'Could not find provided token');
		}
		
		return $token;
	}

	/*
	 * Check a token provided by a user, through the authorization header.
	 */
	function checkToken($scope = null) {
		
		$tokenstr = $this->getProvidedToken();
		$token = $this->store->getToken($tokenstr);
		try {
			$token->requireValid($scope);
		} catch(Exception $e) {
			throw new So_UnauthorizedRequest('unauthorized_client', 'Insufficient scope on provided token. Missing [' . join(',', $scope) . ']');	
		}
		
		
		return $token;
	}
	
	
	private function validateRedirectURI(So_AuthRequest $request, $clientconfig) {

		$configuredRedirectURI = $clientconfig->get('redirect_uri', null);


		if (is_array($configuredRedirectURI)) {
			if (empty($request->redirect_uri)) {
				// url not specified in request, returning first entry from config
				return $configuredRedirectURI[0];
				
			} else {
				// url specified in request, returning if is substring match any of the entries in config
				foreach($configuredRedirectURI AS $su) {
					if (strpos($request->redirect_uri, $su) === 0) {
						return $request->redirect_uri;
					}
				}
			}
		} else if (!empty($configuredRedirectURI)) {
			if (empty($request->redirect_uri)) {
				// url not specified in request, returning the only entry from config
				return $configuredRedirectURI;
				
			} else {
				// url specified in request, returning if is substring match the entry in config
				if (strpos($request->redirect_uri, $configuredRedirectURI) === 0) {
					return $request->redirect_uri;
				}	
			}
		}
		
		throw new So_Exception('invalid_request', 'Not able to resolve a valid redirect_uri for client');
	}
	

	public function setAuthorization($client_id, $userid, $scopes) {

		error_log('setAuthorization($client_id, $userid, $scopes) ' . "($client_id, $userid, $scopes)");
		$clientconfig = $this->store->getClient($client_id);
		$acceptedScopes = array_intersect($scopes, $clientconfig->get('scopes', array()));

		$authorization = $this->store->getAuthorization($client_id, $userid);
		if ($authorization === null) {
			$authorization = new So_Authorization($userid, $client_id, $scopes);	
		}

		if (!empty($scopes)) {
			$authorization->addScopes($scopes);
		}
		$this->store->setAuthorization($authorization);
	}


	public function authorizationFailed($error, $url, $descr) {
		$request = new So_AuthRequest($_REQUEST);
		$clientconfig = $this->store->getClient($request->client_id);
		$url = $this->validateRedirectURI($request, $clientconfig);

		$msg = array(
			'error' => $error,
			'error_description' => $url,
			'error_uri' => $descr
		);
		// print_r($msg); 

		$tokenresponse = new So_ErrorResponse($msg);
		if ($request->state) {
			$tokenresponse->state = $request->state;
		}
		
		$tokenresponse->sendRedirect($url, true);
	}

	public function authorization($userid, $userdata = null) {
		
		// echo "authorization()"; exit;

		$request = new So_AuthRequest($_REQUEST);
		$clientconfig = $this->store->getClient($request->client_id);
		$url = $this->validateRedirectURI($request, $clientconfig);
		
		$authorization = $this->store->getAuthorization($request->client_id, $userid);

		$scopes = $clientconfig->get('scopes', array());
		if (!empty($request->scope)) {
			// Only consider scopes that the client is authorized to ask for.
			$scopes = array_intersect($request->scope, $scopes);
		}

		// echo '<pre>';
		// print_r($request); print_r($clientconfig['scopes']);



		// echo "authorization()<pre>"; print_r($authorization); exit;

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
			$expires_in = 3600*24*680; // 680 days
		}


		// Handle the various response types. code or token
		if ($request->response_type === 'token') {



			$accesstoken = So_AccessToken::generate($clientconfig->get('id'), $userid, $userdata, $scopes, false, $expires_in);
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

			// echo "put a code <pre>"; print_r($authcode); echo '</pre>'; exit;
			
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
		
		// print_r('Access token endpoint: ' . var_export($_REQUEST, true));
		// print_r($tokenrequest); 
		// exit;

		
		if ($tokenrequest->grant_type === 'authorization_code') {
			
			$clientconfig = $this->store->getClient($tokenrequest->client_id);
			$tokenrequest->checkCredentials($clientconfig->get('id'), $clientconfig->get('client_secret'));
			$code = $this->store->getCode($clientconfig->get('id'), $tokenrequest->code);

			// echo "got a code <pre>"; print_r($code); echo '</pre>'; exit;

			$accesstoken = So_AccessToken::generate($clientconfig->get('id'), $code->userid, $code->userdata, $code->scope, $code->tokenexpiresin);
			$this->store->putAccessToken($clientconfig->get('id'), $code->userid, $accesstoken);
			error_log('Ive generated a token: ' . var_export($accesstoken->getToken(), true));
			$tokenresponse = new So_TokenResponse($accesstoken->getToken());
			
			$tokenresponse->sendBody();
			
		} else if ($tokenrequest->grant_type === 'client_credentials') {

			$clientconfig = $this->store->getClient($tokenrequest->client_id);

			if ($clientconfig->get('id') !== $_SERVER['PHP_AUTH_USER']) {
				throw new So_Exception('invalid_grant', 'Invalid client_id.');
			}
			if ($clientconfig->get('client_secret') !== $_SERVER['PHP_AUTH_PW']) {
				throw new So_Exception('invalid_grant', 'Invalid secret.');
			}

			// echo "Juhuuuu \n";

			$scopes = $clientconfig->get('scopes', null);
			if (empty($scopes)) {
				throw new Exception('Client configuration is missing a list of [scopes] for this client.');
			}

			$expiresin = time() + 3600;
			$accesstoken = So_AccessToken::generate($clientconfig->get('id'), null, null, $clientconfig->get('scopes', array()), $expiresin);
			$accesstoken->clientdata = $clientconfig->get('id');

			// error_log("AT: " . json_encode($accesstoken)); 

			$this->store->putAccessToken($clientconfig->get('id'), null, $accesstoken);
			error_log('Ive generated a token: ' . var_export($accesstoken->getToken(), true));
			$tokenresponse = new So_TokenResponse($accesstoken->getToken());
			
			$tokenresponse->sendBody();

			// echo "\nu: " . $_SERVER['PHP_AUTH_USER'];
			// echo "\np: " . $_SERVER['PHP_AUTH_PW'];
			// echo "\n";

			// echo "request was";
			// print_r($tokenrequest);
			// print_r($clientconfig);
			exit;

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
