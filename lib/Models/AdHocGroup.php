<?php

class AdHocGroup extends Group {
	

	protected static $collection = 'groups';
	protected static $primaryKey = 'id';
	protected static $validProps = array(
		'id', 'type', 'title', 'description', 'members', 'admins', 'uwap-userid', 'source');


	public function __construct($properties) {

		parent::__construct($properties);

	}

	public function getUserRole($user) {

		if ($this->properties['uwap-userid'] === $user->get('userid')) return 'owner';
		if (in_array($user->get('userid'), $this->properties['admins'])) return 'admin';
		if (in_array($user->get('userid'), $this->properties['members'])) return 'member';
		return null;
	}

}