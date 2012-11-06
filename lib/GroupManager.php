<?php

class GroupManager {
	

	protected $store, $userid;


	public function __construct($userid) {

		$this->userid = $userid;
		$this->store = new UWAPStore();

	}

	public function getMyOwnGroups() {
		// error_log("Filtering groups " . $this->userid);
		$groups = $this->store->queryListUser('groups', $this->userid, null, array() );
		return $groups;
	}

	public function getMyGroups() {
		$query = array('members' => array('$all' => array($this->userid)) );
		return $this->store->queryList('groups', $query );
	}


	public function getGroupNamesIndexed($moregroups) {
		$groups = $this->getGroups($moregroups);
		$res = array();
		foreach($groups AS $k => $v) {
			if (isset($v['id'])) {
				$res[$v['id']] = $v['title'];
			}
		}
		return $res;
	}

	public function getGroupsIndexed($moregroups) {
		$groups = $this->getGroups($moregroups);
		$res = array();
		foreach($groups AS $k => $v) {
			if (isset($v['id'])) {
				$res[$v['id']] = $v;
			}
		}
		return $res;
	}


	/**
	 * New API for getting all groups of an user.
	 * Cached token groups are provided in as a parameter.
	 * 
	 * @param  [type] $moregroups [description]
	 * @return [type]             [description]
	 */
	public function getGroups($moregroups) {

		$query = array(
			'$or' => array(
				array(
					'members' => array('$in' => array($this->userid)),
				),
				array(
					'admins' => array('$in' => array($this->userid)),
				),
				array(
					'uwap-userid' => $this->userid,
				)
			)
		);

		$res = $this->store->queryList('groups', $query );
		$result = array();

		foreach($res AS $entry) {
			$ne = array();
			$ne['title'] = $entry['title'];
			$ne['id'] = $entry['id'];
			$ne['description'] = $entry['description'];
			if (isset($entry['listable'])) $ne['listable'] = $entry['listable'];

			$ne['owner'] = (bool) ($entry['uwap-userid'] === $this->userid);
			$ne['admin'] = (bool) (in_array($this->userid, $entry['admins']));
			$ne['member'] = (bool) (in_array($this->userid, $entry['members']));
			$ne['listmembers'] = true;
			$result[] = $ne;

			if (isset($moregroups[$entry['id']])) {
				unset($moregroups[$entry['id']]);
			}
		}



		$agora = new GroupFetcherAgora($this->userid);
		$agroups = $agora->getGroups();

		$result = array_merge($result, $agroups);


		foreach($moregroups AS $key => $title) {
			$ne = array();

			$ne['id'] = $key;
			$ne['title'] = $title;

			$ne['owner'] = false;
			$ne['admin'] = false;
			$ne['member'] = false;
			$ne['listmembers'] = false;

			$result[] = $ne;
		}






		return $result;
	}


	public function addGroup($group) {
		if (empty($group['title'])) throw new Exception('Missing group attribute [title]');
		$allowedFields = array('id', 'title', 'description', 'listable');
		foreach($group AS $key => $val) {
			if (!in_array($key, $allowedFields)) throw new Exception('Invalid group attribute provided');
		}
		if (empty($group['id'])) {
			$group['id'] = Utils::genID();
		}
		if (isset($group['listable']) && !is_bool($group['listable'])) {
			throw new Exception('Property listable must be boolean.');
		}

		$group['members'] = array($this->userid);
		$group['admins'] = array();


		Utils::validateGroupID($group['id']);
		if ($this->exists($group['id'])) throw new Exception('Group ID [' . $group['id'] . '] is already taken.');

		$this->store->store('groups', $this->userid, $group);
		return $this->getGroup($group['id']);
	}



	public function getUsers($users) {
		$ret = array();
		$query = array('userid' => array('$in' => $users));
		$u = $this->store->queryList('users', $query, array('name', 'userid', 'mail', 'a') );

		if (empty($u)) return $ret;

		foreach($u AS $user) {
			$ret[$user['userid']] = $user;
		}

		return $ret;
	}

	public function removeGroup($groupid) {
		$this->getGroup($groupid, 'admin');
		return $this->store->remove('groups', $this->userid, array('id' => $groupid));
	}

	public function updateGroup($groupid, $obj) {
		$group = $this->getGroup($groupid, 'admin');
		
		if (empty($group)) {
			throw new Exception('Could not lookup group details for this group');
		}

		if (isset($obj['title'])) {
			$group['title'] = $obj['title'];
		}
		if (isset($obj['description'])) {
			$group['description'] = $obj['description'];
		}
		if (isset($obj['listable'])) {
			if (!is_bool($obj['listable'])) throw new Exception('listable property must be boolean');
			$group['listable'] = $obj['listable'];
		}
		return $this->store->store('groups', null, $group);

	}

	public function exists($groupid) {
		$res = $this->getGroup($groupid);
		// echo "Group returned"; print_r($res);
		return !empty($res);
	}


	/**
	 * Get a group by ID.
	 * Also implements access control, by requeseting an access level.
	 * An exception is thrown if 
	 * @param  [type] $groupid [description]
	 * @param  [type] $access  Access level: null, "member", "admin" or "owner".
	 * @return [type]          [description]
	 */
	public function getGroup($groupid, $access = null) {
		$group = $this->store->queryOne('groups', array('id' => $groupid), array('id', 'uwap-userid', 'title', 'description', 'admins', 'members', 'listable'));

		if (empty($group)) return null;
		if (!isset($group['members']) || !is_array($group['members'])) $group['members'] = array();
		if (!isset($group['admins']) || !is_array($group['admins'])) $group['admins'] = array();

		$group['userlist'] = $this->getUsers($group['members']);
		foreach($group['userlist'] AS $k => $u) { 
			$group['userlist'][$k]['admin'] = false;
			$group['userlist'][$k]['member'] = true;
		}
		foreach($group['admins'] AS $admin) {
			if (isset($group['userlist'][$admin])) {
				$group['userlist'][$admin]['admin'] = true;	
			}
		}

		$group['you'] = array(
			'owner' => false,
			'admin' => false,
			'member' => false,
		);
		if ($group['uwap-userid'] === $this->userid) $group['you']['owner'] = true;
		if (in_array($this->userid, $group['members'])) $group['you']['member'] = true;
		if (in_array($this->userid, $group['admins'])) $group['you']['admin'] = true;


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
		
		$admin = false;
		if (isset($member['admin'])) $admin = $member['admin'];

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

	public function updateMember($groupid, $userid, $member) {
		$group = $this->getGroup($groupid, 'admin');
		
		$admin = false;
		if (isset($member['admin'])) $admin = $member['admin'];

		if (!in_array($userid, $group['members'])) {
			throw new Exception('Cannot update a group member that is not member of the group :/');
		}
		if ($admin) {
			self::addToArray($userid, $group['admins']);
		} else {
			self::removeFromArray($userid, $group['admins']);
		}
		// print_r($groupid);
		// echo "MEMBER:"; print_r($member); echo ":";
		// print_r($group); 
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
		// echo '<pre>SEARCH RESULt: ['; print_r($search); echo ']</pre>';
		if ($search) return false;

		$allowedFields = array('userid', 'name', 'mail');
		foreach($user AS $key => $val) {
			if (!in_array($key, $allowedFields)) throw new Exception('Invalid user attribute provided');
		}
		$this->store->store('users', null, $user);
		return true;
	}

}