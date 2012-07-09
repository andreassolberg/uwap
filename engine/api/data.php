<?php

/*
 * This API is reached through
 * 
 * 		appid.uwap.org/_/api/data.php
 *
 * This is used only indirectly through the UWAP core js API.
 * Data is fetched from a remote source and returned, using the specified handler, 
 * such as OAuth1, basic auth or similar
 */

require_once('../../lib/autoload.php');


try {

	if (empty($_REQUEST['args'])) {
		throw new Exception('Missing parameter [args]');
	}
	$args = json_decode($_REQUEST['args'], true);
	
	if (empty($args['url'])) {
		throw new Exception("Missing parameter [url]");
	}

	$url = $args["url"];
	$handler = "plain";

	if (!empty($args["handler"])) $handler = $args["handler"];
	
	$client = HTTPClient::getClient($handler);
	$result = $client->get($url, $args);

	// echo "result was"; print_r(json_encode($result));

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($result);

} catch(Exception $error) {

	$result = array();
	$result['status'] = 'error';
	$result['message'] = $error->getMessage();
	echo json_encode($result);

}


