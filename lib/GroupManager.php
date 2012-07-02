<?php

class GroupManager {
	

	protected $store, $userid;


	public function __construct($userid) {

		$this->userid = $userid;
		$this->store = new UWAPStore();

	}

	public function getMyOwnGroups() {
		$groups = $this->store->queryListUser('groups', $this->userid, null, array() );
	}

	public function getMyGroups() {
		$query = array('members' => array('$all' => array($this->userid)) );
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

	public function getUsers($users) {
		$ret = array();
		$query = array('userid' => array('$in' => $users));
		$u = $this->store->queryList('users', $query);

		if (empty($u)) return null;

		foreach($u AS $user) {
			$ret[$user['userid']] = $user;
		}

		return $ret;
	}

	public function deleteGroup($groupid) {
		$this->getGroup($groupid, 'admin');
		return $this->store->remove('groups', $this->userid, array('id' => $groupid));
	}

	public function updateGroup($group) {

	}

	public function exists($groupid) {
		$res = $this->getGroup($groupid);
		return !empty($res);
	}


	/**
	 * Get a group by ID.
	 * Also implements access control, by requeseting an access level.
	 * An exception is thrown if 
	 * @param  [type] $groupid [description]
	 * @param  [type] $access  Access level: null, member, admin or owner.
	 * @return [type]          [description]
	 */
	public function getGroup($groupid, $access = null) {
		$group = $this->store->queryOne('groups', array('id' => $groupid));

		if (!isset($group['members']) || !is_array($group['members'])) $group['members'] = array();
		if (!isset($group['admins']) || !is_array($group['admins'])) $group['admins'] = array();


		$group['userlist'] = $this->getUsers($group['members']);
		foreach($group['admins'] AS $admin) {
			if (isset($group['userlist'][$admin])) {
				$group['userlist'][$admin]['admin'] = true;	
			}
		}

		// Access control
		if ($access === null) {

		} else if ($access === 'member') {

			if (!in_array($this->userid, $group['members']) && $group["uwap-userid"] !== $this->userid) {
				throw new Exception('User is not authorized to access this group [member]');
			}

		} else if ($access === 'admin') {

			if ( !in_array($this->userid, $group['admins']) && $group["uwap-userid"] !== $this->userid)  {
				throw new Exception('User is not authorized to access this group [admin]');
			}

		} else if ($access === 'owner') {
			if ($group["uwap-userid"] !== $this->userid) throw new Exception('User is not authorized to access this group [owner]');
		}
		return $group;

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
		$group = $this->getGroup($groupid, 'admin');
		
		if (empty($group)) {
			throw new Exception('Could not lookup group details for this group');
		}

		if (empty($member['userid'])) throw new Exception('Missing parameter userid');
		$userid = $member['userid'];

		self::addToArray($userid, $group['members']);
		if ($admin) {
			self::addToArray($userid, $group['admins']);
		} else {
			self::removeFromArray($userid, $group['admins']);
		}

		$this->addUser($member);
		return $this->store->store('groups', null, $group);

	}

	public function removeMember($groupid, $userid) {
		$group = $this->getGroup($groupid, 'admin');
		
		if (empty($group)) {
			throw new Exception('Could not lookup group details for this group');
		}

		self::removeFromArray($userid, $group['members']);
		self::removeFromArray($userid, $group['admins']);

		return $this->store->store('groups', null, $group);
	}

	protected function getUser($userid) {
		$user = $this->store->queryOne('users', array('userid' => $userid));
		return $user;
	}

	protected function addUser($user) {
		if (empty($user['userid'])) throw new Exception('Missing user attribute [userid]');
		// if (empty($user['name'])) throw new Exception('Missing user attribute [userid]');

		$search = $this->getUser($user['userid']);
		// echo '<pre>SEARCH RESULt: ['; print_r($search); echo ']';
		if ($search) return false;

		$allowedFields = array('userid', 'name', 'email');
		foreach($user AS $key => $val) {
			if (!in_array($key, $allowedFields)) throw new Exception('Invalid user attribute provided');
		}
		$this->store->store('users', null, $user);
		return true;
	}

}