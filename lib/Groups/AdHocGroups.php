<?php

class AdHocGroups {
	
	protected $user, $store;

	public function __construct($user) {
		$this->user = $user;
		$this->store = new UWAPStore();
	}

	public function getGroups() {

		$memberships = array();

		$query = array('members' => array('$all' => array($this->user->get('userid'))) );
		$res = $this->store->queryList('groups', $query );

		foreach($res AS $g) {
			// echo "new ad hoc group with "; print_r($g);
			
			$group = new AdHocGroup($g);
			$role = new Role($this->user, $group, array('role' => $group->getUserRole($this->user)));

			$memberships[] = $role;
			
		}

		return $memberships;

	}


}