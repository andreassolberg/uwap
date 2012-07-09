<?php

/*
 * This API is reached through
 * 
 * 		appid.uwap.org/_/api/groups.php
 *
 * This is used only indirectly through the UWAP core js API.
 */

require_once('../../lib/autoload.php');





try {


/*
  * TODO: Add restriction that this AIP is only accessible from the apps that are authorized to perform group management.
  * Including groups.uwap.org.
 */


	$config = new Config();
	$subhost = $config->getID();

	$auth = new Auth();
	$auth->req();

	$result = array();
	$result['status'] = 'ok';
	
	$store = new UWAPStore();

	$userid = $auth->getRealUserID();
	$groupmanager = new GroupManager($userid);


	$parameters = null;
	$object = null;

	if (Utils::route('get', '/groups$', &$parameters)) {


		if (isset($_GET['filter']) && $_GET['filter'] === 'admin') {
			$result['data'] = $groupmanager->getMyOwnGroups();
		} else {
			$result['data'] = $groupmanager->getMyGroups();
		}


	} else if (Utils::route('post', '/groups$', &$parameters, &$object)) {

		$result['data'] = $groupmanager->addGroup($object);


	} else if (Utils::route('get', '/group/([^/]+)$', &$parameters)) {

		$groupid = $parameters[1];
		Utils::validateGroupID($groupid);
		$result['data'] = $groupmanager->getGroup($groupid);

	} else if (Utils::route('delete', '/group/([^/]+)$', &$parameters)) {

		$groupid = $parameters[1];
		Utils::validateGroupID($groupid);
		$result['data'] = $groupmanager->removeGroup($groupid);


	} else if (Utils::route('post', '/group/([^/]+)/members$', &$parameters, &$object)) {

		// Add member to group
		
		$groupid = $parameters[1];
		Utils::validateGroupID($groupid);

		$admin = false;
		if (isset($object['admin'])) {
			$admin = $object['admin'];
			unset($object['admin']);
		}

		// addMember($groupid, $member, $admin = false) {
		$result['data'] = $groupmanager->addMember($groupid, $object, $admin);

	} else if (Utils::route('delete', '/group/([^/]+)/member/([^/]+)$', &$parameters, &$object)) {

		// Add member to group
		
		$groupid = $parameters[1];
		Utils::validateGroupID($groupid);

		$userid = $parameters[2];

		// addMember($groupid, $member, $admin = false) {
		$result['data'] = $groupmanager->removeMember($groupid, $userid);

	} else {
		throw new Exception('Invalid URL or HTTP Method');
	}



	// $groupmanager->addGroup(array('title' => 'A cool test group'));
	// $groupmanager->addMember('366e8dcc-a612-4b9e-a4f7-14004990122f', $userid, false);


	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($result);

} catch(Exception $error) {

	$result = array();
	$result['status'] = 'error';
	$result['message'] = $error->getMessage();
	echo json_encode($result);

}


