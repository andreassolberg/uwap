<?php

/*
 * This is the MAIN API for core for external applications using OAuth and the API to access things.
 * 
 * 		core.uwap.org/api/*
 *
 */

require_once('../../lib/autoload.php');

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

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

		} else if (Utils::route('get', '^/groups/public$', &$parameters)) {

			$gres = $groupmanager->getPublicGroups($groups);

			$response = array(
				'status' => 'ok',
				'data' => $gres,
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

		} else if (Utils::route('post', '^/group/([@:.a-z0-9\-]+)/subscription$', &$parameters, &$body)) {

			$groupid = $parameters[1];

			if ($body === true) {
				$res = $groupmanager->subscribe($groupid, $body);
			} else {
				$res = $groupmanager->unsubscribe($groupid, $body);
			}

			$response = array(
				'status' => 'ok',
				'data' => $res,
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
	 *  The appconfig API.
	 */
	} else if (Utils::route(false, '^/appconfig/', &$qs, &$parameters)) {


		$oauth = new OAuth();
		$token = $oauth->check(null, array('appconfig'));
		$userid = $token->getUserID();
		$appdirectory = new AppDirectory();




		if (Utils::route('get', '^/appconfig/apps$', &$qs, &$parameters)) {

			$listing = $appdirectory->getMyApps($userid);			
			$response['data'] = $listing;


		} else if (Utils::route('post', '^/appconfig/apps$', &$qs, &$parameters)) {

			
			$object = $parameters;
			$id = $object["id"];
			Utils::validateID($id);
			// $config = Config::getInstance();
			// $config->store($object, $userid);
			// Config::store($object, $userid);

			$appdirectory->store($object, $userid);

			$ac = Config::getInstance($id);
			$response['data'] = $ac->getConfig();


		} else if (Utils::route('get', '^/appconfig/app/([a-z0-9\-]+)/status$', &$qs, &$parameters)) {

			$appid = $qs[1];
			Utils::validateID($appid);
			$ac = Config::getInstance($appid);
			$c = $ac->getConfig();

			$response['data'] = $c['status'];

		} else if (Utils::route('post', '^/appconfig/app/([a-z0-9\-]+)/status$', &$qs, &$parameters)) {

			$appid = $qs[1];
			$object = $parameters;

			Utils::validateID($appid);

			$ac = Config::getInstance($appid);
			$ac->updateStatus($object, $userid);

			$c = $ac->getConfig();

			$response['data'] = $c['status'];


		} else if (Utils::route('post', '^/appconfig/app/([a-z0-9\-]+)/davcredentials$', &$qs, &$parameters)) {

			$appid = $qs[1];
			Utils::validateID($appid);
			$ac = Config::getInstance($appid);

			$response['data'] = $ac->getDavCredentials($userid);


		} else if (Utils::route('post', '^/appconfig/app/([a-z0-9\-]+)/bootstrap$', &$qs, &$parameters)) {

			$appid = $qs[1];
			$object = $parameters;

			if (!is_string($object) || empty($object)) {
				throw new Exception('Invalid template input to bootstrap application data');
			}
			if (!in_array($object, array('twitter', 'boilerplate'))) {
				throw new Exception('Not valid template to bootstrap application data');	
			}
			
			Utils::validateID($appid);
			$ac = Config::getInstance($appid);
			$response['data'] = $ac->bootstrap($object);



		} else if (Utils::route('post', '^/appconfig/app/([a-z0-9\-]+)/authorizationhandler/([a-z0-9\-]+)$', &$qs, &$parameters)) {

			$appid = $qs[1];
			$authzid = $qs[2];
			$object = $parameters;

			Utils::validateID($appid);
			$ac = Config::getInstance($appid);
			
			Utils::validateID($authzid);

			$handlers = $ac->updateAuthzHandler($authzid, $object, $userid);
			$response['data'] = $handlers;


		} else if (Utils::route('delete', '^/appconfig/app/([a-z0-9\-]+)/authorizationhandler/([a-z0-9\-]+)$', &$qs, &$parameters)) {

			$appid = $qs[1];
			$authzid = $qs[2];

			Utils::validateID($appid);
			$ac = Config::getInstance($appid);
			
			Utils::validateID($authzid);

			$res = $ac->deleteAuthzHandler($authzid, $userid);
			$response['data'] = $res;




		} else if (Utils::route('get', '^/appconfig/check/([a-z0-9\-]+)$', &$qs, &$parameters)) {

			$appid = $qs[1];
			Utils::validateID($appid);
			$response['data'] = !$appdirectory->exists($appid);

		} else if (Utils::route('get', '^/appconfig/app/([a-z0-9\-]+)$', &$qs, &$parameters)) {

			$appid = $qs[1];
			Utils::validateID($appid);
			$ac = Config::getInstance($appid);

			$response['data'] = $ac->getConfig();
			$response['data']['davcredentials'] = $ac->getDavCredentials($userid);
			$response['data']['appdata-stats'] = $ac->getStats();
			$response['data']['files-stats'] = $ac->getFilestats();
			$response['data']['user-stats'] = $ac->getUserStats();

		} else {
			throw new Exception('Invalid request');
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

		$subscriptions = $token->getSubscriptions();

		// echo 'groups: '; print_r($groups); exit;

		$feed = new Feed($userid, $clientid, $groups, $subscriptions);

		if (Utils::route('post', '^/feed$', &$qs, &$parameters)) {
			
			$response['data'] = $feed->read($parameters);

		} else if (Utils::route('post', '^/feed/upcoming$', &$qs, &$parameters)) {

			// $parameters;
			$no = new Upcoming($userid, $groups, $subscriptions);
			$response['data'] = $no->read($parameters);

		} else if (Utils::route('post', '^/feed/notifications$', &$qs, &$parameters)) {

			// $parameters;
			$no = new Notifications($userid, $groups, $subscriptions);
			$response['data'] = $no->read($parameters);

			// header('Content-Type: text/plain; charset: utf-8'); echo "poot"; print_r($response); exit;

		} else if (Utils::route('post', '^/feed/notifications/markread$', &$qs, &$ids)) {


			$no = new Notifications($userid, $groups, $subscriptions);
			$response['data'] = $no->markread($ids);


		} else if (Utils::route('post', '^/feed/post$', &$qs, &$args)) {

			$oauth->check(null, array('feedwrite'));

			if (empty($args['msg'])) throw new Exception("missing required [msg] property");
			$msg = $args['msg'];

			$groups = array();
			if (!empty($msg['groups'])) $groups = $msg['groups']; unset($msg['groups']);

			error_log("About to post groups: " . json_encode($msg));
			error_log("About to post groups: " . json_encode($groups));

			$response['data'] = $feed->post($msg, $groups);

		} else if (Utils::route('delete', '^/feed/item/([a-z0-9\-]+)$', &$qs, &$args)) {

			$oauth->check(null, array('feedwrite'));

			// echo "About to delete an item: " . $qs[1];
			
			$response['data'] = $feed->delete($qs[1]);

		} else if (Utils::route('post', '^/feed/item/([a-z0-9\-]+)/respond$', &$qs, &$args)) {

			if (empty($args['msg'])) throw new Exception("missing required [msg] property");
			$msg = $args['msg'];
			if ($qs[1] !== $msg['inresponseto']) {
				throw new Exception('inresponseto property does not match url endpoint item.');
			}

			$response['data'] = $feed->respond($msg);


		} else if (Utils::route('get', '^/feed/item/([a-z0-9\-]+)$', &$qs, &$args)) {

			// $oauth->check(null, array('feedwrite'));
			// echo "About to delete an item: " . $qs[1];
			
			$response['data'] = $feed->readItem($qs[1]);

		} else {

			throw new Exception('Invalid request');

		}

	/**
	 *  The SOA Proxy REST data API.
	 */
	} else if (Utils::route('post', '^/soa$', &$qs, &$args)) {


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

		$useoauth = false;
		if (isset($args['oauth']) && $args['oauth']) { $useoauth = true; }

		/*
		 * Try to figure out on behalf of which app to perform the request.
		 * This will be used to lookup HTTP REST handler configurations.
		 */
		$targetapp = $args['appid'];

		if (!empty($args["handler"])) $handler = $args["handler"];

		// echo "Oauth " . var_export($useoauth, true);
		// echo "handler " . var_export($handler, true);

		// Initiate an Oauth server handler
		$oauth = new OAuth();
		$token = $oauth->getProvidedToken(false);
		// echo '<pre>';  print_r($token);

		$client = HTTPClient::getClient($handler, $targetapp);

		if (($token !== null) || ($handler !== 'plain')) {

			// Get provided Token on this request, if present.
			

			$oauth->check(null, array('app_' . $targetapp . '_user'));
			$userid = $token->getUserID();
			$client->setAuthenticated($userid);
		}

		// echo '<pre>'; 
		// print_r($args);
		// print_r($client);
		// echo '---- o ---- o ---- o ---- o ---- o ---- o ---- ';

		$response = $client->get($url, $args);


	// } else if (Utils::route('post', '^/apps$', &$parameters, &$object)) {

	// 	$id = $object["id"];
	// 	Utils::validateID($id);
	// 	$config->store($object, $auth->getRealUserID());

	// 	$ac = Config::getInstance($id);
	// 	$response['data'] = $ac->getConfig();



	/**
	 *  Media files
	 */
	} else if (Utils::route('get', '^/media/user/([a-z0-9\-]+)$', &$qs, &$args)) {

		$default = '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';
		try {

			$auth = new AuthBase();
			$user = $auth->getUser($qs[1]);

			if (empty($user)) {echo 'Not found'; exit;}

			if (!empty($user["photo"])) {
				header('Content-Type: image/jpeg');
				echo base64_decode($user["photo"]);
			} else {
				header('Content-Type: image/png');
				echo base64_decode($default);
			}


		} catch(Exception $error) {


		}
		exit;


	/**
	 *  Media files
	 */
	} else if (Utils::route('get', '^/media/logo/app/([a-z0-9\-]+)$', &$qs, &$args)) {


		$default = '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';


		$c = Config::getInstance($qs[1]);
		$ac = $c->getConfig();
		
		if (!empty($ac["logo"])) {

			header('Content-Type: image/png');
			echo base64_decode($ac["logo"]);
		} else {

			header('Content-Type: image/png');
			echo base64_decode($default);
		}
		exit;


	/**
	 *  Media files
	 */
	} else if (Utils::route('get', '^/media/logo/client/([a-z0-9\-]+)$', &$qs, &$args)) {


		$store = new So_StorageServerUWAP();
		$ac = $store->getClient($qs[1]);

		if (!empty($ac["logo"])) {

			header('Content-Type: image/png');
			echo base64_decode($ac["logo"]);
		} else {

			header('Content-Type: image/png');
			echo base64_decode($default);
		}

		exit;


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



