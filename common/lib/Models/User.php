<?php

class User extends StoredModel {


	protected static $collection = 'users';
	protected static $primaryKey = 'userid';
	protected static $validProps = array(
		'userid', 'userid-sec', 'mail', 'name', 'a', 'photo', 
		'accounts',
		'groups', 'subscriptions',
		'customdata',
		'created', 'updated',
		'shaddow-generator', 'shaddow');

	protected $groupconnector;

	public function __construct($properties, $stored = false) {

		// Utils::dump('create new user user:', $properties); exit;
		parent::__construct($properties, $stored);

		$this->groupconnector = new GroupConnector($this);
	}

	// public function hasRealm($realm) {
	// 	// echo "checking if has realm " . $realm . " for me " . $this->get('userid') . "\n";
	// 	$pos = strpos($this->get('userid'), '@' . $realm);
	// 	$has = ($pos !== false);
	// 	return $has;
	// }

	public function getGroups() {
		return $this->groupconnector->getGroups();
	}

	public function getGroupIDs() {
		$ids = array();
		$groups = $this->getGroups();
		foreach($groups AS $g) {
			$ids[] = $g->get('id');
		}
		// echo "IDs: "; print_r($ids);
		return $ids;
	}

	public function isMemberOf($groupid) {
		$groups = $this->getGroups();
		// print_r(array_keys($groups)); exit;
		// echo "Checking if " . $this->get('userid') . "  is member of " . $groupid . "\n";
		// echo "   › is member of " . join(',', array_keys($groups) ). "\n";
		return isset($groups[$groupid]);
		// return in_array($groupid, $groups);
	}

	public function isSubscribedTo($groupid) {
		$groups = $this->get('subscriptions');
		return in_array($groupid, $groups);
	}

	public function getSubscriptions() {
  		$subids = $this->get('subscriptions', array());
		$subs = $this->groupconnector->getGroupsByID($subids);
		return $subs;
	}



	public function subscribe(Group $group) {
		$groupid = $group->get('id');
		$subscriptions = $this->get('subscriptions', array());
		$subscriptions = self::array_add($subscriptions, $groupid);		
		$this->set('subscriptions', $subscriptions);
	}

	public function unsubscribe(Group $group) {
		$groupid = $group->get('id');
		$subscriptions = $this->get('subscriptions', array());
		$subscriptions = self::array_remove($subscriptions, $groupid);		
		$this->set('subscriptions', $subscriptions);
	}

	public function getJSON($opts = array()) {

		$props = self::$validProps;
		if (isset($opts['type']) && $opts['type'] === 'basic') {
			$props = array('userid', 'mail', 'name', 'a');
		}

		if (isset($opts['type']) && $opts['type'] === 'subscriptions') {
			$subgroups = $this->getSubscriptions();
			$res = array();
			foreach($subgroups AS $groupid => $group) {
				if (empty($group)) continue;
				$res[$group->get('id')] = $group->getJSON(array('type' => 'basic'));
			}

			return $res;
		}

		$ret = array();
		foreach($props AS $p) {
			if (isset($this->properties[$p])) {
				$ret[$p] = $this->properties[$p];
			}
		}
			
		if (isset($opts['subscriptions'])) {

			$ret['subscriptions'] = array();
			$subs = $this->getSubscriptions();

			// print_r($subs); exit;

			foreach($subs AS $groupid => $group) {
				if (empty($group)) continue;
				$ret['subscriptions'][$group->get('id')] = $group->getJSON($opts['groups']);
			}
		}

		// if (isset($opts['groups'])) {
		// 	$ret['groups'] = array();
		// 	$groups = $this->getGroups();
		// 	foreach($groups AS $g) {
		// 		$ret['groups'][$g->group->get('id')] = $g->getJSON($opts['groups']);
		// 	}
		// }

		return $ret;
	}


	public function mergeInto(User $into) {

		// 'mail', 'name', 'a', 'photo', 'groups', 'subscriptions'
		// $updateFields = array('');

		$keys = $this->get('userid-sec');
		foreach($keys AS $k) {
			$into->addSecondaryKey($k);	
		}
		$into->addSecondaryKey($this->get('userid'));

		// TODO consider to update attributes on user object that we are merging into.
		// Currently the properties on the object that is merged into this is forgotten.
		

		$accounts = $this->get('accounts', array());
		foreach($accounts AS $accountid => $account) {
			$into->updateAccountinfo($accountid, $account);
		}

		$into->store();
		$this->remove();


		return $into;


	}

