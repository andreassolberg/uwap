<?php


/*
 * This script proxies API requests through the API GAtekeeper.
 * 
 * 		*.gk.uwap.org/<anything>
 *
 */

require_once(dirname(dirname(__FILE__)) . '/lib/autoload.php');

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: HEAD, GET, OPTIONS, POST, DELETE, PATCH");
header("Access-Control-Allow-Headers: Authorization, X-Requested-With, Origin, Accept, Content-Type");
header("Access-Control-Expose-Headers: Authorization, X-Requested-With, Origin, Accept, Content-Type");


if (strtolower($_SERVER['REQUEST_METHOD']) === 'options') {
	header('Content-Type: application/json; charset=utf-8');
	exit;
}


try {

	$globalconfig = GlobalConfig::getInstance();
	$app = $globalconfig->getApp(null, 'gk');


} catch(Exception $e) {

	header("HTTP/1.0 404 Not Found");
	require_once('../../templates/404.php');
	// echo "Not found";
	exit;
}

if (!($app instanceof APIProxy))
	throw new Exception('AppEngine may only run on APIProxies');


try {


	$h = new GateKeeper($app);
	// UWAPLogger::debug('engine', 'Accessing a SOA proxied endpoint', $h->getInfo());
	$h->show();



} catch(Exception $e) {
	header("X-Error: Notfound", true, 404);
	UWAPLogger::error('engine', 'Error processing a static file from app area.', $e->getMessage());

	echo "Error: " . $e->getMessage();
}





