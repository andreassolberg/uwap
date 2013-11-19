<?php

class Group extends StoredModel {
	



	protected static $validProps = array(
		'id', 'type', 'title', 'description', 'members', 'admins', 'listable', 'uwap-userid', 'source');


	public function __construct($properties) {

		parent::__construct($properties);

	}

	public function getMembers() {

		$members = $this->get('members', array());
		$admins = $this->get('admins', array());

		$set = new RoleSet();

		foreach($members AS $member) {
			$user = User::getByID($member);
			$role = 'member';
			if ($user->get('userid') === $this->get('uwap-userid')) {
				$role = 'owner';
			} else if(in_array($user->get('userid'), $admins)) {
				$role = 'admin';
			}

			$set->add(new Role($user, $this, array('role' => $role)));
		}
		// foreach($admins AS $admin) {
		// 	$user = User::getByID($admin);
		// 	$set->add(new Role($user, $this, array('role' => 'admin')));
		// }
		return $set;
	}



	public function getJSON($opts = array()) {

		// echo 'group::getjson <pre>'; print_r($opts);
		// throw new Exception();


		$props = self::$validProps;
		if (isset($opts['type']) && $opts['type'] === 'basic') {
			$props = array('id', 'title', 'type', 'description', 'uwap-userid');
		}

		$ret = array();
		foreach($props AS $p) {
			if (isset($this->properties[$p])) {
				$ret[$p] = $this->properties[$p];
			}
		}


		return $ret;
	}





}