<?php

/*
 * This API is reached through
 * 
 * 		appid.uwap.org/_/api/auth.php
 *
 * This is used only indirectly through the UWAP core js API.
 * This API checks if the user is authenticated, and returns userdata if so.
 * If the user is not authenticated, nothing is returned ({status: error}).
 */

require_once('../../lib/autoload.php');


try {

	$config = Config::getInstance();
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


