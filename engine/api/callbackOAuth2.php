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

	
	$store = new UWAPStore();
	$auth = new Auth();


	error_log("Config " . json_encode($handlerconfig));

	if (isset($handlerconfig["sharedtokens"]) && $handlerconfig["sharedtokens"] === true) {
		error_log("SHARED Tokens: true");
		$userid = '_sharedtokens';
	} else {
		error_log("SHARED Tokens: false");
		$auth->req();
		// $userdata = $auth->getUserdata();
		$userid = $auth->getRealUserID();
	}


	$client = new So_Client(new So_StorageUWAP($userid));
	$client->callback($userid);


} catch(Exception $e) {

	// For now, just dump the error on the user.
	echo '<pre>';
	print_r($e);
	echo '</pre>';
}