	public function addSecondaryKey($key) {
		$updated = false;
		$keys = $this->get('userid-sec');
		if (!in_array($key, $keys)) {
			$keys[] = $key;
			$updated = true;
			$this->set('userid-sec', $keys);
		}
		return $updated;
	}


	/**
	 * Return an array with zero or more entries of secondary keys that contains a given prefix.
	 * In example: type=feide
	 * @param  [type] $type [description]
	 * @return [type]       [description]
	 */
	public function getSecondaryKeysOfType($type) {
		assert('is_string($type)');

		$res = array();

		$keys = $this->get('userid-sec');
		foreach($keys AS $key) {
			if (0 === strpos($key, $type . ':')) {
				$res[] = substr($key, strlen($type) +1);
			}
		}

		return $res;

	}


	public function updateUserFromAttributes(UserAttributeInput $userinput) {

		// Update account attributes if any changes...
		$updated = $this->updateAccountinfo($userinput->accountId, $userinput->accountinfo);

		// Update secondary keys if neccessary
		foreach($userinput->complexId->getKeys() AS $key) {
			if ($this->addSecondaryKey($key)) {
				$updated = true;
			}
		}
		if ($updated) {
			$this->store();
		}

	}

	public function updateAccountinfo($accountid, $attr) {


		$updated = false;
		$accounts = $this->get('accounts', array());

		if (!isset($accounts[$accountid])) {
			$accounts[$accountid] = array();
			$updated = true;
		}
		foreach($attr AS $key => $val) {

			if (!isset($accounts[$accountid][$key])) {
				$accounts[$accountid][$key] = $val; $updated = true;
			} else if ($accounts[$accountid][$key] !== $val) {
				$accounts[$accountid][$key] = $val; $updated = true;
			}

		}
		if ($updated) {
			$this->set('accounts', $accounts);
		}
		
		return $updated;

	}


	/**
	 * IMPORTANT: Make sure that no existing users exists with a matching key when using this function.
	 * This function is mostly used by UserDirectory, and one needs to be careful when using this directly.
	 * 
	 * @param  UserAttributeInput   $attributeInput [description]
	 * @param  boolean $update     [description]
	 * @return [type]              [description]
	 */
	public static function createUserFromAttributes(UserAttributeInput $attributeInput, $update = true) {


		$store = new UWAPStore();

		$userattr = array();

		$userattr["a"] =  Utils::genID();

		/*
		 * Deal with primary and secondary keys for users.
		 */
		$attributeInput->complexId->ensurePri();
		$userattr['userid'] = $attributeInput->complexId->getPri();
		$userattr['userid-sec'] = $attributeInput->complexId->getKeys();


		$userattr['accounts'] = array();
		$userattr['accounts'][$attributeInput->accountId] = $attributeInput->accountinfo;

		if (empty($attributeInput->accountinfo['name'])) {
			throw new Exception('Cannot obtain a proper name from identify provider');
		}
		$userattr['name'] = $attributeInput->accountinfo['name'];
		// echo '<pre>';
		// print_r($userattr);
		// print_r($attributeInput);
		// exit;

		/*
		 * Create and store new user object to databsae
		 */
		$newUser = new User($userattr);
		$newUser->store();
		return $newUser;
	}





	// public static function generateShaddow($properties, $user) {
	// // public static function generate($properties, $user) {


	// 	$allowed = array('userid', 'mail', 'name', 'photo');
	// 	foreach($properties AS $k => $v) {
	// 		if (!in_array($k, $allowed)) {
	// 			unset($properties[$k]);
	// 		}
	// 	}
	// 	$properties["a"] =  Utils::genID();
	// 	$properties['shaddow-generator'] = $user->get('userid');
	// 	$properties['shaddow'] = true;


	// 	$user = new User($properties);
	// 	return $user;
	// }



	public function getVerifier() {
		
		$salt = GlobalConfig::getValue('salt', null, true);
		$rawstr = 'consent' . '|' . $salt . '|' . $this->get('userid');

		error_log('Calculating verifier from this string: ' . $rawstr);
		return sha1($rawstr);
	}




}


