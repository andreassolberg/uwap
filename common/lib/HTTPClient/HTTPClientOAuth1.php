<?php


// TODO: Did some major changes to HTTPClient that needs to be reflected in this class, if it will be used anymore...
// 



class HTTPClientOauth1 extends HTTPClient {
	


	public function get($method, $url, $options, $method = 'GET', $data = null) {


		$session = SimpleSAML_Session::getInstance();

		$config = Config::getInstance();
		$store = new UWAPStore();
		$auth = new Auth();
		$auth->req();
		$userdata = $auth->getUserdata();

		$this->verifyURL($url);

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

			$method = 'get'; $data = null;
			if (isset($options['method'])) {
				$method = $options['method'];
			}
			if (isset($options['data'])) {
				$data = $options['data'];
			}

			// getHTTPsigned($url, $accessToken, $method = 'GET', $data = null) {
			$result['data'] = $consumer->getHTTPsigned($url, $accessToken, $method, $data);

			return $result;			
		}


		// Get the request token
		$requestToken = $consumer->getRequestToken($this->config['request']);
		
		error_log ("Got a request token from the OAuth service provider [" . $requestToken->key . "] with the secret [" . $requestToken->secret . "]");

		// Authorize the request token
		$url = $consumer->getAuthorizeRequest($this->config['authorize'], $requestToken, FALSE, 
			'httsp://' . $this->config["subhost"] . '.' . GlobalConfig::hostname() . '/_/oauth1callback/' . $options['handler']
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