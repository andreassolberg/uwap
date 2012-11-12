<?php


class OAuth {

	protected $storage;
	protected $server;
	protected $auth;

	function __construct() {
		$this->storage = new So_StorageServerUWAP();
		$this->server  = new So_Server($this->storage);
		$this->auth = new AuthBase();
	}

	function processAuthorizationResponse() {

		$verifier = $this->auth->getVerifier();

		if (empty($_REQUEST['verifier'])) throw new Exception('Missing required parameter [verifier]');
		if (empty($_REQUEST['scopes'])) throw new Exception('Missing required parameter [scopes]');
		if (empty($_REQUEST['client_id'])) throw new Exception('Missing required parameter [client_id]');

		if (isset($_REQUEST["verifier"])) {
			if ($verifier !== $_REQUEST["verifier"]) {
				throw new Exception("Invalid verifier code.");
			}
			// setAuthorization($client_id, $userid, $scopes) ..

			// echo "about to set authorization: ";
			// print_r($config->getID());
			// print_r($auth->getRealUserID());
			
			// exit;
			$scopes = null;
			if (!empty($_REQUEST['scopes'])) {
				$scopes = explode(',', $_REQUEST['scopes']);
			}

			// echo 'about to set scopes '; print_r($scopes);exit;

			// TODO Add support for deciding which scope to use...
			// TODO additional verification that the client_id is not modified by the user.
			$this->server->setAuthorization($_REQUEST["client_id"], $this->auth->getRealUserID(), $scopes);

			$this->auth->storeUser();

		}
		$return = $_REQUEST['return'];
		SimpleSAML_Utilities::redirect($return);

	}

	function authorization() {



		$passive = false;
		if (isset($_REQUEST["passive"]) && $_REQUEST["passive"] === 'true') $passive = true;

		if (!empty($_REQUEST['SimpleSAML_Auth_State_exceptionId'])) {

			// echo "Failed because user was not authenticated..."; exit;

			$this->server->authorizationFailed('access_denied', 'https://core.uwap.org/oauth/noPassiveAuthentication', 'Unable to perform passive authentication');


		} else if ($passive) {
			// echo "about to passive auth"; exit;
			$this->auth->authenticatePassive();
		} else {
			$this->auth->authenticate();	
		}

		$userid = $this->auth->getRealUserID();
		$userdata = $this->auth->getUserdata();

		$search = $this->auth->getUserBasic($userid);
		if(!empty($search) && !empty($search['a'])) {
			$userdata['a'] = $search['a'];
		}


		// TODO: Do we need to suport passive requests??
		// TODO: Check first clients scopes, then check authorization consent.

		UWAPLogger::info('auth', "User is authenticate. Now ready to check authorization.");


		try {
			$this->server->authorization($userid, $userdata);
		} catch(So_AuthorizationRequired $e) {


			if ($passive) {
				$this->server->authorizationFailed('access_denied', 'https://core.uwap.org/oauth/noPassiveAuthorization', 'Unable to perform passive authorization');	
			}

			$postdata = array();
			// $postdata = $_REQUEST;

			$postdata["client_id"] = $e->client_id;
			$postdata["return"] = SimpleSAML_Utilities::selfURL();
			$postdata["verifier"] = $this->auth->getVerifier();
			$posturl = SimpleSAML_Utilities::selfURLNoQuery();

			$scopes = array();


			if (!empty($e->scopes)) {
				$postdata["scopes"] = join(',', $e->scopes);
				$scopes = $e->scopes;
			}

			// $data = $config->getConfig();
			$data = $this->storage->getClient($e->client_id);

			$owner = $this->auth->getUserBasic($data['uwap-userid']);
			
			$permissions = $this->getPermissionText($scopes);

			// header("Content-Type: text/plain"); 
			// // print_r($e);
			// print_r($data); 
			// print_r($postdata); 
			// print_r($owner); 
			// exit;

			header("Content-Type: text/html; charset: utf-8");
			require_once("../../templates/oauthgrant.php"); exit;

		}

	}

	function getPermissionText($scopes) {
		$res = array();
		foreach($scopes AS $scope){ 
			$res[] = $this->getPermissionTextItem($scope); 
		}
		return $res;
	}

	function getPermissionTextItem($scope) {
		if (preg_match('/^app_([a-z0-9]+)_user$/', $scope, $matches)) {
			return 'Access to all application data for app ' . $matches[1];
		// } else if (preg_match('/^app_([a-z0-9]+)_user$/')) {

		} else if ($scope === 'voot') {
			return 'Access to your group memberships';
		} else if ($scope === 'feedread') {
			return 'Read access to your eduFeed';
		} else if ($scope === 'feedwrite') {
			return 'Access to post on the eduFeed on behalf of you';
		} else {
			return 'Permission [' . $scope . ']';
		}
	}


	function token() {
		$this->server->token();
	}

	function info() {
		$this->server->info();
	}

	function getProvidedToken() {
		return new AuthenticatedToken($this->server->getToken());
	}

	function check($appscopes = array(), $scopes = array()) {

		try {
			$token = $this->server->getToken();
			$client_id = $token->client_id;
			if (!empty($appscopes)) {
				foreach($appscopes AS $as) {
					$scopes[] = $client_id . '_' . $as;
				}	
			}

			// echo "About to check for these scopes "; print_r($scopes); 
			// print_r($token);
			// exit;

			$token = $this->server->checkToken($scopes);

		} catch(So_UnauthorizedRequest $e) {
			header("HTTP/1.1 401 Unauthorized");
			header("X-UWAP-Error: " . $e->getMessage());
			exit;
		}
		
		return new AuthenticatedToken($token);

	}

	
}