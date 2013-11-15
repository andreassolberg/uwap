<?php


/**
 * One entry-point to retrieve all data related to groups from the perspective of one user.
 * Will contact adhoc group connetor and external group connector to get data.
 */
class GroupConnector {
	
	protected $user;
	protected $adhoc, $ext;

	protected $cachedGroups = null;

	public function __construct($user) {
		$this->user = $user;

		$this->adhoc = new AdHocGroups($user);
		$this->ext = new ExtGroups($user);
		// $this->ss = new Subscriptions($user);

	}

	// public function getGroup($id) {

	// 	if (preg_match('/^uwap:grp-ah:/', $id)) {
			
	// 		return $this->adhoc->getByID($id);
	// 	}
	// 	$data = $this->ext->getByID($id);

	// 	return $data;
	// }


	public function peopleListRealms() {
		$realms = array();
		$r = array(
			'name' => '',
			'realm' => '',
			'default' => false,
		);
		$realms[] = $r;
		return $realms;
	}

	public function peopleQuery($realm, $query) {

		return $this->ext->peopleQuery($realm, $query);
	}


	public function addMember($groupid, $userprops) {



		$group = $this->getByID($groupid);
		
		if (empty($group)) {
			throw new Exception('Could not lookup group details for this group');
		}

		if (!$group->requireLevel($this->user, 'admin')) 
			throw new Exception('User unauthorized to manage members of this group');

		if (empty($userprops['userid'])) {
			throw new Exception('UserID missing from ');
		}

		$targetUser = User::getByID($userprops['userid']);
		if ($targetUser === null) {
			$targetUser = User::generateShaddow($userprops, $this->user);
			$targetUser->store();


		}

		

		$group->updateMember($userprops['userid'], 'member');

		// echo "about to add member"; print_r($group->getJSON()); print_r($userprops); print_r($targetUser->getJSON()); exit;

		return $group->store();

	}


	public function updateMember($groupid, $userid, $member) {


		$group = $this->getByID($groupid);
		
		if (empty($group)) {
			throw new Exception('Could not lookup group details for this group');
		}

		if (!$group->requireLevel($this->user, 'admin')) 
			throw new Exception('User unauthorized to manage members of this group');

		$group->updateMember($userid, $member);
		return $group->store();

	}





	public function removeMember($groupid, $userid) {
		$group = $this->getByID($groupid, 'admin');
		
		if (empty($group)) {
			throw new Exception('Could not lookup group details for this group');
		}

		if (!$group->requireLevel($this->user, 'admin')) 
			throw new Exception('User unauthorized to manage members of this group');

		$group->removeMember($userid);
		return $group->store();
	}


	public function subscribe($groupid) {
		$group = $this->getByID($groupid);

		if (empty($group)) {
			throw new Exception('Could not lookup group details for this group');
		}
		// print_r($group);
		if (!$group->get('listable', false)) throw new Exception('This group does not allow subscriptions');
		$this->user->subscribe($group);
		return $this->user->store();

	}

	public function unsubscribe($groupid) {
		$group = $this->getByID($groupid);

		if (empty($group)) {
			throw new Exception('Could not lookup group details for this group');
		}

		// print_r($group);
		if (!$group->get('listable', false)) throw new Exception('This group does not allow subscriptions');
		$this->user->unsubscribe($group);
		return $this->user->store();

	}

	public function addGroup(array $properties = array()) {

		$group = AdHocGroup::generate($properties, $this->user);
		// return $group->store();

		return $this->adhoc->addGroup($group);

	}

	public function remove($groupid) {
		$group = $this->getByID($groupid);
		
		if (empty($group)) {
			throw new Exception('Could not lookup group details for this group');
		}

		if (!$group->requireLevel($this->user, 'owner')) 
			throw new Exception('User unauthorized to delete this group');

		return $group->remove();

	}

	public function update($groupid, $properties) {
		$group = $this->getByID($groupid);
		
		if (empty($group)) {
			throw new Exception('Could not lookup group details for this group');
		}

		// print_r($group);

		if (!$group->requireLevel($this->user, 'admin')) 
			throw new Exception('User unauthorized to update this group');

		$group->update($properties, array('title', 'description', 'listable'));

		return $group->store();

		// if (isset($obj['title'])) {
		// 	$group['title'] = $obj['title'];
		// }
		// if (isset($obj['description'])) {
		// 	$group['description'] = $obj['description'];
		// }
		// if (isset($obj['listable'])) {
		// 	if (!is_bool($obj['listable'])) throw new Exception('listable property must be boolean');
		// 	$group['listable'] = $obj['listable'];
		// }
		// return $this->store->store('groups', null, $group);

	}



	public function getGroups() {

		if ($this->cachedGroups !== null)  {
			return $this->cachedGroups;
		}

		$groups = array();

		$m = $this->adhoc->getGroups();
		foreach($m AS $me) {
			// print_r($me);
			$groups[$me->group->get('id')] = $me;
		}

		$m = $this->ext->getGroups();
		foreach($m AS $me) {
			$groups[$me->group->get('id')] = $me;
		}

		$this->cachedGroups = $groups;

		return $groups;
	}	


	public function getPublicGroups() {

		$groups = array();

		$m = $this->adhoc->getPublicGroups();
		foreach($m AS $me) {
			$groups[] = $me;
		}

		$m = $this->ext->getPublicGroups();
		foreach($m AS $me) {
			$groups[] = $me;
		}

		return $groups;
	}

	public function getPublicGroupsJSON() {
		$data = $this->getPublicGroups();
		$res = array();

		foreach($data AS $entry) {
			$res[] = $entry->getJSON();
		}
		return $res;
	}


	public function getGroupsJSON() {
		$data = $this->getGroups();
		$res = array();

		foreach($data AS $entry) {
			$res[] = $entry->getJSON();
		}
		return $res;

	}

	public function getGroupsByID($ids) {
		$ret = array();
		foreach($ids AS $id) {
			$ret[$id] = $this->getByID($id);
		}
		// echo "about to get by id"; print_r($ret);

		return $ret;
	}


	public function getByID($id) {

		if (preg_match('/^uwap:grp-ah:/', $id)) {

			return $this->adhoc->getByID($id);
			// return AdHocGroup::getByID($id);

		}
		$data = $this->ext->getByID($id);
		return $data;

	}


}