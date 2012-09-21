<?php

/*
 * This endpoint is where the user is redirected afther authorization with OAuth 1.X
 * 
 * 		appid.uwap.org/_/api/callbackOAuth.php
 *
 * We'll migrate to start using:
 * 		core.uwap.org/_/callbackoauth2...
 *
 * The user should never stop and see here, it will be redirected back to the 'redirect'
 * parameter, after the access token has been stored in the user token storage.
 * 
 */

require_once('../../lib/autoload.php');


try {


	/*
	 * Make sure the user is authenticated. Check the token.
	 */

	$authresponse = new So_AuthResponse($_REQUEST);
	$stateobj = So_StorageUWAP::getStateStatic($authresponse->state);

	// echo '<pre>';
	// print_r($stateobj);
	// echo '</pre>';
	// exit;

	$provider_id = $stateobj["provider_id"];
	$userid = $stateobj['uwap-userid'];
	$appid = $stateobj['appid'];


	$client = new So_Client(new So_StorageUWAP($userid, $appid));
	$client->callback($userid);


} catch(Exception $e) {

	// For now, just dump the error on the user.
	echo '<pre>';
	print_r($e);
	echo '</pre>';
}


