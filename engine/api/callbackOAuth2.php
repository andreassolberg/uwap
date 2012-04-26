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

	$store = new UWAPStore();
	$auth = new Auth();
	$auth->req();
	$userdata = $auth->getUserdata();



	$client = new So_Client(new So_StorageUWAP($auth->getRealUserID()));
	$client->callback($auth->getRealUserID());


} catch(Exception $e) {

	// For now, just dump the error on the user.
	echo '<pre>';
	print_r($e);
	echo '</pre>';
}






// try {

// 	$subconfigobj = new Config();
// 	$subhost = $subconfigobj->getID();
// 	$subconfig = $subconfigobj->getConfig();

// 	$session = SimpleSAML_Session::getInstance();

// 	$store = new UWAPStore();
// 	$auth = new Auth();
// 	$auth->req();
// 	$userdata = $auth->getUserdata();

// 	if (empty($_REQUEST['provider'])) throw new Exception("Missing required parameter in callback URL: provider" );
// 	if (empty($_REQUEST['return'])) throw new Exception("Missing required parameter in callback URL: return" );

// 	$provider = $_REQUEST['provider'];
// 	$return = $_REQUEST['return'];
// 	$requestTokenKey = $_REQUEST['requestToken'];


// 	$oauthconfig = $subconfig['handlers'][$provider];

// 	// echo '<pre>config:'; print_r($oauthconfig); exit;





// 	$consumer = new sspmod_oauth_Consumer($oauthconfig['key'], $oauthconfig['secret']);
	
// 	$state = $session->getData('appengine:oauth', $provider . ':' . $requestTokenKey);
// 	//	echo '<pre>rt: ' . $requestTokenKey . ' state: '; print_r($state); exit;
	
// 	$requestToken = $state['requestToken'];
	
// 	error_log("Is about to retrieve the access token with request token " . $requestToken);
// 	error_log("access url is " . $oauthconfig['access']);

// 	// if (!empty($_REQUEST['oauth_verifier']))
	
// 	// Replace the request token with an access token
// 	$parameter = array();
// 	if ($_REQUEST['oauth_verifier']) {
// 		$parameters['oauth_verifier'] = $_REQUEST['oauth_verifier'];
// 	}
// 	$accessToken = $consumer->getAccessToken( $oauthconfig['access'], $requestToken, $parameters);
// 	error_log ("Got an access token from the OAuth service provider [" . $accessToken->key . "] with the secret [" . $accessToken->secret . "]");

// 	$state = array(
// 		'accessToken' => $accessToken,
// 	);

// 	$store->store("oauth1-client", $auth->getRealUserID(), $state);

// 	// $session->setData('appengine:accesstoken',  $provider, $state);

// 	// $store->store("oauth1-client", );
	
// 	SimpleSAML_Utilities::redirect($return);


// } catch(Exception $e) {
// 	echo 'Error occured: ' . $e->getMessage() . "\n\n";
// }




