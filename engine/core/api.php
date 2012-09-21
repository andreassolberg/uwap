<?php

/*
 * This is the MAIN API for core for external applications using OAuth and the API to access things.
 * 
 * 		core.uwap.org/api/*
 *
 */

require_once('../../lib/autoload.php');

// UWAPLogger::info('auth', 'User is accessing core authentication endpint core.uwap.org/login .');

// if (empty($_REQUEST['app'])) {
// 	UWAPLogger::error('auth', "missing required query string parameter: app");	
// 	throw new Exception("missing required query string parameter: app");
// }
// $auth = new Auth($app);


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: HEAD, GET, OPTIONS, POST, DELETE");
header("Access-Control-Allow-Headers: Authorization, X-Requested-With, Origin, Accept, Content-Type");

try {

	if (Utils::route('options', '.*', &$parameters)) {
		header('Content-Type: application/json; charset=utf-8');
		exit;
	}

	$response = array(
		"status" => "ok"
	);

	/**
	 *  The OAuth endpoints on core, typically the OAuth Server endpoints for communication with clients
	 *  using the API.
	 */
	if (Utils::route(false, '^/oauth', &$parameters)) {


		$oauth = new OAuth();

		if (Utils::route('post','^/oauth/authorization$', &$parameters)) {
			$oauth->processAuthorizationResponse();
		} else if (Utils::route('get', '^/oauth/authorization$', &$parameters)) {
			$oauth->authorization();
		} else if (Utils::route('get', '^/oauth/token$', &$parameters)) {
			$oauth->token();
		} else if (Utils::route('get', '^/oauth/info$', &$parameters)) {
			$oauth->info();
		} else {
			throw new Exception('Invalid request');
		}




	/**
	 *  The userinfo endpoint is used for authentication of clients.
	 */
	} else if (Utils::route('get', '^/userinfo$', &$parameters)) {

		$oauth = new OAuth();
		$token = $oauth->check(array('user'));

		// header('Content-Type: application/json; chat-set: utf-8');
		$response['data'] = $token->userdata;
		// echo json_encode($userdata);



	/**
	 *  The groups API is VOOT
	 */
	} else if (Utils::route('get', '^/groups', &$parameters)) {

		$oauth = new OAuth();
		$token = $oauth->check(null, array('voot'));


		$groups = $token->userdata['groups'];
		$g = array();
		foreach($groups AS $key => $group) {
			$g[] = array('id' => $key, 'name' => $group);
		}

		if (Utils::route('get', '^/groups/@me$', &$parameters)) {

			$no = count($g);
			$response = array(
				"startIndex" => 0,
				"totalResults" => $no,
			    "itemsPerPage" => $no,
			    "entry" => $g
			);

		} else {
			echo "Invalid request"; exit;
		}


	/**
	 *  The storage API.
	 */
	} else if (Utils::route('post', '^/store$', &$qs, &$parameters)) {

		$oauth = new OAuth();

		$token = $oauth->getProvidedToken();

		if (!empty($parameters['appid'])) {
			$targetapp = $parameters['appid'];	
		} else {
			if (preg_match('/^app_(.*?)$/', $token->client_id, $matches)) {
				$targetapp = $matches[1];
			} else {
				throw new Exception('You MUST provide the [appid] parameter to tell which application target storage to use.');
			}
		}

		// echo "TARGET APP IS " . $targetapp . " and clienti_id was " . $token->client_id;

		$token = $oauth->check(null, array('app_' . $targetapp . '_user'));
		$userid = $token->userdata['userid'];
		$groups = $token->userdata['groups'];

		// print_r($token); exit;

		$store = new UWAPStore();

		if (empty($parameters['op'])) throw new Exception("Missing required parameter [op] operation");

		switch($parameters['op']) {

			case 'remove':
				if (empty($parameters['object'])) throw new Exception("Missing required parameter [object] object to save");
				$store->remove("appdata-" . $targetapp, $userid, $parameters['object']);
				break;

			case 'save':
				if (empty($parameters['object'])) throw new Exception("Missing required parameter [object] object to save");
				$store->store("appdata-" . $targetapp, $userid, $parameters['object']);
				break;

				// TODO: Clean output before returning. In example remove uwap- namespace attributes...
			case 'queryOne':
				if (empty($parameters['query'])) throw new Exception("Missing required parameter [query] query");
				$response['data'] = $store->queryOneUser("appdata-" . $targetapp, $userid, $groups, $parameters['query']);
				break;

			case 'queryList':
				if (empty($parameters['query'])) throw new Exception("Missing required parameter [query] query");
				$response['data'] = $store->queryListUser("appdata-" . $targetapp, $userid, $groups, $parameters['query']);
				break;

		}



	/**
	 *  The REST data API.
	 */
	} else if (Utils::route('post', '^/rest$', &$qs, &$args)) {


		if (empty($args['url'])) {
			throw new Exception("Missing parameter [url]");
		}

		if (empty($args['appid'])) {
			throw new Exception("Missing parameter [appid]");
		}

		$url = $args["url"];
		$handler = "plain";

		/*
		 * Try to figure out on behalf of which app to perform the request.
		 * This will be used to lookup HTTP REST handler configurations.
		 */
		$targetapp = $args['appid'];

		if (!empty($args["handler"])) $handler = $args["handler"];



		// Initiate an Oauth server handler
		$oauth = new OAuth();

		// Get provided Token on this request, if present.
		$token = $oauth->getProvidedToken();

		$client = HTTPClient::getClient($handler, $targetapp);

		if ($token) {
			$oauth->check(null, array('app_' . $targetapp . '_user'));
			$userid = $token->userdata['userid'];
			$client->setAuthenticated($userid);
		}

		$response = $client->get($url, $args);


	} else if (Utils::route('post', '^/apps$', &$parameters, &$object)) {

		$id = $object["id"];
		Utils::validateID($id);
		$config->store($object, $auth->getRealUserID());

		$ac = Config::getInstance($id);
		$response['data'] = $ac->getConfig();

	} else {

		throw new Exception('Invalid request');
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($response);

} catch(Exception $e) {

	// TODO: Catch OAuth token expiration etc.! return correct error code.

	header("Status: 500 Internal Error");
	header('Content-Type: text/plain; charset: utf-8');
	echo "Error stack trace: \n";
	print_r($e);


}



