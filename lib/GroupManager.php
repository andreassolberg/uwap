<?php

class GroupManager {
	

	protected $store, $userid;


	public function __construct($userid) {

		$this->userid = $userid;
		$this->store = new UWAPStore();

	}

	public function getMyOwnGroups() {
		return $this->store->queryListUser('groups', $this->userid, null, array() );
	}

	public function getMyGroups() {
		$query = array('members' => array('$all' => array($this->userid)) );
		// echo '<pre>'; print_r($query); exit;
		return $this->store->queryList('groups', $query );
	}

	public function addGroup($group) {
		if (empty($group['title'])) throw new Exception('Missing group attribute [title]');
		$allowedFields = array('id', 'title', 'description');
		foreach($group AS $key => $val) {
			if (!in_array($key, $allowedFields)) throw new Exception('Invalid group attribute provided');
		}
		if (empty($group['id'])) {
			$group['id'] = Utils::genID();
		}

		$group['members'] = array($this->userid);
		$group['admins'] = array();

		Utils::validateGroupID($group['id']);
		if ($this->exists($group['id'])) throw new Exception('Group ID is already taken.');

		$this->store->store('groups', $this->userid, $group);
		return true;
	}

	public function deleteGroup($groupid) {
		return $this->store->remove('groups', $this->userid, array('id' => $groupid));
	}

	public function updateGroup($group) {

	}

	public function exists($groupid) {
		$res = $this->getGroup($groupid);
		return !empty($res);
	}

	public function getGroup($groupid) {
		// echo 'query group id ' . $groupid; exit;
		return $this->store->queryOne('groups', array('id' => $groupid));
	}

	public static function addToArray($key, &$arr) {
		if (!in_array($key, $arr)) {
			$arr[] = $key;
		}
	}
	public static function removeFromArray($key, &$arr) {
		$narr = array();
		foreach($arr AS $a) {
			if ($key !== $a) $narr[] = $a;
		}
		$arr = $narr;
	}

	public function addMember($groupid, $member, $admin = false) {
		$group = $this->getGroup($groupid);
		
		if (empty($group)) {
			throw new Exception('Could not lookup group details for this group');
		}
		if (!is_array($group['members'])) $group['members'] = array();
		if (!is_array($group['admins'])) $group['admins'] = array();

		self::addToArray($member, $group['members']);
		if ($admin) {
			self::removeFromArray($member, $group['admins']);
		} else {
			self::removeFromArray($member, $group['admins']);
		}

		$this->store->store('groups', null, $group);

	}


	protected function ensureUser($userid) {

	}

}