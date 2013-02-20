<?php


class AuthenticatedToken {
	
	var $token, $store;

	public function __construct($token) {
		$this->token = $token;
		$this->store = new UWAPStore();
	}

	public function isUser() {
		return (!empty($this->token->userdata));
	}

	public function getUserdata() {

		$userdata = $this->token->userdata;
		return $userdata;

	}

	public function getUserID() {
		$userdata = $this->token->userdata;
		return $userdata['userid'];
	}
	
	public function getSubscriptions() {
		if (!$this->token->userdata) return array();

		$groupmanager = new GroupManager($this->token->userdata['userid']);
		$s = $groupmanager->getSubscriptions();
		return $s;
	}


	public function getGroups() {

		$userdata = $this->token->userdata;
		$groups = $userdata['groups'];
		$userid = $userdata['userid'];

		$groupmanager = new GroupManager($userid);
		$groups = $groupmanager->getGroupNamesIndexed($groups);

		// $groupmanager = new GroupManager($userid);
		// $adhocgroups = $groupmanager->getMyGroups();
		// if (!empty($adhocgroups)) {
		// 	foreach($adhocgroups AS $adhocgroup) {
		// 		$groups[$adhocgroup['id']] = $adhocgroup['title'];
		// 	}
		// }

		// if (in_array($userid, GlobalConfig::getValue('admins', array()))) {
		// 	$groups['uwapadmin'] = 'UWAP System Administrators';
		// }

		return $groups;
	}

	public function getUserdataWithGroups() {

		$userdata = $this->token->userdata;
		$userid = $userdata['userid'];

		$groupmanager = new GroupManager($userid);
		// $adhocgroups = $groupmanager->getMyGroups();

		$userdata['groups'] = $groupmanager->getGroupNamesIndexed($userdata['groups']);

		// if (!empty($adhocgroups)) {
		// 	foreach($adhocgroups AS $adhocgroup) {
		// 		$userdata['groups'][$adhocgroup['id']] = $adhocgroup['title'];
		// 	}
		// }

		// if (in_array($userid, GlobalConfig::getValue('admins', array()))) {
		// 	$userdata['groups']['uwapadmin'] = 'UWAP System Administrators';
		// }

		return $userdata;
	}

	

	public function getClientID() {
		return $this->token->client_id;
	}

	public function getClientGroups() {
		$clientdata = $this->token->clientdata;
		return $clientdata['groups'];
	}


}