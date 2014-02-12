<?php

class User extends StoredModel {


	protected static $collection = 'users';
	protected static $primaryKey = 'userid';
	protected static $validProps = array(
		'userid', 'mail', 'name', 'a', 'photo', 'groups', 'subscriptions',
		'customdata',
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
			$ids[] = $g->group->get('id');
		}
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

		if (isset($opts['groups'])) {
			$ret['groups'] = array();
			$groups = $this->getGroups();
			foreach($groups AS $g) {
				$ret['groups'][$g->group->get('id')] = $g->getJSON($opts['groups']);
			}
		}

		return $ret;
	}


	public static function fromAttributes($attributes, $update = true) {

		$store = new UWAPStore();

		$map = GlobalConfig::getValue('attributeMap', null, true);

		$userattr = array();
		foreach($map AS $key => $akey) {
			if (isset($attributes[$akey])) {
				$userattr[$key] = $attributes[$akey][0];	
			}
		}

		if (empty($userattr['userid'])) {
			throw new Exception('Cannot obtain a proper userid from identify provider');
		}


		// $groups = self::groupsFromAttributes($attributes);

		
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


		$existingUser = self::getByID($userattr['userid'], true);
		if ($existingUser !== null) {



			if ($update) {
				foreach($userattr AS $key => $value) {
					$existingUser->set($key, $value);	
				}
				$existingUser->store();
			}

			return $existingUser;
		}

		$userattr["a"] =  Utils::genID();


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

		// $groups = self::groupsFromAttributes($attributes);
		// UWAP_Utils::dump('grouos', $groups); exit;

		$newUser->store();


		return $newUser;
	}


	/*
	 * DEPRECATED
	 */
	public static function groupsFromAttributes($attributes) {

		$groups = array();

		$realm = 'norealm_uwap_org';
		if (!empty($attributes['eduPersonPrincipalName']) && !empty($attributes['eduPersonOrgDN:o'])) {
			if (preg_match('/^(.*?)@(.*?)$/', $attributes['eduPersonPrincipalName'][0], $matches)) {
				$realm = str_replace('.', '_', $matches[2]);
				$orgname = $attributes['eduPersonOrgDN:o'][0];
				$groups['uwap:realm:' . $realm] = $orgname;
			}
		}
		if (!empty($attributes['eduPersonOrgUnitDN']) && !empty($attributes['eduPersonOrgUnitDN:ou'])) {
			for($i = 0; $i < count($attributes['eduPersonOrgUnitDN']); $i++) {
				$key = sha1($attributes['eduPersonOrgUnitDN'][$i]);
				$name = $attributes['eduPersonOrgUnitDN:ou'][$i];
				$groups['uwap:orgunit:' . $realm . ':' . $key] = $name;
			}
		}


		return $groups;



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


