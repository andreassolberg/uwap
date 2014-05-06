<?php

class Proxy_REST {
	
	protected $config, $userid;

	function __construct() {

		$this->globalconfig = GlobalConfig::getInstance();
		$this->proxy = $this->globalconfig->getApp();

		// header('Content-type: text/plain');
		// print_r($this->proxy);

		if (! ($this->proxy instanceof APIProxy)) {
			throw new Exception('Invalid proxy');
		}

	}



	function show() {


		try {

			if (Utils::route(false, '.*', &$qs, &$args)) {

				// if (empty($args['url'])) {
				// 	throw new Exception("Missing parameter [url]");
				// }

				// if (empty($args['appid'])) {
				// 	throw new Exception("Missing parameter [appid]");
				// }
				

				/*
				 * TODO: Convert incoming request to a new method url path qs etc.
				 * make it work.
				 */


				$url = $args["url"];
				$handler = "plain";

				// $remoteConfig = Config::getInstanceFromHost($remoteHost);



				// $proxyconfig = $remoteConfig->_getValue('proxy', null, true) ;

				$proxyconfig = $this->proxy->get('proxy');



				// $rawpath = parse_url($url, PHP_URL_PATH);
				$rawpath = $_SERVER['PATH_INFO'];
				if (preg_match('|^(/.*)$|i', $rawpath, $matches)) {
					// $api = $matches[1];
					$restpath = $matches[1];

					if (!isset($proxyconfig)) {
						throw new Exception('API Endpoint is not configured...');
					}

					$realurl = $proxyconfig['endpoints'][0] . $restpath;

					if (!empty($_SERVER['QUERY_STRING'])) {
						$realurl .= '?' . $_SERVER['QUERY_STRING'];
					}

					// echo("REAL URL IS " . $realurl . "\n\n");
				} else {
					throw new Exception('Does not include a API prefix: ' . $rawpath);
				}


				$providerID = $this->proxy->get('id');



				// header('Content-type: text/plain');
				// print_r($proxyconfig);
				// echo 'provider id '. $providerID;

				// exit;




				// // // Initiate an Oauth server handler
				$oauth = new OAuth();

				// // // Get provided Token on this request, if present.
				$token = $oauth->getProvidedToken();

				// echo "TOKEN used was : <pre>"; print_r($token); exit;


				// echo "proxyconfig was : <pre>"; print_r($proxyconfig); exit;

				$client = HTTPClient::getClientWithConfig($proxyconfig, $providerID);
				if ($token) {
					
					$clientid = $token->getClientID();
					
					$ensureScopes = array('rest_' . $providerID);
					$oauth->check(null, $ensureScopes);

					// print_r($ensureScopes);  exit;


					$userdata = $token->getUserdataWithGroups();
					$client->setAuthenticated($userdata);
					$scopes = $oauth->getApplicationScopes('rest', $providerID);
					$client->setAuthenticatedClient($clientid, $scopes);
				}
				$response = $client->get($realurl, $args); 


				// echo "Response is "; echo $resposne;

				header('Content-Type: application/json; charset=utf-8');
				echo json_encode($response);

			} else {
				throw new Exception('Bad request.');
			}

		} catch(Exception $e) {

			// TODO: Catch OAuth token expiration etc.! return correct error code.

			header("Status: 500 Internal Error");
			header('Content-Type: text/plain; charset: utf-8');
			echo "Error stack trace: \n";
			print_r($e);


		}

	}

}

