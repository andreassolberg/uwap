<?php


class HTTPClientOAuth2 extends HTTPClient {
	
	public function __construct($config) {
		parent::__construct($config);
	}


	public function get($url, $options) {

		
		$store = new UWAPStore();
		$auth = new Auth();
		$auth->req();
		$userdata = $auth->getUserdata();

		$makeMoreAttempts = true;

		while ($makeMoreAttempts) {
			$makeMoreAttempts = false;

			// OAuth 2.0 library
			$client = new So_Client(new So_StorageUWAP($auth->getRealUserID()));
			// $token = $client->getToken($options["handler"], 'andreas');

			try {

				// error_log('Stored provider config: ' . var_export($this->config));
				// error_log('GET specific options: ' . var_export($options));

				$scopes = null;
				if (isset($this->config["scopes"])) {
					$scopes = $this->config["scopes"];
				}				
				// if (isset($options["scopes"])) {
				// 	$scopes = array_merge($scopes, $options["scopes"]);
				// }
				$feed = $client->getHTTP($options["handler"], null, $url, $scopes, null, false, $options["returnTo"]);

				$parsed = json_decode($feed, true);
				$result = array(
					'status' => 'ok',
					'data' => $parsed
				);
				return $result;


			} catch(So_ExpiredToken $e) {


				error_log("Token was expired. Then wiped. Trying again....");

				// An expired token was attempted to be used.
				// We have wiped this token, and want to try again
				// staring a new flow, obtaining a new token.
				$makeMoreAttempts = true;


			} catch(So_RedirectException $redir ) {

				$result = array(
					'status' => 'redirect',
					'url' => $redir->getURL()
				);
				return $result;

			} 
			
			// catch(Exception $e) {
			// 	return array(
			// 		'status' => 'error',
			// 		'msg' => $e->getMessage()
			// 	);
			// }


		}
		// should never reach this.

	}

}