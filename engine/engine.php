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


try {

	$globalconfig = GlobalConfig::getInstance();
	$app = $globalconfig->getApp();

} catch(Exception $e) {

	header("HTTP/1.0 404 Not Found");
	require_once('../templates/404.php');
	// echo "Not found";
	exit;
}





if ($app->get('type') === 'app') {


	try {

		$h = new Static_File();
		UWAPLogger::debug('engine', 'Accessing a static file from app area.', $h->getInfo());
		$h->show();

	} catch(Exception $e) {
		header("X-Error: Notfound", true, 404);
		UWAPLogger::error('engine', 'Error processing a static file from app area.', $e->getMessage());

		echo "Error: " . $e->getMessage();
	}

} else if ($app->get('type') === 'proxy') {

	// Specify domains from which requests are allowed
	header('Access-Control-Allow-Origin: *');

	// Specify which request methods are allowed
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

	// Additional headers which may be sent along with the CORS request
	// The X-Requested-With header allows jQuery requests to go through
	header('Access-Control-Allow-Headers: X-Requested-With, Authorization');

	$h = new Proxy_REST();
	// UWAPLogger::debug('engine', 'Accessing a SOA proxied endpoint', $h->getInfo());
	$h->show();


} else {

	UWAPLogger::error('engine', 'Trying to access an WebApp of unknown type.');
	throw new Exception('Unknown type.');
	
}

