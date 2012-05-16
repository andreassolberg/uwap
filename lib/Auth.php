<?php


class Auth {
	
	protected $config;
	protected $as;
	protected $salt = 'sldkfjsdfsdf87sd6f87sd6f';

	public function __construct($appid = null) {

		$this->config = new Config($appid);
		$this->store = new UWAPStore();
		$this->as = new SimpleSAML_Auth_Simple('default-sp');

	}

	function authorized() {
		$query = array(
			"app" => $this->config->getID()
		);
		$result = $this->store->queryOneUser("consent", $this->getRealUserID(), $query);
		error_log("Query authorization: " . var_export($result, true));
		if (empty($result)) return false;
		if (isset($result["ok"]) && $result["ok"] === true) return true;
	}

	function authorize() {
		$this->req();
		$query = array(
			"app" => $this->config->getID()
		);
		$result = $this->store->queryOneUser("consent", $this->getRealUserID(), $query);
		if (empty($result)) $result = array("app" => $this->config->getID());
		$result["ok"] = true;
		$this->store->store("consent", $this->getRealUserID(), $result);
	}

	public function getGroups() {

		$attributes = $this->as->getAttributes();

		$groups = array();
		// echo '<pre>' . "\n";
		// print_r($attributes);
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
		// print_r($groups);
		// exit;

		return $groups;
	}

	public function getVerifier() {
		$attributes = $this->as->getAttributes();

		if (empty($attributes['displayName'])) throw new Exception("Can not obtain displayName from authenticated user");
		if (empty($attributes['eduPersonPrincipalName'])) throw new Exception("Can not obtain eduPersonPrincipalName from authenticated user");
		if (empty($attributes['mail'])) throw new Exception("Can not obtain mail from authenticated user");
		return sha1('consent' . '|' . $this->salt . '|' . $attributes['eduPersonPrincipalName'][0] . '|' . $this->config->getID());
	}

	public function authenticated() {
		return $this->as->isAuthenticated();
	}


	public function check() {
		if (!$this->authenticated()) return false;
		if (!$this->authorized()) return false;
		return true;
	}

	public function checkPassive() {
		if (!$this->authenticated()) {
			// If a passive authnrequest was attempted less than one minute ago, return false
			if (isset($_SESSION['passiveAttempt']) && $_SESSION['passiveAttempt'] > (time() - 60)) {
				return false;
			} else {
				$_SESSION['passiveAttempt'] = time();

				SimpleSAML_Utilities::redirect('http://app.bridge.uninett.no/login', array(
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

		$this->as->login(array(
            'isPassive' => true,
            'ErrorURL' => SimpleSAML_Utilities::selfURL(),
        ));

	}

	public function req($allowRedirect = false, $return = null) {
		if ($return === null) $return = SimpleSAML_Utilities::selfURL();
		if (!$this->as->isAuthenticated()) {
			if ($allowRedirect) {
				SimpleSAML_Utilities::redirect('http://app.bridge.uninett.no/login', array(
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

	public function getUserdata() {

		$attributes = $this->as->getAttributes();

		if (empty($attributes['displayName'])) throw new Exception("Can not obtain displayName from authenticated user");
		if (empty($attributes['eduPersonPrincipalName'])) throw new Exception("Can not obtain eduPersonPrincipalName from authenticated user");
		if (empty($attributes['mail'])) throw new Exception("Can not obtain mail from authenticated user");

		$data = array(
			'name' => $attributes['displayName'][0],	
			'userid' => sha1($this->salt . '|' . $attributes['eduPersonPrincipalName'][0] . '|' . $this->config->getID()),
			'mail' => $attributes['mail'][0],
			'groups' => $this->getGroups(),
		);

		return $data;

	}

}