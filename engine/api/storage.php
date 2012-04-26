<?php


/*
 * This API is reached through
 * 
 * 		appid.uwap.org/_/api/storage.php
 *
 * This is used only indirectly through the UWAP core js API.
 * This API checks if the user is authenticated, and returns userdata if so.
 * If the user is not authenticated, nothing is returned ({status: error}).
 */

require_once('../../lib/autoload.php');


try {

	$config = new Config();
	$subhost = $config->getID();

	$auth = new Auth();
	$auth->req();

	$result = array();
	$result['status'] = 'ok';
	
	$store = new UWAPStore();

	if (empty($_REQUEST['op'])) throw new Exception("Missing required parameter [op] operation");

	switch($_REQUEST['op']) {

		case 'save':
			if (empty($_REQUEST['object'])) throw new Exception("Missing required parameter [object] object to save");
			$parsed = json_decode($_REQUEST['object'], true);
			if (isset($parsed["_id"])) {
				$parsed["_id"] = new MongoId($parsed["_id"]['$id']);
			}
			// echo "Is about to store object:"; print_r($parsed); exit;
			$store->store("appdata-" . $subhost, $auth->getRealUserID(), $parsed);
			break;

			// TODO: Clean output before returning. In example remove uwap- namespace attributes...
		case 'queryOne':
			if (empty($_REQUEST['query'])) throw new Exception("Missing required parameter [query] query");
			$query = json_decode($_REQUEST['query'], true);
			$result['data'] = $store->queryOneUser("appdata-" . $subhost, $auth->getRealUserID(), $query);
			if (is_null($result['data'])) {
				throw new Exception("Query did not return any results");
			}
			break;

		case 'queryList':
			if (empty($_REQUEST['query'])) throw new Exception("Missing required parameter [query] query");
			$query = json_decode($_REQUEST['query'], true);
			$result['data'] = $store->queryListUser("appdata-" . $subhost, $auth->getRealUserID(), $query);
			if (is_null($result['data'])) {
				throw new Exception("Query did not return any results");
			}
			break;

	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($result);
	
} catch(Exception $error) {

	$result = array();
	$result['status'] = 'error';
	$result['message'] = $error->getMessage();
	echo json_encode($result);

}







