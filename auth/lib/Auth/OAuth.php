<?php


class OAuth {

	protected $storage;
	protected $server;
	protected $auth;

	function __construct() {
		$this->storage = new So_StorageServerUWAP();
		$this->server  = new So_Server($this->storage);
		// $this->auth = new AuthBase();

		$this->auth = new Authenticator();
	}



	/**
	 * Process the HTML Form POST where the user has authorized the client with the requested scopes to get
	 * an access token.
	 * @return [type] [description]
	 */
	function processAuthorizationResponse() {


		$this->auth->req(true, false);
		$user = $this->auth->getUser();
		$verifier = $user->getVerifier();

		// echo '<pre>Got this request '; print_r($_REQUEST);

		if (empty($_REQUEST['verifier'])) throw new Exception('Missing required parameter [verifier]');
		if (empty($_REQUEST['client_id'])) throw new Exception('Missing required parameter [client_id]');
		// if (empty($_REQUEST['scopes'])) throw new Exception('Missing required parameter [scopes]');
		
		if (isset($_REQUEST["verifier"])) {
			if ($verifier !== $_REQUEST["verifier"]) {
				throw new Exception("Invalid verifier code.");
			}
			// setAuthorization($client_id, $userid, $scopes) ..

			// echo "about to set authorization: ";
			// print_r($config->getID());
			// print_r($auth->getRealUserID());
			
			// exit;
			// $scopes = null;
			// if (!empty($_REQUEST['scopes'])) {
			// 	$scopes = explode(',', $_REQUEST['scopes']);
			// }

			$oauthclient = $this->storage->getClient($_REQUEST['client_id']);
			$scopes = $oauthclient->get('scopes');

			// echo 'about to set scopes '; print_r($scopes);exit;

			// TODO Add support for deciding which scope to use...
			// TODO additional verification that the client_id is not modified by the user.
			$this->server->setAuthorization($_REQUEST["client_id"], $user->get('userid'), $scopes);

			// $this->auth->storeUser();

		}
		$return = $_REQUEST['return'];
		SimpleSAML_Utilities::redirect($return);

	}



