<?php

/*
 * This script is the static app data proxy. It will shuffle static files. 
 * 
 * 		appid.uwap.org/<anything>
 *
 * This is used only indirectly through the UWAP core js API.
 * This API checks if the user is authenticated, and returns userdata if so.
 * If the user is not authenticated, nothing is returned ({status: error}).
 */

require_once('../lib/autoload.php');

error_log( "show about to go");


if (Config::getValue('type') === 'app') {

	try {


		$h = new Static_File();
		$h->show();

	} catch(Exception $e) {
		header("X-Error: Notfound", true, 404);
		echo "Error: " . $e->getMessage();
	}

} else if (Config::getValue('type') === 'proxy') {


	// Specify domains from which requests are allowed
	header('Access-Control-Allow-Origin: *');

	// Specify which request methods are allowed
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

	// Additional headers which may be sent along with the CORS request
	// The X-Requested-With header allows jQuery requests to go through
	header('Access-Control-Allow-Headers: X-Requested-With, Authorization');

	$h = new Proxy_REST();
	$h->show();


} else {
	throw new Exception('Unknown type.');
}

