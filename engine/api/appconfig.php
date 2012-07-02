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


/*
  * TODO: REstrict access to this API to other apps than 'dev'.
 */

	$config = new Config();
	$subhost = $config->getID();

	$auth = new Auth();
	$auth->req();

	$result = array();
	$result['status'] = 'ok';
	


	$parameters = null;
	$object = null;

	if (Utils::route('get', '/apps$', &$parameters)) {

		$ac = new Config();
		$listing = $ac->getMyApps($auth->getRealUserID());
		$result['data'] = $listing;

	} else if (Utils::route('post', '/apps$', &$parameters, &$object)) {

		$id = $object["id"];
		Utils::validateID($id);
		$config->store($object, $auth->getRealUserID());

		$ac = new Config($id);
		$result['data'] = $ac->getConfig();

	} else if (Utils::route('get', '/app/([^/]+)$', &$parameters, &$object)) {

		$subid = $parameters[1];
		Utils::validateID($subid);
		$ac = new Config($subid);

		$result['data'] = $ac->getConfig();
		$result['data']['davcredentials'] = $ac->getDavCredentials($auth->getRealUserID());
		$result['data']['appdata-stats'] = $ac->getStats();
		$result['data']['files-stats'] = $ac->getFilestats();
		$result['data']['user-stats'] = $ac->getUserStats();

	} else if (Utils::route('post', '/app/([^/]+)/status$', &$parameters, &$object)) {

		$subid = $parameters[1];
		Utils::validateID($subid);

		$ac = new Config($subid);
		$ac->updateStatus($object, $auth->getRealUserID());

		$c = $ac->getConfig();

		$result['data'] = $c['status'];

	} else if (Utils::route('get', '/app/([^/]+)/status$', &$parameters, &$object)) {

		$subid = $parameters[1];
		Utils::validateID($subid);
		$ac = new Config($subid);
		$c = $ac->getConfig();

		$result['data'] = $c['status'];

	} else if (Utils::route('get', '/app/([^/]+)/davcredentials$', &$parameters, &$object)) {

		$subid = $parameters[1];
		Utils::validateID($subid);
		$ac = new Config($subid);

		$result['data'] = $ac->getDavCredentials($auth->getRealUserID());

	} else if (Utils::route('post', '/app/([^/]+)/authorizationhandler/([^/]+)$', &$parameters, &$object)) {

		$subid = $parameters[1];
		Utils::validateID($subid);
		$ac = new Config($subid);
		
		$authzhandler = $parameters[2];
		Utils::validateID($authzhandler);

		$handlers = $ac->updateAuthzHandler($authzhandler, $object, $auth->getRealUserID());
		$result['data'] = $handlers;

	} else if (Utils::route('delete', '/app/([^/]+)/authorizationhandler/([^/]+)$', &$parameters, &$object)) {

		$subid = $parameters[1];
		Utils::validateID($subid);
		$ac = new Config($subid);
		
		$authzhandler = $parameters[2];
		Utils::validateID($authzhandler);

		$res = $ac->deleteAuthzHandler($authzhandler, $auth->getRealUserID());
		$result['data'] = $res;

	} else if (Utils::route('get', '/check/([^/]+)$', &$parameters, &$object)) {
		$subid = $parameters[1];
		Utils::validateID($subid);
		$result['data'] = !$config->exists($subid);

	} else {
		throw new Exception('Invalid URL or HTTP Method');
	}




	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($result);


} catch(Exception $error) {

	$result = array();
	$result['status'] = 'error';
	$result['message'] = $error->getMessage();
	echo json_encode($result);

}


