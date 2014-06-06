<?php

class User extends StoredModel {


	protected static $collection = 'users';
	protected static $primaryKey = 'userid';
	protected static $validProps = array(
		'userid', 'userid-sec', 'mail', 'name', 'a', 'photo', 'groups', 'subscriptions',
		'customdata',
		'created', 'updated',
		'shaddow-generator', 'shaddow');

	protected $groupconnector;

	public function __construct($properties) {

		// Utils::dump('create new user user:', $properties); exit;
		parent::__construct($properties);

		$this->groupconnector = new GroupConnector($this);
	}

	public function hasRealm($realm) {
		// echo "checking if has realm " . $realm . " for me " . $this->get('userid') . "\n";
		$pos = strpos($this->get('userid'), '@' . $realm);
		$has = ($pos !== false);

		return $has;
	}

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
		
		$into->store();
		$this->remove();


		return $into;


	}

	public function addSecondaryKey($key) {
		$keys = $this->get('userid-sec');
		if (!in_array($key, $keys)) {
			$keys[] = $key;
			$this->set('userid-sec', $keys);
		}
	}


	public static function getUserIDfromAttributes(array $attributes) {

		$map = GlobalConfig::getValue('useridattrs', null, true);
		
		$uid = new ComplexUserID();

		$userattr = array();
		foreach($map AS $key => $akey) {
			if (isset($attributes[$akey])) {
				$uid->add($key, $attributes[$akey][0]);
			}
		}

		// $uid->add('nnin', '101080');
		return $uid;

	}




	public static function interpretAttributes(array $attributes) {
		$map = GlobalConfig::getValue('attributeMap', null, true);


		/*
		 * Pick the attributes defined in the configuration mapping, and store the attributes in 
		 * a new attribute array $userattr
		 */
		$userattr = array();
		foreach($map AS $key => $akey) {
			if (isset($attributes[$akey])) {
				$userattr[$key] = $attributes[$akey][0];	
			}
		}




		// $groups = self::groupsFromAttributes($attributes);
		

		/*
		 * The user field customdata can contain a set of custom data to be used for other purposes
		 * such as generating groups from it. 
		 */
		$collectUserdata = array(
			'eduPersonEntitlement', 'eduPersonAffiliation',
			'eduPersonOrgDN:o', 'eduPersonOrgUnitDN:ou', 
			'eduPersonOrgUnitDN', 'eduPersonOrgUnitDN:ou',
			'eduPersonOrgUnitDN:norEduOrgUnitUniqueIdentifier'
		);
		$userattr['customdata'] = array();
		foreach($collectUserdata AS $key) {
			if (isset($attributes[$key])) {
				$userattr['customdata'][$key] = $attributes[$key];	
			}
		}


		// $existingUser = self::getByID($userattr['userid'], true);
		// if ($existingUser !== null) {

		// 	if ($update) {
		// 		foreach($userattr AS $key => $value) {
		// 			$existingUser->set($key, $value);	
		// 		}
		// 		$existingUser->store();
		// 	}
		// 	return $existingUser;
		// }



		return $userattr;
	}

	/**
	 * Update this user object with attributes from the IdP.
	 * @param  array  $attributes [description]
	 * @return [type]             [description]
	 */
	public function updateFromAttributes(array $attributes) {

		$userattr = self::interpretAttributes($attributes);

		$isUpdated = false;

		foreach(array('mail', 'name', 'photo', 'customdata') AS $key) {
			if (isset($userattr[$key])) {
				if ($this->set($key, $userattr[$key]) === true) {
					error_log("Updating user, and this property was changed [" . $key . "]    From [". $this->get($key) . "] to [" . $userattr[$key] . "]");
					$isUpdated = true;
				}
			}
		}

		if ($isUpdated) {
			$this->store();
			return true;
		}
		return false;

	}

	/**
	 * IMPORTANT: Make sure that no existing users exists with a matching key when using this function.
	 * This function is mostly used by UserDirectory, and one needs to be careful when using this directly.
	 * 
	 * @param  array   $attributes [description]
	 * @param  boolean $update     [description]
	 * @return [type]              [description]
	 */
	public static function createUserFromAttributes(array $attributes, $update = true) {


		$store = new UWAPStore();

		$userattr = self::interpretAttributes($attributes);
		$userattr["a"] =  Utils::genID();

		/*
		 * Deal with primary and secondary keys for users.
		 */
		$complexID = self::getUserIDfromAttributes($attributes);
		if (!$complexID->isValid()) throw new Exception('Cannot register new user, because no valid userid was provided.');
		// if ($complexID->hasPri()) throw new Exception('Trying to create new user that already exists... Bug.');

		$complexID->genPri();
		$userattr['userid'] = $complexID->getPri();
		$userattr['userid-sec'] = $complexID->getKeys();


		// if (empty($userattr['mail'])) {
		// 	throw new Exception('Cannot obtain a proper mail from identify provider');
		// }

		if (empty($userattr['name'])) {
			throw new Exception('Cannot obtain a proper name from identify provider');
		}

		if (isset($userattr['subscriptions'])) {	
			unset ($userattr['subscriptions']);
		}

		$newUser = new User($userattr);
		$newUser->store();
		return $newUser;
	}





	public static function generateShaddow($properties, $user) {
	// public static function generate($properties, $user) {


		$allowed = array('userid', 'mail', 'name', 'photo');
		foreach($properties AS $k => $v) {
			if (!in_array($k, $allowed)) {
				unset($properties[$k]);
			}
		}
		$properties["a"] =  Utils::genID();
		$properties['shaddow-generator'] = $user->get('userid');
		$properties['shaddow'] = true;


		$user = new User($properties);
		return $user;
	}



	public function getVerifier() {
		
		$salt = GlobalConfig::getValue('salt', null, true);
		$rawstr = 'consent' . '|' . $salt . '|' . $this->get('userid');

		error_log('Calculating verifier from this string: ' . $rawstr);
		return sha1($rawstr);
	}




}


