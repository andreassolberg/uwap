<?php

/*
 * This API is reached through
 * 
 * 		appid.uwap.org/_/api/applisting.php
 *
 * This is used only indirectly through the UWAP core js API.
 * Returns list of applications to be used in public display, such as app store.
 */

require_once('../../lib/autoload.php');

try {

	$ac = new Config();
	$listing = $ac->getAppListing();

	// foreach ($apps as $key) {
	// 	$ac = new Config($key);
	// 	$appconfig[$key] = $ac->getConfig();
	// }
	
	$result = array(
		'status' => 'ok',
		'data' => $listing
	);

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($result);


} catch(Exception $error) {

	$result = array();
	$result['status'] = 'error';
	$result['message'] = $error->getMessage();
	echo json_encode($result);

}


