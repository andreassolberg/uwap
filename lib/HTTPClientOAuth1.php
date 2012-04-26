<?php


class HTTPClientOauth1 extends HTTPClient {
	
	public function __construct($config) {
		parent::__construct($config);
	}


	public function get($url, $options) {

		$session = SimpleSAML_Session::getInstance();

		$config = new Config();
		$store = new UWAPStore();
		$auth = new Auth();
		$auth->req();
		$userdata = $auth->getUserdata();

		$consumer = new sspmod_oauth_Consumer($this->config['key'], $this->config['secret']);

		// print_r($this->config); exit;

		// TODO: this will only get one access token. We MUST handle multiple access tokens where one or more is expired.
		$query = array(
			"app" => $config->getID()
		);
		$savedState = $store->queryOneUser("oauth1-client", $auth->getRealUserID(), $query);

		// $savedState = $session->getData('appengine:accesstoken',  $options["handler"]);
		if (!empty($savedState) && isset($savedState['accessToken']["key"]) && isset($savedState['accessToken']["secret"])) {
			$accessToken = new OAuthToken($savedState['accessToken']["key"], $savedState['accessToken']["secret"]);

			error_log("Got this access token:");
			error_log(var_export($accessToken, true));

			$result = array("status" => 'ok');
			$result['data'] = $consumer->getUserInfo($url, $accessToken);
			return $result;			
		}


		// Get the request token
		$requestToken = $consumer->getRequestToken($this->config['request']);
		
		error_log ("Got a request token from the OAuth service provider [" . $requestToken->key . "] with the secret [" . $requestToken->secret . "]");

		// Authorize the request token
		$url = $consumer->getAuthorizeRequest($this->config['authorize'], $requestToken, FALSE, 
			'http://' . $this->config["subhost"] . '.app.bridge.uninett.no/_/api/callbackOAuth1.php?' . 
			'provider=' . $options["handler"] . '&return=' . urlencode($options['returnTo']) . 
			'&requestToken=' . urlencode($requestToken->key)
		);
		
		$state = array(
			'requestToken' => $requestToken,
		);
		$session->setData('appengine:oauth',  $options["handler"] . ':' . $requestToken->key, $state, 3600);
		
		
		$result = array(
			'status' => 'redirect',
			'url' => $url,
		);
		return $result;
	}

}