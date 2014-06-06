<?php

/*
 * This endpoint is where the user is redirected afther authorization with OAuth 1.X
 * 
 * 		appid.uwap.org/_/api/callbackOAuth.php
 *
 * The user should never stop and see here, it will be redirected back to the 'redirect'
 * parameter, after the access token has been stored in the user token storage.
 * 
 */

require_once('../../lib/autoload.php');


try {



	$subconfigobj = Config::getInstance();
	$subhost = $subconfigobj->getID();
	$subconfig = $subconfigobj->getConfig();


	$parameters = null;
	$object = null;
	$handler = null;

	// echo $_SERVER['PATH_INFO']; exit;

	if (Utils::route('get', '/([a-zA-Z0-9-_]+)$', &$parameters)) {
		$handler = $parameters[1];
	} else {
		throw new Exception('Missing handler parameter.');
	}

	$handlerconfig = array("type" => "plain");
	if ($handler !== 'plain') {

		if (empty($subconfig["handlers"]) || empty($subconfig["handlers"][$handler])) {
			throw new Exception("Cannot find a authentication handler for [" . $handler . "]");
		}
		$handlerconfig = $subconfig["handlers"][$handler];			
	}





	$session = SimpleSAML_Session::getInstance();

	$store = new UWAPStore();
	$auth = new Auth();
	$auth->req();
	$userdata = $auth->getUserdata();

	// if (empty($_REQUEST['return'])) {
	// 	$return = 'http://test.app.bridge.uninett.no/twitter.html';
	// 	// throw new Exception("Missing required parameter in callback URL: return" );
	// } else {
	// 	$return = $_REQUEST['return'];
	// }

	$provider = $handler;
	
	$requestTokenKey = $_REQUEST['oauth_token'];

	$oauthconfig = $subconfig['handlers'][$provider];

	// echo '<pre>config:'; print_r($oauthconfig); exit;



	$consumer = new sspmod_oauth_Consumer($oauthconfig['client_id'], $oauthconfig['client_secret']);
	
	$state = $session->getData('appengine:oauth', $provider . ':' . $requestTokenKey);
	//	echo '<pre>rt: ' . $requestTokenKey . ' state: '; print_r($state); exit;
	
	$requestToken = $state['requestToken'];
	$return = $state['return'];
	
	error_log("Is about to retrieve the access token with request token " . $requestToken);
	error_log("access url is " . $oauthconfig['access']);

	// if (!empty($_REQUEST['oauth_verifier']))
	
	// Replace the request token with an access token
	$parameter = array();
	if ($_REQUEST['oauth_verifier']) {
		$parameters['oauth_verifier'] = $_REQUEST['oauth_verifier'];
	}
	$accessToken = $consumer->getAccessToken( $oauthconfig['access'], $requestToken, $parameters);
	error_log ("Got an access token from the OAuth service provider [" . $accessToken->key . "] with the secret [" . $accessToken->secret . "]");

	$state = array(
		'app' => $subhost,
		'accessToken' => $accessToken,
	);

	$store->store("oauth1-client", $auth->getRealUserID(), $state);

	// $session->setData('appengine:accesstoken',  $provider, $state);

	// $store->store("oauth1-client", );
	
	SimpleSAML_Utilities::redirect($return);


} catch(Exception $e) {
	echo 'Error occured: ' . $e->getMessage() . "\n\n";
}




