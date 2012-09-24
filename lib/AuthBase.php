<?php


class AuthBase {
	
	protected $config;
	protected $as;

	protected static $basicusers = array();

	public function __construct() {

		$this->store = new UWAPStore();
		$this->as = new SimpleSAML_Auth_Simple('default-sp');

	}


	/*
		Authorization objects are stored in 'consent'

		{
			"client_id": "appname",
			"uwap-userid": "andreas@uninett.no",
			"scopes": ["app_name_user"] // verified and consented scopes.
		}

	 */

	function getUser($a) {
		// echo "getUser(" . $a . ")";
		$query = array('a' => $a); 
		$search = $this->store->queryOne('users', array('a' => $a));
		if (empty($search)) return false;

		return $search;
	}

	function getUserBasic($userid) {

		if (!empty(self::$basicusers[$userid])) {
			return self::$basicusers[$userid];
		}

		$query = array('userid' => $userid);
		$search = $this->store->queryOne('users', $query, array('name', 'mail', 'a'));
		if (empty($search)) return false;

		self::$basicusers[$userid] = $search;

		return $search;
	}

	function storeUser() {

		$user = $this->getUserdata();

		if (empty($user['userid'])) throw new Exception('Missing user attribute [userid]');

		$search = $this->store->queryOne('users', array('userid' => $user['userid']));
		if (!empty($search)) return false;

		$user['a'] = Utils::genID();
		$this->store->store('users', null, $user);
		return true;

		// $data = array(
		// 	'name' => $attributes['displayName'][0],	
		// 	'userid' => $attributes['eduPersonPrincipalName'][0],
		// 	'mail' => $attributes['mail'][0],
		// 	'groups' => $this->getGroups(),
		// 	photo
		// );
		
	}


	/**
	 * Check if user has authorized client to a set of scopes.
	 * @param  [type] $cliend_id The client id
	 * @param  [type] $scopes    An optional arrray of scopes to verify.
	 * @return [type]            Returns an array of remaining scopes, returns true if all scopes are authorized.
	 */
	function authorized($cliend_id, $scopes = array()) {

		$query = array(
			"client_id"   => $cliend_id,
			"uwap-userid" => $this->getRealUserID(),
		);
		$result = $this->store->queryOne("consent", $query);

		UWAPLogger::debug('auth', 
			'Checking if authenticated user [' . $this->getRealUserID() . '] is also authorized to use client [' . $cliend_id . ']', 
			$result);

		if (empty($result)) return $scopes;

		if (is_array($result['scopes'])) {
			$remaining = array_diff($scopes, $result['scopes']);
			if (empty($remaining)) return true;
			return $remaining;
		}
		return $scopes;
	}


	// function authorize() {
	// 	$this->req();
	// 	$query = array(
	// 		"app" => $this->config->getID(),
	// 		"uwap-userid" => $this->getRealUserID(),
	// 	);
	// 	$result = $this->store->queryOne("consent", $query);
	// 	if (empty($result)) $result = array("app" => $this->config->getID());
	// 	$result["ok"] = true;
	// 	$this->store->store("consent", $this->getRealUserID(), $result);
	// }

	public function getGroups() {

		$attributes = $this->as->getAttributes();

		$groups = array();

		$realm = 'na.feide.no';
		if (!empty($attributes['eduPersonPrincipalName']) && !empty($attributes['eduPersonOrgDN:o'])) {
			if (preg_match('/^(.*?)@(.*?)$/', $attributes['eduPersonPrincipalName'][0], $matches)) {
				$realm = $matches[2];
				$orgname = $attributes['eduPersonOrgDN:o'][0];
				$groups['@realm:' . $realm] = $orgname;
			}
		}
		if (!empty($attributes['eduPersonOrgUnitDN']) && !empty($attributes['eduPersonOrgUnitDN:cn'])) {
			for($i = 0; $i < count($attributes['eduPersonOrgUnitDN']); $i++) {
				$key = sha1($attributes['eduPersonOrgUnitDN'][$i]);
				$name = $attributes['eduPersonOrgUnitDN:cn'][$i];
				$groups['@orgunit:' . $realm . ':' . $key] = $name;
			}
		}

		$groupmanager = new GroupManager($this->getRealUserID());
		$adhocgroups = $groupmanager->getMyGroups();
		if (!empty($adhocgroups)) {
			foreach($adhocgroups AS $adhocgroup) {
				$groups[$adhocgroup['id']] = $adhocgroup['title'];
			}
		}

		if (in_array($this->getRealUserID(), GlobalConfig::getValue('admins', array()))) {
			$groups['uwapadmin'] = 'UWAP System Administrators';
		}

		return $groups;
	}

