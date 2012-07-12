<?php

/*
 * This file is part of Solberg-OAuth
 * Read more here: https://github.com/andreassolberg/solberg-oauth
 */


// Load the OAuth library
require_once('../../lib/soauth.php');

try {
	$client = new So_Client();
	
	$token = $client->getToken('proxydemo', 'andreas');
	
	// echo '<pre>Token from (test1, andreas):';
	// print_r($token);
	// echo '</pre>';
	// exit;
	
	// getHTTP($provider_id, $user_id, $url, array $requestScope = null, array $requireScope = null) {
	$data = $client->getHTTP('proxydemo', 'andreas', 'http://proxydemo.app.bridge.uninett.no/api/rest.php', array("openid"));

	
	echo '<p>Got data: <pre>';
	print_r(json_decode($data, true));
	echo '</pre>';
	
	
} catch(Exception $e) {
	
	// For now, just dump the error on the user.
	echo '<pre>';
	print_r($e);
	echo '</pre>';
}

