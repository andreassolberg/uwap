<?php


class AuthenticatedToken {
	
	protected 
		$token, 
		$client,
		$user = null;

	public function __construct($token) {
		$this->token = $token;

		if (!empty($this->token->userdata)) {
			$this->user = User::getByID($this->token->userdata['userid']);
		}

		if (!empty($this->token->client_id)) {
			$this->client = Client::getByID($this->token->client_id);
		}
	}

	public function isUser() {
		return (!empty($this->token->userdata));
	}


	public function getClientID() {
		return $this->token->client_id;
	}

	public function getUser() {
		return $this->user;
	}

	public function getClient() {
		return $this->client;
	}


	public function getClientGroups() {
		$clientdata = $this->token->clientdata;
		return $clientdata['groups'];
	}



	// public function getUserdata() {

	// 	$userdata = $this->token->userdata;
	// 	return $userdata;

	// }

	// public function getUserID() {
	// 	$userdata = $this->token->userdata;
	// 	return $userdata['userid'];
	// }
	
	// public function getSubscriptions() {
	// 	if (!$this->token->userdata) return array();

	// 	$groupmanager = new GroupManager($this->token->userdata['userid']);
	// 	$s = $groupmanager->getSubscriptions();
	// 	return $s;
	// }


	// public function getGroups() {

	// 	$userdata = $this->token->userdata;
	// 	$groups = $userdata['groups'];
	// 	$userid = $userdata['userid'];

	// 	$groupmanager = new GroupManager($userid);
	// 	$groups = $groupmanager->getGroupNamesIndexed($groups);

	// 	// $groupmanager = new GroupManager($userid);
	// 	// $adhocgroups = $groupmanager->getMyGroups();
	// 	// if (!empty($adhocgroups)) {
	// 	// 	foreach($adhocgroups AS $adhocgroup) {
	// 	// 		$groups[$adhocgroup['id']] = $adhocgroup['title'];
	// 	// 	}
	// 	// }

	// 	// if (in_array($userid, GlobalConfig::getValue('admins', array()))) {
	// 	// 	$groups['uwapadmin'] = 'UWAP System Administrators';
	// 	// }

	// 	return $groups;
	// }

	// public function getUserdataWithGroups() {

	// 	$userdata = $this->token->userdata;
	// 	$userid = $userdata['userid'];

	// 	$groupmanager = new GroupManager($userid);
	// 	$adhocgroups = $groupmanager->getMyGroups();

	// 	// echo '<pre>getUserdataWithGroups() ';
	// 	// print_r($userdata); 
	// 	// // print_r($adhocgroups); 
	// 	// exit; 

	// 	$userdata['groups'] = $groupmanager->getGroupNamesIndexed($userdata['groups']);



	// 	// $userdata['groups'] = $adhocgroups;

	// 	// if (!empty($adhocgroups)) {
	// 	// 	foreach($adhocgroups AS $adhocgroup) {
	// 	// 		$userdata['groups'][$adhocgroup['id']] = $adhocgroup['title'];
	// 	// 	}
	// 	// }

	// 	// if (in_array($userid, GlobalConfig::getValue('admins', array()))) {
	// 	// 	$userdata['groups']['uwapadmin'] = 'UWAP System Administrators';
	// 	// }

	// 	return $userdata;
	// }

	





}