	public function memberOf($group) {
		$groups = $this->getGroups();
		return array_key_exists($group, $groups);
	}

	public function getVerifier() {
		$attributes = $this->as->getAttributes();

		if (empty($attributes['displayName'])) throw new Exception("Can not obtain displayName from authenticated user");
		if (empty($attributes['eduPersonPrincipalName'])) throw new Exception("Can not obtain eduPersonPrincipalName from authenticated user");
		if (empty($attributes['mail'])) throw new Exception("Can not obtain mail from authenticated user");

		$salt = GlobalConfig::getValue('salt', null, true);

		return sha1('consent' . '|' . $salt . '|' . $attributes['eduPersonPrincipalName'][0]);
	}

	public function authenticated() {
		return $this->as->isAuthenticated();
	}


	// public function check() {
	// 	if (!$this->authenticated()) return false;
	// 	if (!$this->authorized()) return false;
	// 	return true;
	// }

	public function checkPassive() {
		if (!$this->authenticated()) {
			// If a passive authnrequest was attempted less than one minute ago, return false
			if (isset($_SESSION['passiveAttempt']) && $_SESSION['passiveAttempt'] > (time() - 60)) {
				return false;
			} else {
				$_SESSION['passiveAttempt'] = time();

				SimpleSAML_Utilities::redirect(GlobalConfig::scheme() . '://core.' . GlobalConfig::hostname() . '/login', array(
					'return' => $return,
					'app' => $this->config->getID()
				));

			}
		}
		if (!$this->authorized()) return false;
		return true;
	}

	public function authenticate() {
		$this->as->requireAuth();
	}

	public function authenticatePassive() {

		if (!$this->as->isAuthenticated()) {
			error_log(' > authenticatePassive() NOT AUTH');
		} else {
			error_log(' > authenticatePassive()     AUTH ');
		}

		if (!$this->as->isAuthenticated()) {
			$this->as->login(array(
	            'isPassive' => true,
	            'ErrorURL' => SimpleSAML_Utilities::addURLparameter(SimpleSAML_Utilities::selfURL(), array(
	            	"error" => 1,
	            )),
	        ));
		}

	}

	public function req($allowRedirect = false, $return = null) {
		if ($return === null) $return = SimpleSAML_Utilities::selfURL();
		if (!$this->as->isAuthenticated()) {
			if ($allowRedirect) {
				SimpleSAML_Utilities::redirect(GlobalConfig::scheme() . '://core.' . GlobalConfig::hostname() . '/login', array(
					'return' => $return,
					'app' => $this->config->getID()
				));
			}
			throw new Exception("User is not authenticated");
		}
	}

	public function getRealUserID() {
		$attributes = $this->as->getAttributes();

		if (empty($attributes['displayName'])) throw new Exception("Can not obtain displayName from authenticated user");
		if (empty($attributes['eduPersonPrincipalName'])) throw new Exception("Can not obtain eduPersonPrincipalName from authenticated user");
		if (empty($attributes['mail'])) throw new Exception("Can not obtain mail from authenticated user");

		return $attributes['eduPersonPrincipalName'][0];
	}


	public function getUserdata($appid = null) {

		$attributes = $this->as->getAttributes();

		if (empty($attributes['displayName'])) throw new Exception("Can not obtain displayName from authenticated user");
		if (empty($attributes['eduPersonPrincipalName'])) throw new Exception("Can not obtain eduPersonPrincipalName from authenticated user");
		if (empty($attributes['mail'])) throw new Exception("Can not obtain mail from authenticated user");

		$data = array(
			'name' => $attributes['displayName'][0],	
			'userid' => $attributes['eduPersonPrincipalName'][0],
			'mail' => $attributes['mail'][0],
			'groups' => $this->getGroups()
		);

		if (!empty($attributes['jpegPhoto'])) {
			$data['photo'] = $attributes['jpegPhoto'][0];
		}

		if (isset($appid)) {
			$salt = GlobalConfig::getValue('salt', null, true);
			$data['appuserid'] = sha1($salt . '|' . $attributes['eduPersonPrincipalName'][0] . '|' . $appid);
		}

		return $data;

	}

}