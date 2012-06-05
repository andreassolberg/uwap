<?php

/*
 * This endpoint can be loaded in an iFrame to perform an 
 * passive authentication against an idp
 * 
 * 		appid.uwap.org/_/api/passiveAuthiFrame.php
 *
 * This is used only indirectly through the UWAP core js API.
 * A response is passed to the parent window, and handled by the 
 * JS core API.
 * 
 */

require_once('../../lib/autoload.php');

try {

	$config = new Config();
	$subhost = $config->getID();

	$auth = new Auth();

	$data = array("status" => "error");
	if ($auth->check()) {
		$data['status'] = 'ok';
		$data['user'] = $auth->getUserdata();	
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
	
} catch(Exception $error) {

	$result = array();
	$result['status'] = 'error';
	$result['message'] = $error->getMessage();
	echo json_encode($result);

}



