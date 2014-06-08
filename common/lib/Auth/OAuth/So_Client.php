<?php


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
				http_parse_headers($hdr, $headers);
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
