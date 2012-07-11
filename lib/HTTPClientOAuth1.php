<?php


class HTTPClientOauth1 extends HTTPClient {
	


	public function get($url, $options) {

		$session = SimpleSAML_Session::getInstance();

		$config = new Config();
		$store = new UWAPStore();
		$auth = new Auth();
		$auth->req();
		$userdata = $auth->getUserdata();

		$consumer = new sspmod_oauth_Consumer($this->config['client_id'], $this->config['client_secret']);

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
			error_log("Accessing signed URL endpoint " . $url);

			$result = array("status" => 'ok');
			$result['data'] = $consumer->getUserInfo($url, $accessToken);
			return $result;			
		}


		// Get the request token
		$requestToken = $consumer->getRequestToken($this->config['request']);
		
		error_log ("Got a request token from the OAuth service provider [" . $requestToken->key . "] with the secret [" . $requestToken->secret . "]");

		// Authorize the request token
		$url = $consumer->getAuthorizeRequest($this->config['authorize'], $requestToken, FALSE, 
			'httsp://' . $this->config["subhost"] . '.' . Config::hostname() . '/_/oauth1callback/' . $options['handler']
		);
		
		$state = array(
			'requestToken' => $requestToken,
			'return' => $options['returnTo']
		);
		$session->setData('appengine:oauth',  $options["handler"] . ':' . $requestToken->key, $state, 3600);
		
		
		$result = array(
			'status' => 'redirect',
			'url' => $url,
		);
		return $result;
	}

}