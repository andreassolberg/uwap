<?php

class AdHocGroup extends Group {
	
	public static $prefix = 'uwap:grp-ah';
	public static $grouptype = 'uwap:group:type:ad-hoc';

	protected static $collection = 'groups';
	protected static $primaryKey = 'id';
	protected static $validProps = array(
		'id', 'type', 'displayName', 'description', 'members', 'admins', 'uwap-userid', 'source', 'public');


	public function __construct($properties, $stored = false) {

		if (isset($properties['listable']) && !is_bool($properties['listable'])) {
			throw new Exception('Creating new AdHocGroup object. then listable property needs to be of type boolean');
		}

		parent::__construct($properties, $stored);

		if (empty($this->properties['admins'])) {
			$this->properties['admins'] = array();
		}
		if (empty($this->properties['members'])) {
			$this->properties['members'] = array();
		}

		$this->properties['type'] = self::$grouptype;

	}

	public static function generate($properties, $user) {

		$properties['id'] = self::$prefix  . ':' . Utils::genID();
		$properties['uwap-userid'] = $user->get('userid');
		$properties['members'] = array($user->get('userid'));
		$properties['admins'] = array();

		$group = new AdHocGroup($properties);
		return $group;
	}

	public function update($properties, $allowed) {

		foreach($properties AS $key => $value) {
			if (in_array($key, $allowed)) {
				if ($key === 'public' && !is_bool($value)) {
					throw new Exception('listable property needs to be boolean');
				}
				$this->set($key, $value);
			}
		}

	}

	public function getUserRole($user, $isSubscribed = false) {

		// echo "Checking is user "; print_r($user->getJSON());
		// echo "is member of "; print_r($this->getJSON());

		if ($this->get('uwap-userid', null) === $user->get('userid')) return 'owner';
		if (in_array($user->get('userid'), $this->properties['admins'])) return 'admin';
		if (in_array($user->get('userid'), $this->properties['members'])) return 'member';

		if ($isSubscribed) return 'subscriber';
		return 'no';
	}

	public function requireLevel($user, $requiredLevel) {
		$levels = array(
			'owner' => 4,
			'admin' => 3,
			'member' => 2,
			'subscriber' => 1,
			'no' => 0,
		);
		if (!isset($levels[$requiredLevel])) 
			throw new Exception('Invalid membership level provided as a parameter to authorization check for group membershpis');

		$userLevel = $this->getUserRole($user);

		// echo "User level [" . $levels[$userLevel] . "] required level [" . $levels[$requiredLevel] . "]";

		return ($levels[$userLevel] >= $levels[$requiredLevel]);

	}


	public function updateMember($userid, $member) {

		if (!in_array($member, array('member', 'admin'))) {
			throw new Exception('Invlaid member type for managing membership of this group');
		}

		$isMember = true;
		$isAdmin = ($member === 'admin');

		$members = $this->get('members', array());

		if ($isMember) {
			$members = self::array_add($members, $userid);	
		} else {
			$members = self::array_remove($members, $userid);	
		}
		
		$admins = $this->get('admins', array());

		if ($isAdmin) {
			$admins = self::array_add($admins, $userid);	
		} else {
			$admins = self::array_remove($admins, $userid);	
		}

		$this->set('members', $members);
		$this->set('admins', $admins);

		// echo "Dealing with user ". $userid;
		// print_r($members);
		// print_r($admins); exit;
		
		$this->store();

	}

	public function getAsVoot($currentUser) {

		$map = array(
			'displayName' => 'displayName',
			'description' => 'description',
			'id' => 'id',
			'groupType' => 'groupType',
			'public' => 'public'
		);

		$object = array();
		foreach($map AS $to => $from) {
			if (isset($this->properties[$from])) {
				$object[$to] = $this->properties[$from];
			}
		}

		$role = array();

		// echo 'USER IS <pre>' . $currentUser->get('userid');
		// print_r($this->properties);

		if (in_array($currentUser->get('userid'), $this->properties['members']) 
			|| ($currentUser->get('userid') === $this->properties['uwap-userid'])
			) {

			$role['basic'] = 'member';
			$role['displayName'] = 'Member';
			$role['may'] = array(
				'listMembers' => true,
				'manageMembers' => false,
				'manageGroup' => false
			);

			if ($currentUser->get('userid') === $this->properties['uwap-userid']) {
				$role['basic'] = 'owner';
				$role['displayName'] = 'Owner';
				$role['may']['manageMembers'] = true;
			} else if(in_array($currentUser->get('userid'), $this->properties['admins'])) {
				$role['basic'] = 'admin';
				$role['displayName'] = 'Admin';
				$role['may']['manageMembers'] = true;
				$role['may']['manageGroup'] = true;
			}

			$object['vootRole'] = $role;

		}


		$group = new SCIMResourceGroup($object);
		return $group;

	}


	public function removeMember($userid) {

		
		$isMember = false;
		$isAdmin = false;


		$members = $this->get('members', array());

		if ($isMember) {
			$members = self::array_add($members, $userid);	
		} else {
			$members = self::array_remove($members, $userid);	
		}
		
		$admins = $this->get('admins', array());

		if ($isAdmin) {
			$admins = self::array_add($admins, $userid);	
		} else {
			$admins = self::array_remove($admins, $userid);	
		}

		$this->set('members', $members);
		$this->set('admins', $admins);

		
		$this->store();

	}

}