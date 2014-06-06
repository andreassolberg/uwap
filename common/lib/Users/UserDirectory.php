<?php


/**
 * Will be able to lookup users.
 * Also deals with merging users based upon UserIDs
 */
class UserDirectory {

	protected $store;

	function __construct() {
		$this->store = new UWAPStore();
	}

	function merge(User $item1, User $item2) {

		if ($item1->get('shaddow', false) > $item2->get('shaddow', false)) {
			error_log("Merging item2 into item1 because item1 is shaddowed (Created without authentication)");
			return $item1->mergeInto($item2);
		}
		if ($item2->get('shaddow', false) > $item1->get('shaddow', false)) {
			error_log("Merging item1 into item2 because item2 is shaddowed (Created without authentication)");
			return $item2->mergeInto($item1);
		}

		if ($item1->get('created', null) < $item2->get('created', null)) {
			error_log("Merging item2 into item1 because item1 oldest");
			return $item2->mergeInto($item1);
		}
		if ($item2->get('created', null) < $item1->get('created', null)) {
			error_log("Merging item1 into item2 because item2 oldest");
			return $item1->mergeInto($item2);
		}

		error_log("Merging 1 into 2 because objects are made at same time...");
		return $item1->mergeInto($item2);

	}


	function lookup($uid) {

		$query = $uid->getQuery();
		$list = $this->store->queryList('users', $query);

		// echo '<pre>Query:'; print_r($query);
		// echo '<pre>Results:'; 
		// print_r($list);

		$users = array();

		foreach($list AS $item) {
			$users[] = new User($item);
		}

		return $users;

	}

}