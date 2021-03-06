<?php

class Role extends Model {
	
	public $user, $group, $role;
	protected static $validProps = array('role');

	
	public function __construct(User $user, Group $group, $properties) {


		if (!$user instanceof User) throw new Exception('Creating new role without a proper User object');
		if (!$group instanceof Group) throw new Exception('Creating new role without a proper Group object');

		$this->user = $user;
		$this->group = $group;

		parent::__construct($properties);
	}

	public function getJSON($opts = array()) {

		if (isset($opts['type']) && $opts['type'] === 'key') {
			return $this->group->get('id');
		} else if (isset($opts['type']) && $opts['type'] === 'user') {
			$data = $this->user->getJSON(array('type' => 'basic'));
			return array_merge($data, $this->properties);
		}

		$data = $this->group->getJSON($opts);
		return array_merge($data, $this->properties);
	}

}