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
header("Access-Control-Expose-Headers: Authorization, X-Requested-With, Origin, Accept, Content-Type");




$profiling = microtime(true);
error_log("Time START    :     ======> " . (microtime(true) - $profiling));

try {

	$globalconfig = GlobalConfig::getInstance();

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




	/*
	 *	Testing authentication using the auth libs
	 *	Both API auth and 
	 */
	} else if  (Utils::route('get', '^/test$', &$parameters)) {

		$auth = new Authenticator();
		$auth->req(false, true); // require($isPassive = false, $allowRedirect = false, $return = null

		$user = $auth->getUser();

		// $res = $auth->storeUser();

		// header('Content-Type: application/json; chat-set: utf-8');
		// echo 

		$response['data'] = array('status' => 'ok', 'message' => 'TESTING');









	} else if  (Utils::route('get', '^/updateme$', &$parameters)) {



		$auth = new Authenticator();
		$auth->req(false, true); // require($isPassive = false, $allowRedirect = false, $return = null

		$user = $auth->getUser();

		// $res = $auth->storeUser();

		// header('Content-Type: application/json; chat-set: utf-8');
		// echo 

		$response['data'] = array('status' => 'ok', 'message' => 'updated user data', 'userdata' => $user->getJSON());


	/**
	 *  The userinfo endpoint is used for authentication of clients.
	 */
	} else if (Utils::route('get', '^/userinfo$', &$parameters)) {

		$oauth = new OAuth();
		$token = $oauth->check(array(), array('userinfo'));

		$user = $token->getUser();


		$response['data'] = $user->getJSON(array('type' => 'extended',  'groups' => true, 'subscriptions' => true));


	/**
	 *  The people API
	 */
	} else if (Utils::route(false, '^/people', &$parameters)) {



		$oauth = new OAuth();
		$token = $oauth->check();
		$user = $token->getUser();


		$groupconnector = new GroupConnector($user);

		// $people = new People($token->getUserID());
		// $realm = 'uninett.no';
		// if (preg_match('/^.*@(.*?)$/', $token->getUserID(), $matches)) {
		// 	$relam = $matches[1];
		// }

		if (Utils::route('get', '^/people/realms$', &$parameters)) {

			$response = array(
				'status' => 'ok',
				'data' => $groupconnector->peopleListRealms(),
			);

		} else if (Utils::route('get', '^/people/query/([a-z0-9\.\-]+)$', &$parameters)) {

			// print_r($parameters); exit;

			// $realm = null;
			// if ($parameters[1] !== '_') {
			// 	$realm = $parameters[1];
			// }

			$realm = $parameters[1];
			$response = array(
				'status' => 'ok',
				'data' => $groupconnector->peopleQuery($realm, $_REQUEST['query']),
			);

		} 


	/**
	 *  The groups API is VOOT
	 */
	} else if (Utils::route(false, '^/group[s]?', &$parameters)) {

		$oauth = new OAuth();
		$token = $oauth->check();
		$user = $token->getUser();


		$groupconnector = new GroupConnector($user);

		$userdata = $user->getJSON(array(
			'type' => 'basic',
			'groups' => array('type' => 'key')
		));


		// Get a list of groups
		if (Utils::route('get', '^/groups$', &$parameters)) {

			$response = array(
				'status' => 'ok',
				'data' => $groupconnector->getGroupsJSON(),
			);

		} else if (Utils::route('get', '^/groups/public$', &$parameters)) {

			// throw new NotImplementedException('Have to refactor and implement search for public groups.');

			// $gres = $groupmanager->getPublicGroups($groups);

			$response = array(
				'status' => 'ok',
				'data' => $groupconnector->getPublicGroupsJSON(),
			);


		// Add a new group
		} else if (Utils::route('post', '^/groups$', &$parameters, &$body)) {

			// echo "About to create new group with "; print_r($body); exit;

			$res = $groupconnector->addGroup($body);

			$response = array(
				'status' => 'ok',
				'data' => $res->getJSON(),
			);


		// Get a specific group
		} else if (Utils::route('get', '^/group/([@:.a-zA-Z0-9\-]+)$', &$parameters)) {

			// throw new NotImplementedException('Have to refactor and implement search for public groups.');

			// TODO: ensure user is member of the group to extract memberlist
			$groupid = $parameters[1];
			$group = $groupconnector->getByID($groupid);

			if (empty($group)) {
				throw new Exception('Group not found: ' . $groupid);
			}


			$response = array(
				'status' => 'ok',
				'data' => $group->getJSON(),
			);


		// Get a specific group
		} else if (Utils::route('get', '^/group/([@:.a-zA-Z0-9\-]+)/members$', &$parameters)) {

			// throw new NotImplementedException('Have to refactor and implement search for public groups.');

			// TODO: ensure user is member of the group to extract memberlist
			$groupid = $parameters[1];
			$group = $groupconnector->getByID($groupid);

			if (empty($group)) {
				throw new Exception('Group not found: ' . $groupid);
			}

			$members = $group->getMembers();

			$response = array(
				'status' => 'ok',
				'data' => $members->getJSON(array('type' => 'user')),
			);

			// if ($group === null) {
			// 	$response = array(
			// 		'status' => 'ok',
			// 		'data' => null,
			// 	);
			// } else {
			// 	$response = array(
			// 		'status' => 'ok',
			// 		'data' => $group->getJSON(),
			// 	);
			// }


		// Update some group data...
		} else if (Utils::route('post', '^/group/([@:.a-z0-9\-]+)$', &$parameters, &$body)) {


			// throw new NotImplementedException('Have to refactor and implement search for public groups.');

			// TODO: ensure user is member of the group to extract memberlist
			$groupid = $parameters[1];
			$response = array(
				'status' => 'ok',
				'data' => $groupconnector->update($groupid, $body),
			);

		// Delete  group
		} else if (Utils::route('delete', '^/group/([@:.a-z0-9\-]+)$', &$parameters)) {



			$groupid = $parameters[1];
			$response = array(
				'status' => 'ok',
				'data' => $groupconnector->remove($groupid),
			);



		} else if (Utils::route('post', '^/group/([@:.a-z0-9\-]+)/subscription$', &$parameters, &$body)) {


			$groupid = $parameters[1];

			if (!isset($body['subscribe'])) throw new Exception('Missing property subscribe');

			if ($body['subscribe'] === true) {
				$res = $groupconnector->subscribe($groupid, $body['subscribe']);
			} else {
				$res = $groupconnector->unsubscribe($groupid, $body['subscribe']);
			}

			$response = array(
				'status' => 'ok',
				'data' => $res,
			);


		// Add a new member to a group
		} else if (Utils::route('post', '^/group/([@:.a-z0-9\-]+)/members$', &$parameters, &$body)) {

			$groupid = $parameters[1];
			$response = array(
				'status' => 'ok',
				'data' => $groupconnector->addMember($groupid, $body),
			);

		// Update a membership to a group
		} else if (Utils::route('post', '^/group/([@:.a-z0-9\-]+)/member/([@:.a-z0-9\-]+)$', &$parameters, &$obj)) {

			// throw new NotImplementedException('Have to refactor and implement search for public groups.');

			$groupid = $parameters[1];
			$userid = $parameters[2];
			$response = array(
				'status' => 'ok',
				'data' => $groupconnector->updateMember($groupid, $userid, $obj),
			);

		// Remove a user from a group
		} else if (Utils::route('delete', '^/group/([@:.a-z0-9\-]+)/member/([@:.a-z0-9\-]+)$', &$parameters)) {

			// throw new NotImplementedException('Have to refactor and implement search for public groups.');

			$groupid = $parameters[1];
			$userid = $parameters[2];
			$response = array(
				'status' => 'ok',
				'data' => $groupconnector->removeMember($groupid, $userid),
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

		$user = $token->getUser();
		$userid = $user->get('userid');
		// $groups = $user->getGroups();

		
		$userdata = $user->getJSON(array(
			'type' => 'basic',
			'groups' => array('type' => 'key')
		));


		// echo '<pre>';
		// echo $targetapp; 
		// print_r($userdata);
		// exit;

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
				$response['data'] = $store->queryOneUser("appdata-" . $targetapp, $userid, $userdata['groups'], $parameters['query']);
				break;

			case 'queryList':
				if (empty($parameters['query'])) throw new Exception("Missing required parameter [query] query");
				$response['data'] = $store->queryListUser("appdata-" . $targetapp, $userid, $userdata['groups'], $parameters['query']);
				break;

		}







	/**
	 *  The appconfig API.
	 */
	} else if (Utils::route(false, '^/appconfig/', &$qs, &$parameters)) {


		$oauth = new OAuth();
		$token = $oauth->check(null, array('appconfig'));
		$user = $token->getUser();
		$clientdirectory = new ClientDirectory($user);



		/*
		 * APIs that allows querying a list of appconfig related objects
		 */

		if (Utils::route('get', '^/appconfig/clients$', &$qs, &$parameters)) {

			// echo "GET APPCONFIG/Clients";

			$clients = $clientdirectory->getClients(array(
				'mine' => true,
				'status-' => 'pendingDelete'
			));
			$response['data'] = $clients->getJSON();



		/*
		 * APIs that allows retrieval of single entries
		 */

		} else if (Utils::route('get', '^/appconfig/client/([a-z0-9\-]+)$', &$qs, &$parameters)) {


			$appid = $qs[1];
			Utils::validateID($appid);

			$client = Client::getByID($appid);
			// echo "obtained client by id " . $appid;
			// echo "result was "; print_r(var_export($client, true));

			$clientdirectory->authorize($client, 'owner');
			$response['data'] = $client->getJSON(array(
				'appinfo' => true
			));

			if ($client instanceof App) {

				$apphosting = new AppHosting($user);
				$response['data']['davcredentials'] = $apphosting->getDavCredentials($client);
				/*
				 * Deprecated properties that was previously present in the API result
				 *
				 * appdata-stats
				 * files-stats
				 * user-stats
				 */

			}


		} else if (Utils::route('get', '^/appconfig/client/([a-z0-9\-]+)/status$', &$qs, &$parameters)) {

			$appid = $qs[1];
			Utils::validateID($appid);
			$client = Client::getByID($appid);

			$clientdirectory->authorize($client, 'owner');

			$clientdata = $client->getJSON();
			$response['data'] = $clientdata['status'];


		} else if (Utils::route('get', '^/appconfig/check/([a-z0-9\-]+)$', &$qs, &$parameters)) {

			$appid = $qs[1];
			Utils::validateID($appid);
			$response['data'] = !$clientdirectory->exists($appid);




		/*
		 * APIs that allows modifications of appconfig related items.
		 */

		} else if (Utils::route('post', '^/appconfig/clients$', &$parameters, &$object)) {
			

			$client = Client::generate($object, $user);
			$response['data'] = $client->getJSON();

			// echo "OK. Done";

		} else if (Utils::route('post', '^/appconfig/client/([a-z0-9\-]+)/status$', &$parameters, &$bodyobject)) {

			$appid = $parameters[1];
			Utils::validateID($appid);
			$client = Client::getByID($appid);

			$clientdirectory->authorize($client, 'owner');

			$client->updateStatus($bodyobject);

			$clientdata = $client->getJSON();
			$response['data'] = $clientdata['status'];


		} else if (Utils::route('post', '^/appconfig/client/([a-z0-9\-]+)/bootstrap$', &$parameters, &$object)) {

			$appid = $parameters[1];
			Utils::validateID($appid);
			$client = App::getByID($appid);
			$clientdirectory->authorize($client, 'owner');

			$apphosting = new AppHosting($user);
			$response['data'] = $apphosting->bootstrap($client, $object);



			// Update an authorization handler
		} else if (Utils::route('post', '^/appconfig/client/([a-z0-9\-]+)/authorizationhandler/([a-z0-9\-]+)$', &$parameters, &$object)) {


			$appid = $parameters[1];
			Utils::validateID($appid);
			$client = Client::getByID($appid);
			$clientdirectory->authorize($client, 'owner');

			$authzid = $parameters[2];
			Utils::validateID($authzid);

			$response['data'] = $client->updateAuthzHandler($authzid, $object);


			// Delete an authorization handler
		} else if (Utils::route('delete', '^/appconfig/client/([a-z0-9\-]+)/authorizationhandler/([a-z0-9\-]+)$', &$parameters, &$object)) {

			$appid = $parameters[1];
			Utils::validateID($appid);
			$client = Client::getByID($appid);
			$clientdirectory->authorize($client, 'owner');

			$authzid = $parameters[2];
			Utils::validateID($authzid);

			$response['data'] = $client->deleteAuthzHandler($authzid);


		} else if (Utils::route('post', '^/appconfig/client/([a-z0-9\-]+)/proxy$', &$parameters, &$object)) {


			$appid = $parameters[1];
			Utils::validateID($appid);
			$client = Client::getByID($appid);
			$clientdirectory->authorize($client, 'owner');

			$response['data'] = $client->updateProxy($object); 



		} else if (Utils::route('get', '^/appconfig/client/([a-z0-9\-]+)/clients$', &$parameters, &$object)) {


			$appid = $parameters[1];
			Utils::validateID($appid);
			$app = App::getByID($appid);
			$clientdirectory->authorize($app, 'owner');

			$authorizationList = $clientdirectory->getAuthorizationQueue($app);

			$response['data'] = $authorizationList->getJSON();



		} else if (Utils::route('post', '^/appconfig/client/([a-z0-9\-]+)/scopes$', &$parameters, &$object)) {

			$clientid = $parameters[1];
			Utils::validateID($clientid);
			$client = Client::getByID($clientid);
			
			$clientdirectory->authorize($client, 'owner');
			
			$clientdirectory->requestScopes($client, $object);

			$response['data'] = $client->getJSON();




		} else if (Utils::route('post', '^/appconfig/client/([a-z0-9\-]+)/publicapis$', &$parameters, &$object)) {

			$clientid = $parameters[1];
			Utils::validateID($clientid);
			$client = Client::getByID($clientid);
			
			$clientdirectory->authorize($client, 'owner');

			// echo "About to query getpublic api "; print_r($object); exit;

			$authorizationList = $clientdirectory->getPublicAPIs($client, $object);
			$response['data'] = $authorizationList->getJSON();


		} else if (Utils::route('get', '^/appconfig/client/([a-z0-9\-]+)/authorizedapis$', &$parameters, &$object)) {

			$clientid = $parameters[1];
			Utils::validateID($clientid);
			$client = Client::getByID($clientid);
			
			$clientdirectory->authorize($client, 'owner');

			$authorizationList = $clientdirectory->getAuthorizedAPIs($client, null);
			$response['data'] = $authorizationList->getJSON();




		} else if (Utils::route('post', '^/appconfig/client/([a-z0-9\-]+)/client/([a-z0-9\-]+)/authorization$', &$parameters, &$object)) {

			$appid = $parameters[1];
			Utils::validateID($appid);
			$app = Client::getByID($appid);
			
			$clientdirectory->authorize($app, 'owner');

			$clientid = $parameters[2];
			Utils::validateID($clientid);
			$client = Client::getByID($clientid);

			$clientdirectory->authorizeClientScopes($app, $client, $object);

			$authorizationList = $clientdirectory->getAuthorizationQueue($app);
			$response['data'] = $authorizationList->getJSON();



		// } else if (Utils::route('post', '^/appconfig/client/([a-z0-9\-]+)/proxy/scopes$', &$parameters, &$object)) {

		// 	$appid = $parameters[1];
		// 	Utils::validateID($appid);
		// 	$client = APIProxy::getByID($appid);
		// 	$clientdirectory->authorize($client, 'owner');


		// 	$response['data'] = $client->addProxyScopes($object);


		// } else if (Utils::route('delete', '^/appconfig/client/([a-z0-9\-]+)/proxy/scopes/([a-z0-9\-]+)$', &$qs, &$parameters)) {

		// 	$appid = $qs[1];
		// 	Utils::validateID($appid);
		// 	$client = $appdirectory->getClient($appid);

		// 	$response['data'] = $appdirectory->removeClientScopes($appid, $object);





		} else {
			throw new Exception('Not implemented');
		}






		



		if (true)  {

		// } else if (Utils::route('get', '^/appconfig/apps$', &$qs, &$parameters)) {

		// 	$listing = $appdirectory->getMyApps();			
		// 	$response['data'] = $listing;

		} else if (Utils::route('post', '^/appconfig/apps/query$', &$qs, &$parameters)) {

			$listing = $appdirectory->queryApps($parameters );
			$response['data'] = $listing;


		// } else if (Utils::route('post', '^/appconfig/clients$', &$qs, &$parameters)) {

		// 	$object = $parameters;
			
		// 	if (empty($object['client_id'])) {
		// 		$object['client_id'] = Utils::genID();
		// 	}
			
		// 	$object['client_secret'] = Utils::genID();
		// 	Utils::validateID($object['client_id']);

		// 	// $config = Config::getInstance();
		// 	// $config->store($object, $userid);
		// 	// Config::store($object, $userid);

		// 	$appdirectory->storeClient($object);
		// 	$response['data'] = $appdirectory->getClient($object['client_id']);


			// $ac = Config::getInstance($id);
			// $response['data'] = $ac->getConfig();


		// } else if (Utils::route('post', '^/appconfig/apps$', &$qs, &$parameters)) {

		// 	$object = $parameters;
		// 	$id = $object["id"];
		// 	Utils::validateID($id);

		// 	// $config = Config::getInstance();
		// 	// $config->store($object, $userid);
		// 	// Config::store($object, $userid);

		// 	$appdirectory->store($object);

		// 	$ac = Config::getInstance($id);
		// 	$response['data'] = $ac->getConfig();


		// } else if (Utils::route('get', '^/appconfig/app/([a-z0-9\-]+)/status$', &$qs, &$parameters)) {

		// 	$appid = $qs[1];
		// 	Utils::validateID($appid);
		// 	$ac = Config::getInstance($appid);
		// 	$c = $ac->getConfig();

		// 	$response['data'] = $c['status'];

		// } else if (Utils::route('post', '^/appconfig/app/([a-z0-9\-]+)/status$', &$qs, &$parameters)) {

		// 	$appid = $qs[1];
		// 	Utils::validateID($appid);

		// 	$object = $parameters;

		// 	$ac = Config::getInstance($appid);
		// 	$ac->updateStatus($object);

		// 	$c = $ac->getConfig();

		// 	$response['data'] = $c['status'];

		// } else if (Utils::route('post', '^/appconfig/app/([a-z0-9\-]+)/proxy$', &$qs, &$object)) {

		// 	$appid = $qs[1];
		// 	Utils::validateID($appid);

		// 	$ac = Config::getInstance($appid);
		// 	$ac->updateProxy($object);

		// 	$c = $ac->getConfig();

		// 	$response['data'] = $c['proxy'];

		// } else if (Utils::route('get', '^/appconfig/app/([a-z0-9\-]+)/clients$', &$qs, &$parameters)) {

		// 	$appid = $qs[1];
		// 	Utils::validateID($appid);

		// 	$clients = $appdirectory->getClients($appid);
		// 	$response['data'] = $clients;

		// } else if (Utils::route('post', '^/appconfig/app/([a-z0-9\-]+)/client/([a-z0-9\-]+)/authorize$', &$qs, &$object)) {

		// 	$appid = $qs[1];
		// 	$clientid = $qs[2];
		// 	Utils::validateID($appid);
		// 	Utils::validateID($clientid);

		// 	$a = Config::getInstance($appid);
		// 	$a->requireOwner($user->get('userid'));

		// 	$store = new So_StorageServerUWAP();
		// 	// $ac = $store->getClient($qs[1]);

		// 	$response['data'] = $store->authorizeClient($clientid, $appid, $userid, $object);
			// $response['data'] = $clients;

		// } else if (Utils::route('post', '^/appconfig/app/([a-z0-9\-]+)/davcredentials$', &$qs, &$parameters)) {

		// 	$appid = $qs[1];
		// 	Utils::validateID($appid);
		// 	$ac = Config::getInstance($appid);

		// 	$response['data'] = $ac->getDavCredentials();


		// } else if (Utils::route('post', '^/appconfig/app/([a-z0-9\-]+)/bootstrap$', &$qs, &$parameters)) {

		// 	$appid = $qs[1];
		// 	$object = $parameters;

		// 	if (!is_string($object) || empty($object)) {
		// 		throw new Exception('Invalid template input to bootstrap application data');
		// 	}
		// 	if (!in_array($object, array('twitter', 'boilerplate'))) {
		// 		throw new Exception('Not valid template to bootstrap application data');	
		// 	}
			
		// 	Utils::validateID($appid);
		// 	$ac = Config::getInstance($appid);
		// 	$ac->requireOwner($user->get('userid'));
		// 	$response['data'] = $ac->bootstrap($object);



		// } else if (Utils::route('post', '^/appconfig/app/([a-z0-9\-]+)/authorizationhandler/([a-z0-9\-]+)$', &$qs, &$parameters)) {

		// 	$appid = $qs[1];
		// 	$authzid = $qs[2];
		// 	$object = $parameters;

		// 	Utils::validateID($appid);
		// 	$ac = Config::getInstance($appid);
		// 	$ac->requireOwner($user->get('userid'));
			
		// 	Utils::validateID($authzid);

		// 	$handlers = $ac->updateAuthzHandler($authzid, $object, $user->get('userid'));
		// 	$response['data'] = $handlers;


		// } else if (Utils::route('delete', '^/appconfig/app/([a-z0-9\-]+)/authorizationhandler/([a-z0-9\-]+)$', &$qs, &$parameters)) {

		// 	$appid = $qs[1];
		// 	$authzid = $qs[2];

		// 	Utils::validateID($appid);
		// 	$ac = Config::getInstance($appid);
		// 	$ac->requireOwner($user->get('userid'));
			
		// 	Utils::validateID($authzid);

		// 	$res = $ac->deleteAuthzHandler($authzid, $user->get('userid'));
		// 	$response['data'] = $res;






		} else if (Utils::route('get', '^/appconfig/view/([a-z0-9\-]+)$', &$qs, &$parameters)) {

			$appid = $qs[1];
			Utils::validateID($appid);
			$ac = Config::getInstance($appid);
			$response['data'] = $ac->getConfigLimited();

		// } else if (Utils::route('get', '^/appconfig/app/([a-z0-9\-]+)$', &$qs, &$parameters)) {

		// 	$appid = $qs[1];
		// 	Utils::validateID($appid);
		// 	$ac = Config::getInstance($appid);
		// 	$ac->requireOwner($user->get('userid'));

		// 	$response['data'] = $ac->getConfig();
		// 	$response['data']['user-stats'] = $ac->getUserStats();

		// 	if ($response['data']['type'] === 'app') {
	
		// 		$response['data']['davcredentials'] = $ac->getDavCredentials($user->get('userid'));
		// 		$response['data']['appdata-stats'] = $ac->getStats();
		// 		$response['data']['files-stats'] = $ac->getFilestats();

		// 	}

		// } else if (Utils::route('get', '^/appconfig/client/([a-z0-9\-]+)$', &$qs, &$parameters)) {

		// 	$appid = $qs[1];
		// 	Utils::validateID($appid);
		// 	$response['data'] = $appdirectory->getClient($appid);



			// $ac = Config::getInstance($appid);

		// } else if (Utils::route('post', '^/appconfig/client/([a-z0-9\-]+)/addScopes$', &$qs, &$parameters)) {

		// 	$appid = $qs[1];
		// 	Utils::validateID($appid);
		// 	$client = $appdirectory->getClient($appid);

		// 	$response['data'] = $appdirectory->addClientScopes($appid, $object);


		// } else if (Utils::route('post', '^/appconfig/client/([a-z0-9\-]+)/removeScopes$', &$qs, &$parameters)) {

		// 	$appid = $qs[1];
		// 	Utils::validateID($appid);
		// 	$client = $appdirectory->getClient($appid);

		// 	$response['data'] = $appdirectory->removeClientScopes($appid, $object);

			// $ac = Config::getInstance($appid);

			

			// $ac->requireOwner($userid);
			

		} 








	/**
	 *  The feed API.
	 */
	} else if (Utils::route(false, '^/feed', &$qs, &$parameters)) {

		$oauth = new OAuth();
		$token = $oauth->check(null, array('feedread'));

		$user = $token->getUser();
		$client = $token->getClient();


		$feedReader = new FeedReader($client, $user);


		// Utils::dump('feedReader', $feedReader);


		if (Utils::route('post', '^/feed$', &$parameters, &$object)) {
			
			$response['data'] = $feedReader->read($object)->getJSON();



		} else if (Utils::route('post', '^/feed/upcoming$', &$parameters, &$object)) {

			// $parameters;
			// $no = new Upcoming($userid, $groups, $subscriptions);
			// $response['data'] = $no->read($parameters);


			$response['data'] = $feedReader->readUpcoming($object)->getJSON();

		} else if (Utils::route('post', '^/feed/notifications$',  &$parameters, &$object)) {

			// $parameters;
			// $no = new Notifications($userid, $groups, $subscriptions);
			// $response['data'] = $no->read($parameters);


			$response['data'] = $feedReader->readNotifications($object)->getJSON();

			// header('Content-Type: text/plain; charset: utf-8'); echo "poot"; print_r($response); exit;

		} else if (Utils::route('post', '^/feed/notifications/markread$', &$qs, &$ids)) {


			// $no = new Notifications($userid, $groups, $subscriptions);
			$response['data'] = $feedReader->markNotificationsRead($ids);


		} else if (Utils::route('post', '^/feed/post$', &$parameters, &$object)) {

			$oauth->check(null, array('feedwrite'));

			// if (empty($args['msg'])) throw new Exception("missing required [msg] property");
			// $msg = $args['msg'];

			// $groups = array();
			// if (!empty($msg['groups'])) $groups = $msg['groups']; unset($msg['groups']);

			// error_log("About to post groups: " . json_encode($msg));
			// error_log("About to post groups: " . json_encode($groups));

			$feedItem = $feedReader->post($object);
			$response['data'] = $feedItem->getJSON();



		} else if (Utils::route('delete', '^/feed/item/([a-z0-9\-]+)$',  &$parameters, &$object)) {

			$oauth->check(null, array('feedwrite'));

			// echo "About to delete an item: " . $qs[1];
			// $response['data'] = $feed->delete($qs[1]);
			
			$response['data'] = $feedReader->delete($parameters[1]);


		} else if (Utils::route('post', '^/feed/item/([a-z0-9\-]+)/response$',  &$parameters, &$object)) {

			if ($parameters[1] !== $object['inresponseto']) {
				throw new Exception('inresponseto property does not match url endpoint item.');
			}

			$feedItem = $feedReader->respond($object);;

			$response['data'] = $feedItem->getJSON();




		} else if (Utils::route('get', '^/feed/item/([a-z0-9\-]+)$', &$parameters, &$object)) {

			// $oauth->check(null, array('feedwrite'));
			// echo "About to delete an item: " . $qs[1];
			
			$response['data'] = $feedReader->read(array('id_' => $parameters[1]))->getJSON();

		} else {

			throw new Exception('Invalid request');

		}





	/**
	 *  The SOA Proxy REST data API.
	 */
	} else if (Utils::route('post', '^/soa$', &$qs, &$args)) {


		/**
		 * TODO: Remote the list of proxie apis on each proxy. only one proxy item.
		 */

		if (empty($args['url'])) {
			throw new Exception("Missing parameter [url]");
		}

		if (empty($args['appid'])) {
			throw new Exception("Missing parameter [appid]");
		}

		$url = $args["url"];
		$handler = "plain";

		$remoteHost = parse_url($url, PHP_URL_HOST);
		$proxy = $globalconfig->getApp($remoteHost);
		$proxyID = $proxy->get('id');

		if ($proxy->get('type') !== 'proxy') {
			throw new Exception('This host is not running a soaproxy.');
		}

		$proxyconfig = $proxy->get('proxy');

		$rawpath = parse_url($url, PHP_URL_PATH);
		if (preg_match('|^(/.*)$|i', $rawpath, $matches)) {
			$restpath = $matches[1];

			if (empty($proxyconfig['endpoints'])) {
				throw new Exception('Missing [endpoints] in configuration of this API Proxy.');
			}

			// if (!isset($proxyconfig[$api])) {
			// 	throw new Exception('API Endpoint is not configured...');
			// }

			$realurl = $proxyconfig['endpoints'][0] . $restpath;

			error_log("REAL URL IS " . $realurl);
		}

		

		// error_log("SOA Config " . var_export($args, true));

		// Initiate an Oauth server handler
		$oauth = new OAuth();
		$token = $oauth->check(null, null);
		$user = $token->getUser();
		$client = $token->getClient();



		$httpclient = HTTPClient::getClientFromConfig($proxy, $client);
		if ($token) {
			
			$clientid = $token->getClientID();
			
			$ensureScopes = array('rest_' . $proxyID);
			$oauth->check(null, $ensureScopes);

			$user = $token->getUser();
			$userdata = $user->getJSON(array('type' => 'basic', 'groups' => array('type' => 'key')));

			// header('Content-Type: text/plain'); print_r($userdata); exit;

			// --- TODO

			// $userdata = $token->getUserdataWithGroups();
			$httpclient->setAuthenticated($user);
			$scopes = $oauth->getApplicationScopes('rest', $proxyID);
			// $httpclient->setAuthenticatedClient($clientid, $scopes);
		}
		$response = $httpclient->get($realurl, $args);





	/**
	 *  The REST data API.
	 */
	} else if (Utils::route('post', '^/rest$', &$qs, &$args)) {



		$oauth = new OAuth();

		$client = null;
		$user = null;



		if (empty($args['url'])) {
			throw new Exception("Missing parameter [url]");
		}
		$url = $args["url"];
		$handler = isset($args["handler"]) ? $args["handler"] : "plain";

		if ($handler !== 'plain') {
			$token = $oauth->check();
			$client = $token->getClient();
			$user = $token->getUser();

			// echo 'token:' . "\n"; print_r($token);
		}


		// Get an HTTP Client handler
		$client = HTTPClient::getClient($client, $handler);
		$client->setAuthenticated($user);
		$response = $client->get($url, $args);






	/**
	 *  Media files
	 */
	} else if (Utils::route('get', '^/media/user/([a-z0-9\-]+)$', &$qs, &$args)) {

		$default = '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';
		try {

			// $auth = new Authenticator();
			// $auth->req(false, true); // require($isPassive = false, $allowRedirect = false, $return = null

			$targetUser = User::getByKey('a', $qs[1]);

			// echo "Get by id " . $qs[1]; print_r($targetUser); exit;


			if (empty($targetUser)) {

				header('Content-Type: image/png');
				echo base64_decode($default);

			} else if ($targetUser->has("photo") ) {
				header('Content-Type: image/jpeg');
				echo base64_decode($targetUser->get("photo"));
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
	} else if (Utils::route('get', '^/media/logo/app/([a-z0-9\-]+)$', &$parameters, &$object)) {


		$default = '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';


		$appid = $parameters[1];
		Utils::validateID($appid);

		$client = Client::getByID($appid);

		
		if ($client->has("logo")) {

			header('Content-Type: image/png');
			echo base64_decode($client->get('logo'));
		} else {

			header('Content-Type: image/png');
			echo base64_decode($default);
		}
		exit;


	/**
	 *  Media files
	 */
	// } else if (Utils::route('get', '^/media/logo/client/([a-z0-9\-]+)$', &$qs, &$args)) {


	// 	$store = new So_StorageServerUWAP();
	// 	$ac = $store->getClient($qs[1]);

	// 	if (!empty($ac["logo"])) {

	// 		header('Content-Type: image/png');
	// 		echo base64_decode($ac["logo"]);
	// 	} else {

	// 		header('Content-Type: image/png');
	// 		echo base64_decode($default);
	// 	}

	// 	exit;


	} else {

		throw new Exception('Invalid request');
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($response);

	// $profiling = microtime(true);
	error_log("Time to run command:     ======> " . (microtime(true) - $profiling));

} catch(UWAPObjectNotFoundException $e) {

	header("HTTP/1.0 404 Not Found");
	header('Content-Type: text/plain; charset: utf-8');
	echo "Error stack trace: \n";
	print_r($e);


} catch(Exception $e) {

	// TODO: Catch OAuth token expiration etc.! return correct error code.

	header("HTTP/1.0 500 Internal Server Error");
	header('Content-Type: text/plain; charset: utf-8');
	echo "Error stack trace: \n";
	print_r($e);


}