	/**
	 * Perform authorization (authentication and check authorization)
	 * In contrast to So_Server, this implementation of the authorization endpoint also
	 * handles authentication, which is not implemented in the gneric So_Server.
	 * 
	 * @return [type] [description]
	 */
	function authorization() {

		// Decide whether to run in passive mode. In passive mode no UI is displayed to the enduser.
		$passive = false;
		if (isset($_REQUEST["passive"]) && $_REQUEST["passive"] === 'true') $passive = true;


		// If SimpleSAML_Auth_State_exceptionId query parameter is set, then something failed 
		// while performing authentication.
		if (!empty($_REQUEST['SimpleSAML_Auth_State_exceptionId'])) {

			// The most likely error is that we are not able to perform passive authentication.
			$this->server->authorizationFailed('access_denied', 'https://docs.uwap.org', 'Unable to perform passive authentication [1]');
			return;

		// to doc...
		} else if (isset($_REQUEST['error']) && $_REQUEST['error'] === '1') {

			$this->server->authorizationFailed('access_denied', 'https://docs.uwap.org', 'Unable to perform passive authentication [2]');
			return;
		}

		// We are initiating authentication using simplesamlphp.
		error_log("About to require authentication from simplesamlphp. Passive (" . var_export($passive, true). ")");

		// (isPassive = false, $allowRedirect, $return)
		$this->auth->req($passive, true);

		// If we pass this, then we are now already authenticated as a user.
		// and we obtain information / attributes about the user.
		$user = $this->auth->getUser(true);


		$userid = $user->get('userid');

		// TODO: Do we really need userdata at this point?
		$userdata = $user->getJSON(array('type' => 'basic'));


		// TODO: Do we need to suport passive requests?? 
		// TODO: Check first clients scopes, then check authorization consent.

		UWAPLogger::info('auth', "User is authenticated. Now ready to check authorization.");


		try {


			// Invoke the So_Server authorization process, when the user is already authenticated at the 
			$this->server->authorization($userid, $userdata);


		// If user has not already authorizated this client with the requested scopes, then invoke the UI with 
		} catch(So_AuthorizationRequired $e) {


			if ($passive) {
				$this->server->authorizationFailed('access_denied', 
					'https://docs.uwap.org/', 'User has not authorized, and were unable to perform passive authorization');	
			}


			$postdata = array();
			$postdata[] = array('key' => 'client_id', 'value' => $e->client_id);
			$postdata[] = array('key' => 'return', 'value' => SimpleSAML_Utilities::selfURL());
			$postdata[] = array('key' => 'verifier', 'value' => $user->getVerifier());

			$oauthclient = $this->storage->getClient($e->client_id);
			$oauthclientdata = $oauthclient->getJSON();

			$data = array(
				'user' => $userdata,
				'posturl' => SimpleSAML_Utilities::selfURLNoQuery(),
				'postdata' => $postdata,
				'client' => $oauthclientdata,
				'HOST' => GlobalConfig::hostname(),
			);

			
			if ($oauthclient->has('uwap-userid')) {
				$ownerid = $oauthclient->get('uwap-userid');
				$owner = User::getByID($ownerid);
				$data['owner'] = $owner->getJSON();
			}
			




			// $data = $config->getConfig();
			// $data = $this->storage->getClient($e->client_id);


			// $scopes = $oauthclient->get('scopes', array());

			// echo '<pre>'; print_r($oauthclient); exit;


			// $owner = $this->auth->getUserBasic($data['uwap-userid']);

			
			// echo "Owner of this application is <pre>"; print_r($owner->get('name')); exit;

			// $permissions = $this->getPermissionText($scopes);

			// header("Content-Type: text/plain"); 
			// // print_r($e);
			// print_r($data); 
			// print_r($postdata); 
			// print_r($owner); 
			// exit;



			$cscopes = $oauthclient->get('scopes', array());
			// $scopes = array();
			// foreach($cscopes AS $scope) {
			// 	$scopes[$scope] = 1;
			// }

			// $data['scopes'] = $scopes;
			$data['clientname'] = $oauthclient->get('name', 'Unnamed client');

			$ap = new AuthorizationPresenter($cscopes);
			$data['perm'] = $ap->getData();

			// echo '<pre>'; print_r($data); exit;

			header("Content-Type: text/html; charset: utf-8");

			$mustache = new Mustache_Engine(array(
				// 'cache' => '/tmp/uwap-mustache',
				'loader' => new Mustache_Loader_FilesystemLoader(dirname(dirname(dirname(__FILE__))).'/templates'),
				// 'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views/partials'),
			));
			$tpl = $mustache->loadTemplate('oauthgrant');
			echo $tpl->render($data);
			exit;

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

	/**
	 * Impmentation of the OAuth 2.0 Token Endpoint.
	 * @return [type] [description]
	 */
	function token() {
		$this->server->token();
	}

	/**
	 * Impementation of an information endpoint, that dumps some useful information 
	 * about the other endpoints etc.
	 * @return [type] [description]
	 */
	function info() {
		$this->server->info();
	}

	function getProvidedToken($required = true) {
		$token = $this->server->getToken($required);
		if ($token === null) return null;
		return new AuthenticatedToken($token);
	}

	function getApplicationScopes($type, $appid) {
		// Return only as set of scopes that matches an app
		// Type might be 'app' or 'soa'
		$scopesMatch = array();
		$scopes = $this->getScopes();
		foreach($scopes AS $scope) {
			if (preg_match('/^' . $type . '_' . $appid . '_(.*)$/', $scope, $matches)) {
				$scopesMatch[] = $matches[1];
			}
		}
		return $scopesMatch;
	}

	function getScopes() {
		$token = $this->server->getToken();
		// print_r($token->scope); exit;
		return $token->scope;
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



