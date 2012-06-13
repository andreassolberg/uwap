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

	if (empty($_REQUEST['url'])) {
		throw new Exception("Missing parameter [url]");
	}

	$url = $_REQUEST["url"];
	$handler = "plain";

	if (!empty($_REQUEST["handler"])) $handler = $_REQUEST["handler"];
	

	$client = HTTPClient::getClient($handler);
	$result = $client->get($url, $_REQUEST);


	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($result);

} catch(Exception $error) {

	$result = array();
	$result['status'] = 'error';
	$result['message'] = $error->getMessage();
	echo json_encode($result);

}


