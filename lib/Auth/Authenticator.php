<?php


class Authenticator {

	protected $as, $user;

	public function __construct() {

		$this->as = new SimpleSAML_Auth_Simple('default-sp');
		$this->user = null;

	}


	/**
	 * Assumes the user is not logged in, and performs a isPassive=true login request against the IdP
	 * @return [type] [description]
	 */
	protected function authenticatePassive() {

		$this->as->login(array(
			'isPassive' => true,
			'ErrorURL' => SimpleSAML_Utilities::addURLparameter(SimpleSAML_Utilities::selfURL(), array(
				"error" => 1,
			)),
		));

	}


	/**
	 * Require authentication of the user. This is meant to be used with user frontend access.
	 * 
	 * @param  boolean $isPassive     [description]
	 * @param  boolean $allowRedirect Set to false if using on an API where user cannot be redirected.
	 * @param  [type]  $return        URL to return to after login.
	 * @return void
	 */
	public function req($isPassive = false, $allowRedirect = false, $return = null) {

		if ($this->as->isAuthenticated()) {
			return;
		}

		// User is not authenticated locally.
		// If allowed, attempt is passive authentiation.
		if ($isPassive && $allowRedirect) {

			$this->authenticatePassive();
			return;
		}

		if ($allowRedirect) {
			if ($return === null) $return = SimpleSAML_Utilities::selfURL();

			$defaultidp = GlobalConfig::getValue('idp');
			$options = array('saml:idp' => $defaultidp);
			if (isset($_COOKIE['idp'])) {
				$options = array('saml:idp' => $_COOKIE['idp']);
			}
			$this->as->requireAuth($options);
			return;

		}


		throw new Exception('User is not authenticated. Authentication is required for this operation.');
	}


	// TODO: Make sure that the user is not updated to storage at each request...
	public function getUser() {

		if ($this->user !== null) return $this->user;

		$attributes = $this->as->getAttributes();
		$this->user = User::fromAttributes($attributes);

		return $this->user;

	}


}
