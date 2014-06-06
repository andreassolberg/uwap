<?php

/*
 * This file is part of Solberg-OAuth
 * Read more here: https://github.com/andreassolberg/solberg-oauth
 */

// Load the OAuth library
require_once('../../lib/soauth.php');

try {
	$client = new So_Client();
	$client->callback('proxydemo', 'andreas');


} catch(Exception $e) {

	// For now, just dump the error on the user.
	echo '<pre>';
	print_r($e);
	echo '</pre>';
}

