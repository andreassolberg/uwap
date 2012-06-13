<?php

/*
 * This API is reached through
 * 
 * 		appid.uwap.org/_/api/appconfig.php
 *
 * This is used only indirectly through the UWAP core js API.
 * Allows management of your own applications.
 */

require_once('../../lib/autoload.php');

try {


	$config = new Config();
	$subhost = $config->getID();

	$auth = new Auth();
	$auth->req();

	$result = array();
	$result['status'] = 'ok';


	if (empty($_SERVER['PATH_INFO']) || strlen($_SERVER['PATH_INFO']) < 2) {
		throw new Exception('Missing part of the URL path.');
	}
	$parameters = explode('/', substr($_SERVER['PATH_INFO'], 1));
	$method = strtolower($_SERVER['REQUEST_METHOD']);
	$first = array_shift($parameters);

	if ($first === 'apps') {

		if ($method === 'get') {
			$ac = new Config();
			$listing = $ac->getMyApps($auth->getRealUserID());
			$result['data'] = $listing;

		} else if ($method === 'post') {
			$object = json_decode(file_get_contents('php://input'), true);
			$id = $object["id"];
			Utils::validateID($id);
			$config->store($object, $auth->getRealUserID());

			$ac = new Config($id);
			$result['data'] = $ac->getConfig();

		} else {
			throw new Exception('Invalid method');
		}


	} else if ($first === 'app') {

		if(empty($parameters)) throw new Exception('Missing app id in request');

		$subid = array_shift($parameters);
		Utils::validateID($subid);
		$ac = new Config($subid);


		if (empty($parameters)) {

			$result['data'] = $ac->getConfig();
			$result['data']['davcredentials'] = $ac->getDavCredentials($auth->getRealUserID());
			$result['data']['appdata-stats'] = $ac->getStats();
			$result['data']['files-stats'] = $ac->getFilestats();
			$result['data']['user-stats'] = $ac->getUserStats();
			// echo '<pre>';
			// print_r($result); exit;

		} else {

			$second = array_shift($parameters);

			if ($second === 'davcredentials') {
				$result['data'] = $ac->getDavCredentials($auth->getRealUserID());
			} else {
				throw new Exception('Invalid app property part of URL');
			}
		}

		
		
	} else if ($first === 'check') {
		if(empty($parameters)) throw new Exception('Missing app id in request');

		$subid = array_shift($parameters);
		Utils::validateID($subid);
		$result['data'] = !$config->exists($subid);



	} else {
		throw new Exception('Invalid request URI');
	}

	
	// if (!empty($_REQUEST['get'])) {

	// 	Utils::validateID($_REQUEST['get']);
	// 	$ac = new Config($_REQUEST['get']);
	// 	$result['data'] = $ac->getConfig();

	// } else if (!empty($_REQUEST['check'])) {

	// 	Utils::validateID($_REQUEST['check']);
	// 	$result['data'] = !$config->exists($_REQUEST['check']);

	// } else if (!empty($_REQUEST['store'])) {

	// 	$c = json_decode($_REQUEST['store']);
	// 	Utils::validateID($c['id']);
	// 	$config->store('appconfig', $auth->getRealUserID(), $c);

	// 	$ac = new Config($c['id']);
	// 	$result['data'] = $ac->getConfig();

	// } else {

	// 	$ac = new Config();
	// 	$listing = $ac->getMyApps($auth->getRealUserID());
	// 	$result['data'] = $listing;

	// }


	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($result);


} catch(Exception $error) {

	$result = array();
	$result['status'] = 'error';
	$result['message'] = $error->getMessage();
	echo json_encode($result);

}


