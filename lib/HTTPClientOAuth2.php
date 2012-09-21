<?php


class HTTPClientOAuth2 extends HTTPClient {
	


	public function get($url, $options) {

		
		$store = new UWAPStore();

		// error_log("Config " . json_encode($this->config));
		// if (isset($this->config["sharedtokens"]) && $this->config["sharedtokens"] === true) {
		// 	error_log("SHARED Tokens: true");
		// 	$userid = '_sharedtokens';
		// } else {
			// error_log("SHARED Tokens: false");
			if (empty($this->userid)) {
				throw new Exception("REST handler requires an user authenticated token to perform operation.");
			}
			$userid = $this->userid;
		// }
		$makeMoreAttempts = true;


		while ($makeMoreAttempts) {
			$makeMoreAttempts = false;


			// OAuth 2.0 library
			$client = new So_Client(new So_StorageUWAP($userid, $this->appid));

			try {

				error_log('Stored provider config: ' . var_export($this->config, true));
				error_log('GET specific options: ' . var_export($options, true));

				$requestedScopes = array();

				if (isset($this->config["defaultscopes"])) {
					$requestedScopes = explode(' ', $this->config["defaultscopes"]);
				// } else if (isset($this->config["scopes"])) {
				// 	$requestedScopes = $this->config["scopes"];
				}	
				if (isset($options["requestedScopes"])) {
					$requestedScopes = array_merge($requestedScopes, $options["requestedScopes"]);
				}

				// echo 'about to start request: ' . json_encode($requestedScopes); exit;

				$requiredScopes = array();
				if (isset($options["requiredScopes"])) {
					$requiredScopes = array_merge($requiredScopes, $options["requiredScopes"]);
				}

				$allowRedirect = false;
				if (isset($options['allowRedirect'])) {
					$allowRedirect = $options['allowRedirect'];
				}

				error_log("Adding returnto parameter to gethttp oauth2 " + $options["returnTo"] );

				// getHTTP($provider_id, $user_id, $url, array $requestScope = null, array $requireScope = null, $allowRedirect = true, $returnTo = null) {
				// error_log("Scopes: " . var_export($this->config, true));
				$feed = $client->getHTTP($options["handler"], null, $url, $requestedScopes, $requiredScopes, $allowRedirect, $options["returnTo"]);

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

			} catch (So_InsufficientScope $e) {
				$result = array(
					'status' => 'error',
					'message' => $e->getMessage()
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