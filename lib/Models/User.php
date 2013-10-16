<?php

class User extends Model {


	protected static $collection = 'users';
	protected static $primaryKey = 'userid';
	protected static $validProps = array(
		'userid', 'mail', 'name', 'a', 'photo', 'groups', 'subscriptions',
		'shaddow-generator', 'shaddow');

	protected $groupconnector;

	public function __construct($properties) {
		parent::__construct($properties);

		$this->groupconnector = new GroupConnector($this);
	}


	public function getGroups() {

		return $this->groupconnector->getGroups();

	}

	public function getSubscriptions() {
  
		return $this->get('subscriptions');

	}



	public function subscribe(Group $group) {
		$groupid = $group->get('id');
		$subscriptions = $this->getSubscriptions();
		$subscriptions = self::array_add($subscriptions, $groupid);		
		$this->set('subscriptions', $subscriptions);
	}

	public function unsubscribe(Group $group) {
		$groupid = $group->get('id');
		$subscriptions = $this->getSubscriptions();
		$subscriptions = self::array_remove($subscriptions, $groupid);		
		$this->set('subscriptions', $subscriptions);
	}

	public function getJSON($opts = array()) {

		$props = self::$validProps;
		if (isset($opts['type']) && $opts['type'] === 'basic') {
			$props = array('userid', 'mail', 'name', 'a');
		}

		$ret = array();
		foreach($props AS $p) {
			if (isset($this->properties[$p])) {
				$ret[$p] = $this->properties[$p];
			}
		}

		if (isset($opts['groups'])) {
			$ret['groups'] = array();
			$groups = $this->getGroups();
			foreach($groups AS $g) {
				$ret['groups'][] = $g->getJSON($opts['groups']);
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


		$existingUser = self::getByID($userattr['userid']);
		if ($existingUser !== null) {


			if ($update) {
				foreach($userattr AS $key => $value) {
					$existingUser->set($key, $value);	
				}
				$existingUser->store();
			}

			return $existingUser;
		}


		if (empty($userattr['mail'])) {
			throw new Exception('Cannot obtain a proper mail from identify provider');
		}

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


