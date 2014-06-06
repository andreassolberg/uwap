<?php



/**
 * This class handles all authentication, and uses SimpleSAMLphp for that task.
 * It will also handle all local user creation. All new users will be stored in the user repository.
 * 
 */
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

		error_log("Perform passive authentication..");

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

			$defaultidp = GlobalConfig::getValue('idp', false);
			$options = array();

			if ($defaultidp !== false) {
				$options['saml:idp'] = $defaultidp;
			}
			if (isset($_COOKIE['idp'])) {
				$options['saml:idp'] = $_COOKIE['idp'];
			}

			// echo "about to require authentication "; print_r($options); print_r($_COOKIE); exit;
			$this->as->requireAuth($options);

			return;

		}


		throw new Exception('User is not authenticated. Authentication is required for this operation.');
	}


	// TODO: Make sure that the user is not updated to storage at each request...
	public function getUser($update = false) {

		if ($this->user !== null) return $this->user;

		$attributes = $this->as->getAttributes();

		// echo '<pre> UserID '; print_r($attributes);


		$uid = User::getUserIDfromAttributes($attributes);

		if (!$uid->isValid()) {
			throw new Exception('No valid user identifiers provided from the authentication layer. Not able to create new user.');
		}

		$directory = new UserDirectory();
		$search = $directory->lookup($uid);


		// echo 'Found these users: <pre>'; print_r($search);
		// echo 'Query '; print_r( $uid->getQuery());


		if (count($search) === 0) {
			// Create a new user
			
			$this->user = User::fromAttributes($uid, $attributes, $update);

		} else if (count($search) === 1) {

			$this->user = $search[0];


		} else if (count($search) === 2) {

			$this->user = $directory->merge($search[0], $search[1]);

		} else {

			throw new Exception('Not implemented merge of more than two items yet');
			// $this->user = $directory->merge($search);

		}


		// exit;
		// $this->user = User::fromAttributes($attributes, $update);

		return $this->user;

	}


}
