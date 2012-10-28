<?php

/*
 * This is the MAIN API for core for external applications using OAuth and the API to access things.
 * 
 * 		core.uwap.org/api/*
 *
 */

require_once('../../lib/autoload.php');


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: HEAD, GET, OPTIONS, POST, DELETE, PATCH");
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
		} else if (Utils::route(false, '^/oauth/token$', &$parameters)) {
			$oauth->token();
		} else if (Utils::route('get', '^/oauth/info$', &$parameters)) {
			$oauth->info();
		} else {
			throw new Exception('Invalid request');
		}



	} else if  (Utils::route('get', '^/updateme$', &$parameters)) {

		$auth = new AuthBase();
		$auth->authenticate();
		$res = $auth->storeUser();

		header('Content-Type: application/json; chat-set: utf-8');
		echo json_encode(array('status' => 'ok', 'message' => 'updated user data', 'result' => $res));


	/**
	 *  The userinfo endpoint is used for authentication of clients.
	 */
	} else if (Utils::route('get', '^/userinfo$', &$parameters)) {

		$oauth = new OAuth();
		$token = $oauth->check(array(), array('userinfo'));

		// header('Content-Type: application/json; chat-set: utf-8');
		$response['data'] = $token->getUserdataWithGroups();
		// echo json_encode($userdata);


	/**
	 *  The people API
	 */
	} else if (Utils::route(false, '^/people', &$parameters)) {


		$oauth = new OAuth();
		$token = $oauth->check();
		$people = new People($token->getUserID());
		$realm = 'uninett.no';
		if (preg_match('/^.*@(.*?)$/', $token->getUserID(), $matches)) {
			$relam = $matches[1];
		}

		if (Utils::route('get', '^/people/realms$', &$parameters)) {

			$response = array(
				'status' => 'ok',
				'data' => $people->listRealms($realm),
			);

		} else if (Utils::route('get', '^/people/query/([a-z0-9\.]+)$', &$parameters)) {

			// print_r($parameters); exit;

			if ($parameters[1] !== '_') {
				$realm = $parameters[1];
			}

			$response = array(
				'status' => 'ok',
				'data' => $people->query($realm, $_REQUEST['query']),
			);

		} 





	/**
	 *  The groups API is VOOT
	 */
	} else if (Utils::route(false, '^/group[s]?', &$parameters)) {

		$oauth = new OAuth();
		$token = $oauth->check();

		$groupmanager = new GroupManager($token->getUserID());

		$groups = $token->getGroups();

		// Get a list of groups
		if (Utils::route('get', '^/groups$', &$parameters)) {

			$response = array(
				'status' => 'ok',
				'data' => $groupmanager->getGroups($groups),
			);

		// Add a new group
		} else if (Utils::route('post', '^/groups$', &$parameters, &$body)) {

			$res = $groupmanager->addGroup($body);

			$response = array(
				'status' => 'ok',
				'data' => $res,
			);

		// VOOT get a list of groups
		} else if (Utils::route('get', '^/groups/@me$', &$parameters)) {

			$allgroups = $groupmanager->getGroups($groups);
			$no = count($allgroups);
			$response = array(
				"startIndex" => 0,
				"totalResults" => $no,
			    "itemsPerPage" => $no,
			    "entry" => $allgroups
			);

		// Get a specific group
		} else if (Utils::route('get', '^/group/([@:.a-z0-9\-]+)$', &$parameters)) {

			// TODO: ensure user is member of the group to extract memberlist
			$groupid = $parameters[1];
			$response = array(
				'status' => 'ok',
				'data' => $groupmanager->getGroup($groupid),
			);

		// Update some group data...
		} else if (Utils::route('post', '^/group/([@:.a-z0-9\-]+)$', &$parameters, &$body)) {

			// TODO: ensure user is member of the group to extract memberlist
			$groupid = $parameters[1];
			$response = array(
				'status' => 'ok',
				'data' => $groupmanager->updateGroup($groupid, $body),
			);

		// Delete  group
		} else if (Utils::route('delete', '^/group/([@:.a-z0-9\-]+)$', &$parameters)) {

			$groupid = $parameters[1];
			$response = array(
				'status' => 'ok',
				'data' => $groupmanager->removeGroup($groupid),
			);

		// Add a new member to a group
		} else if (Utils::route('post', '^/group/([@:.a-z0-9\-]+)/members$', &$parameters, &$body)) {

			$groupid = $parameters[1];
			$response = array(
				'status' => 'ok',
				'data' => $groupmanager->addMember($groupid, $body),
			);

		// Update a membership to a group
		} else if (Utils::route('post', '^/group/([@:.a-z0-9\-]+)/member/([@:.a-z0-9\-]+)$', &$parameters, &$obj)) {

			$groupid = $parameters[1];
			$userid = $parameters[2];
			$response = array(
				'status' => 'ok',
				'data' => $groupmanager->updateMember($groupid, $userid, $obj),
			);

		// Remove a user from a group
		} else if (Utils::route('delete', '^/group/([@:.a-z0-9\-]+)/member/([@:.a-z0-9\-]+)$', &$parameters)) {

			$groupid = $parameters[1];
			$userid = $parameters[2];
			$response = array(
				'status' => 'ok',
				'data' => $groupmanager->removeMember($groupid, $userid),
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
			if (preg_match('/^app_(.*?)$/', $token->getClientID(), $matches)) {
				$targetapp = $matches[1];
			} else {
				throw new Exception('You MUST provide the [appid] parameter to tell which application target storage to use.');
			}
		}

		// echo "TARGET APP IS " . $targetapp . " and clienti_id was " . $token->getClientID();

		$token = $oauth->check(null, array('app_' . $targetapp . '_user'));
		$userid = $token->getUserID();
		$groups = $token->getGroups();

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
	 *  The feed API.
	 */
	} else if (Utils::route(false, '^/feed', &$qs, &$parameters)) {

		$oauth = new OAuth();
		$token = $oauth->check(null, array('feedread'));

		if ($token->isUser()) {
			$clientid = null;
			$userid = $token->getUserID();
			$groups = $token->getGroups();
		} else {
			$clientid = $token->getClientID();
			$userid = null;
			$groups = $token->getClientGroups();
		}

		// echo 'groups: '; print_r($groups); exit;

		$feed = new Feed($userid, $clientid, $groups);

		if (Utils::route('post', '^/feed$', &$qs, &$parameters)) {
			
			$response['data'] = $feed->read($parameters);

		} else if (Utils::route('post', '^/feed/post$', &$qs, &$args)) {

			$oauth->check(null, array('feedwrite'));

			if (empty($args['msg'])) throw new Exception("missing required [msg] property");
			$msg = $args['msg'];

			$groups = array();
			if (!empty($msg['groups'])) $groups = $msg['groups']; unset($msg['groups']);
			$response['data'] = $feed->post($msg, $groups);

		} else if (Utils::route('delete', '^/feed/item/([a-z0-9\-]+)$', &$qs, &$args)) {

			$oauth->check(null, array('feedwrite'));

			// echo "About to delete an item: " . $qs[1];
			
			$response['data'] = $feed->delete($qs[1]);


		} else {

			throw new Exception('Invalid request');

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
			$userid = $token->getUserID();
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



