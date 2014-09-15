<?php


/**
* GateKeeper implementation
*/
class GateKeeper {

	
	protected $config, $userid;

	function __construct($proxy) {

		$this->globalconfig = GlobalConfig::getInstance();
		$this->proxy = $proxy;

		// header('Content-type: text/plain');
		// print_r($this->proxy);

		if (! ($this->proxy instanceof APIProxy)) {
			throw new Exception('Invalid proxy');
		}

	}



	function show() {

		if (Utils::route(false, '.*', $qs, $args)) {

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


			$auth = new APIAuthenticator();
			$user = $auth->reqToken()->reqScopes(array('userinfo'))->getUser();
			$appscopes = $auth->reqToken()->reqAppScope($this->proxy)->getAppScopes($this->proxy);
			$client = $auth->getClient();

			// header('Content-type: text/plain; charset=utf-8');
			// echo "PRoxy config: "; print_r($proxyconfig);
			// echo "User is "; print_r($user);
			// echo "App scopes "; print_r($appscopes);
			// echo "\nClient"; print_r($client);


			$httpClient = HTTPClient::getClientFromConfig($this->proxy, $client);
			$httpClient->setAuthenticated($user);

			// echo "real url is " . $realurl . "\n";
			// echo "\nClass is " . get_class($httpClient);
			//  exit;


			UWAPLogger::debug('gk', 'Performing authentication API request', array(
				'client' => $client->get('id'),
				'user' => $user->get('userid'),
				'url' => $realurl,
			));

			$headers = array();
			$requestBody = file_get_contents("php://input");
			if (empty($requestBody)) {
				$requestBody = null;
			} else {
				$h = getallheaders();
				if (isset($h['Content-Type'])) {
					$headers['Content-Type'] = $h['Content-Type'];
				} else {
					$headers['Content-Type'] = 'application/json; charset=utf-8';
				}
			}

			// print_r($headers); print_r($requestBody); exit;

			// get($url, $options, $method = 'GET', $data = null) 
			$method = $_SERVER['REQUEST_METHOD'];
			$response = $httpClient->get($method, $realurl, $args, $requestBody, $headers); 

			// echo "Response is "; echo $resposne;

			if ($response->code !== 200) {
				http_response_code($response->code);
			}

			// header('Content-Type: ' . $response->contentType);
			echo $response->body;
			exit;
			// echo json_encode($response);

		} else {
			throw new Exception('Bad request.');
		}

	}

}

