<?php

class Proxy_REST {
	
	protected $config, $userid;

	function __construct() {

		$this->config = Config::getInstance();
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

				$remoteHost = parse_url($url, PHP_URL_HOST);
				$remoteConfig = Config::getInstanceFromHost($remoteHost);

				if ($remoteConfig->_getValue('type', null, true) !== 'proxy') {
					throw new Exception('This host is not running a soaproxy.');
				}

				$proxyconfig = $remoteConfig->_getValue('proxies', null, true) ;

				// $rawpath = parse_url($url, PHP_URL_PATH);
				$rawpath = $_SERVER['PATH_INFO'];
				if (preg_match('|^/([^/]+)(/.*)$|i', $rawpath, $matches)) {
					$api = $matches[1];
					$restpath = $matches[2];

					if (!isset($proxyconfig[$api])) {
						throw new Exception('API Endpoint is not configured...');
					}

					$realurl = $proxyconfig[$api]['endpoints'][0] . $restpath;

					if (!empty($_SERVER['QUERY_STRING'])) {
						$realurl .= '?' . $_SERVER['QUERY_STRING'];
					}

					// echo("REAL URL IS " . $realurl . "\n\n");
				} else {
					throw new Exception('Does not include a API prefix: ' . $rawpath);
				}


				// // // Initiate an Oauth server handler
				$oauth = new OAuth();

				// // // Get provided Token on this request, if present.
				$token = $oauth->getProvidedToken();

				$providerID = $remoteConfig->getID();

				$client = HTTPClient::getClientWithConfig($proxyconfig[$api], $providerID);
				if ($token) {
					
					$clientid = $token->getClientID();
					
					$ensureScopes = array('soa_' . $providerID);
					$oauth->check(null, $ensureScopes);

					// print_r($ensureScopes);  exit;


					$userdata = $token->getUserdataWithGroups();
					$client->setAuthenticated($userdata);
					$scopes = $oauth->getApplicationScopes('soa', $providerID);
					$client->setAuthenticatedClient($clientid, $scopes);
				}
				$response = $client->get($realurl, $args); 

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

