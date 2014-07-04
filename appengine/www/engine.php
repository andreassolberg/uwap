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


require_once(dirname(dirname(__FILE__)) . '/lib/autoload.php');



// TODO: Set proper caching headers for production. We use this for development.
header('Pragma: no-cache"');
header('Expires: Thu, 1 Jan 1970 00:00:00 GMT');
header('Cache-Control: max-age=0, no-store, no-cache, must-revalidate');



try {

	$globalconfig = GlobalConfig::getInstance();
	$app = $globalconfig->getApp();

} catch(Exception $e) {

	header("HTTP/1.0 404 Not Found");
	require_once('../../templates/404.php');
	// echo "Not found";
	exit;
}


if (!($app instanceof App))
	throw new Exception('AppEngine may only run on Apps');


try {

	$h = new Static_File();
	UWAPLogger::debug('engine', 'Accessing a static file from app area.', $h->getInfo());

	// echo "Running app <pre>"; print_r($app);
	$h->show();

} catch(Exception $e) {
	header("X-Error: Notfound", true, 404);
	UWAPLogger::error('engine', 'Error processing a static file from app area.', $e->getMessage());

	echo "Error: " . $e->getMessage();
}


