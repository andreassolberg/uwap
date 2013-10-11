<?php


/**
 * One entry-point to retrieve all data related to groups from the perspective of one user.
 * Will contact adhoc group connetor and external group connector to get data.
 */
class GroupConnector {
	
	protected $user;

	protected $adhoc, $ext;

	public function __construct($user) {
		$this->user = $user;

		$this->adhoc = new AdHocGroups($user);
		$this->ext = new ExtGroups($user);

	}

	public function getGroups() {


		$groups = array();

		$m = $this->adhoc->getGroups();
		foreach($m AS $me) {
			$groups[] = $me;
		}

		$m = $this->ext->getGroups();
		foreach($m AS $me) {
			$groups[] = $me;
		}

		return $groups;

	}	


}