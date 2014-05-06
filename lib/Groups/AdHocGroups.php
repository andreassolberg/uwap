<?php

class AdHocGroups {
	
	protected $user, $store;

	public function __construct($user) {
		$this->user = $user;
		$this->store = new UWAPStore();
	}


	public function addGroup(AdHocGroup $group) {

		if ($this->exists($group->get('id'))) 
			throw new Exception('Group ID [' . $group['id'] . '] is already taken.');

		// echo "About to store new object<pre>"; print_r($group->getJSON());

		$group->store();

		// $this->store->store('groups', null, $group->getJSON() );
		return $this->getByID($group->get('id'));
	}

	public function exists($id) {
		$res = $this->getByID($id);
		return !empty($res);
	}


	public function getGroups() {

		$memberships = array();

		$query = array(
			'$or' => array(
				array(
					'members' => array(
						'$all' => array($this->user->get('userid'))
					),
				),
				array(
					'uwap-userid' => $this->user->get('userid'),
				)
			),
		);
		$res = $this->store->queryList('groups', $query );

		foreach($res AS $g) {
	
			$group = new AdHocGroup($g);
			$role = new Role($this->user, $group, array('role' => $group->getUserRole($this->user)));

			$memberships[] = $role;			
		}

		return $memberships;

	}



	public function getPublicGroups() {

		$query = array(
			'public' => true
		);

		$res = $this->store->queryList('groups', $query );
		$result = array();

		$mysub = $this->user->getSubscriptions();

		foreach($res AS $entry) {

			$isSubscribed = in_array($entry['id'], $mysub);
			$group = new AdHocGroup($entry);

			$roledef = $group->getUserRole($this->user, $isSubscribed);
			$role = new Role($this->user, $group, array('role' => $roledef));

			$result[] = $role;

		}

		return $result;
	}



	public function getByID($id) {


		$query = array('id' => $id);
		$res = $this->store->queryOne('groups', $query );

		// echo "Looking up " . $id; print_r($res); exit;

		if (empty($res)) return null;
		$group = new AdHocGroup($res);

		return $group;

	}



}