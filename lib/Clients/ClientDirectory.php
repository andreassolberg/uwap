<?php

/**
 * ClientDirectory allows you to get information about applications.
 * Used by dev and store.
 */
class ClientDirectory {


	protected $store, $user;
	
	public function __construct($user) {
		$this->store = new UWAPStore();
		$this->user = $user;
	}

	public function get() {



	}




	public function authorize($client, $req) {

		if ($req === 'owner') {

			if ($client->get('uwap-userid') !== $this->user->get('userid')) {
				throw new UWAPNotAuthorizedException('You are not authorized to read this client object');
			}

		} else {
			throw new Exception('Invalid authorization requirement for client provided');
		}

	}

	public function exists($appid) {
		$config = $this->store->queryOne('clients', array("id" => $appid));
		return (!empty($config));
	}


	/**
	 * Get list of clients.
	 */
	public function getClients($opts) {

		$query = array();

		if (isset($opts['mine']) && $opts['mine']) {
			$query['uwap-userid'] =$this->user->get('userid');
		}

		if (isset($opts['status-'])) {
			$query['status'] = array('$ne' => $opts['status-']);
		}

		// collection, $criteria, $fields,  $options
		$res = $this->store->queryList('clients', $query);
		return new ClientSet($res);
	}


	public function getAllApps() {
		$fields = array();
		// 	'id' => true,
		// 	'name' => true,
		// 	'descr' => true,
		// 	'type' => true,
		// 	'owner-userid' => true,
		// 	'owner' => true,
		// 	'name' => true,
		// );
		$query = array(
			"status" => array(
				'$ne' => "pendingDelete"
			),
			"type" => "app",
		);
		$listing = $this->store->queryList('clients', $query, $fields);

		return new ClientSet($listing);
	}




	/**
	 * Get list of all available apis for this client.
	 * 
	 * @param  [type] $appid The client in question that clients requests access to.
	 * @return [type]           [description]
	 */
	public function getAuthorizedAPIs(Client $client, $query = null) {


		$clientScopes = $client->get('scopes', array());
		$clientScopesRequested = $client->get('scopes_requested', array());

		$cscopes = array_merge($clientScopes, $clientScopesRequested);

		$targetApps = array();
		foreach($cscopes AS $scope) {
			if (preg_match('/^rest_([a-z0-9\-]+)(_([a-z0-9\-]+))?/', $scope, $matches)) {
				try {
					if (isset($targetApps[$matches[1]])) continue;
					$targetApps[$matches[1]] = APIProxy::getByID($matches[1]);
				} catch(Exception $e) {
					error_log("Skipping to process nonexisting apiproxy " . $matches[1]);
				}
				
			}
		}

		// header('Content-Type: text/plain'); print_r($targetApps); exit;

		$authorizationListData = array();

		foreach($targetApps AS $app) {

			$authData = array(
				'client' => $client,
				'targetApp' => $app,
			);

			$appscopePrefix = 'rest_' . $app->get('id');

			$authData['scopes'] = self::filterScopeWithPrefix($appscopePrefix, $clientScopes);
			$authData['scopes_requested'] = self::filterScopeWithPrefix($appscopePrefix, $clientScopesRequested);

			$authorizationListData[] = $authData;
		}

		
		// header('Content-Type: text/plain'); 
		// // echo (json_encode($listing)); 
		// print_r($authorizationListData);
		// exit;

		$authorizationqueue = new AuthorizationList($authorizationListData);



		// header('Content-Type: text/plain'); print_r($authorizationqueue); exit;
		return $authorizationqueue;

	}



	/**
	 * Get list of all available apis for this client.
	 * 
	 * @param  [type] $appid The client in question that clients requests access to.
	 * @return [type]           [description]
	 */
	public function getPublicAPIs(Client $client, $q = array()) {



		$clientScopes = $client->get('scopes', array());
		$clientScopesRequested = $client->get('scopes_requested', array());


		$query = array(
			'type' => 'proxy',
			'status' => array(
				'$all' => array('listing', 'operational'),
			),
		);


		if (isset($q['query'])) {

			$regex = '.*' . $q['query'] . '.*';

			$query['$or'] = array(
				array('name' => array('$regex' => $regex, '$options' => 'i')),
				array('descr' => array('$regex' => $regex, '$options' => 'i')),
			);
		}


		$fields = array(
			'id',
			'name',
			'descr',
			'status',
			'proxy',
			'type',
		);


		$options = array(
			'limit' => 100,
		);
		if (isset($q['startsWith'])) {
			$options['startsWith'] = $q['startsWith'];
		}
		if (isset($q['limit'])) {
			$options['limit'] = $q['limit'];
		}


		$listing = $this->store->queryListMeta('clients', $query, $fields, $options);



		$authorizationListData = array();

		foreach($listing['items'] AS $i => $item) {

			$authData = array(
				'client' => $client,
			);

			$authData['targetApp'] = new APIProxy($item);

			$appscopePrefix = 'rest_' . $authData['targetApp']->get('id');

			$authData['scopes'] = self::filterScopeWithPrefix($appscopePrefix, $clientScopes);
			$authData['scopes_requested'] = self::filterScopeWithPrefix($appscopePrefix, $clientScopesRequested);

			$authorizationListData[] = $authData;
		}

		
		// header('Content-Type: text/plain'); 
		// // echo (json_encode($listing)); 
		// print_r($authorizationListData);
		// exit;

		$authorizationqueue = new AuthorizationList($authorizationListData);
		$authorizationqueue->setMeta($listing);
		// header('Content-Type: text/plain'); print_r($authorizationqueue); exit;
		return $authorizationqueue;
	}


	/**
	 * Get list of all clients that have requested authorization for scopes that involves this
	 * specific client.
	 * 
	 * @param  [type] $appid The client in question that clients requests access to.
	 * @return [type]           [description]
	 */
	public function getAuthorizationQueue(App $app) {


		/**
		 * TODO
		 * The queries that pulls scopes that starts with should later be optimalized..
		 * May be add a list of relevant apps in an appconfig of clients, both requested and accepted scope references.
		 */

		// $fields = array(
		// 	'client_id' => true,
		// 	'client_name' => true,
		// 	'scopes' => true,
		// 	'scopes_requested' => true,
		// 	'uwap-userid' => true,
		// );

		$appscope = "rest_" . $app->get('id');
		$appregexmatch = "rest_" . $app->get('id') . "($|[_])";
		$query = array(
			'$or' => array(
				array(
					"scopes" => array('$regex' => $appregexmatch)
				),
				array(
					"scopes_requested" => array('$regex' => $appregexmatch)
				),
			),

		);
		$listing = $this->store->queryList('clients', $query);

		// header('Content-Type: text/plain'); print_r($listing); exit;


		$authorizationListData = array();

		foreach($listing AS $i => $item) {

			$authData = array(
				'targetApp' => $app,
			);

			$authData['client'] = new Client($item);

			if(isset($item['scopes'])) {
				$authData['scopes'] = self::filterScopeWithPrefix($appscope, $item['scopes']);
			}
			if(isset($item['scopes_requested'])) {
				$authData['scopes_requested'] = self::filterScopeWithPrefix($appscope, $item['scopes_requested']);
			}
			$authorizationListData[] = $authData;
		}

		


		$authorizationqueue = new AuthorizationList($authorizationListData);

		// header('Content-Type: text/plain'); print_r($authorizationqueue); exit;
		return $authorizationqueue;


		// $data = array('clients' => $listing);
		// foreach($listing AS $item) {
		// 	if (isset($item['scopes']) && in_array($appscope, $item['scopes'])) {
		// 		$data['clients'][] = $item;
		// 	} else {
		// 		$data['clients-pending'][] = $item;
		// 	}
		// }

		// return $data;

		// $sorted = array("app" => array(), "proxy" => array(), "client" => array());
		// foreach($listing AS $e) {
		// 	if (isset($e['type']) && isset($sorted[$e['type']])) {
		// 		$sorted[$e['type']][] = $e;
		// 	}
		// }
		// return $sorted;
	}



	public function authorizeClientScopes(App $app, Client $client, $scopes) {

		// header('Content-Type: text/plain');
		// echo "<pre>About to authorize client scopes: ";
		// print_r($scopes); print_r($client->getJSON());
		

		foreach($scopes AS $scope => $value) {

			if ($app->controlsScope($scope)) {

				if ($value) {
					// echo "Adding scope [" . $scope . "]";
					$client->setScope($scope, true); // add scope as accepted

				} else {

					$client->setScope($scope, null); // remove scope

				}

			} else {
				// echo ("Ignoring this scope, as app does not control it " . $scope);
				error_log("Ignoring this scope, as app does not control it " . $scope);
			}

				
		}

		$client->store();

		// print_r($scopes); print_r($client->getJSON());
		// exit;

		return $client->getJSON();

	}





	public function requestScopes(Client $client, $scopes) {

		// header('Content-Type: text/plain');
		// echo "Request scopes"; print_r($scopes); exit;


		// $updates = array('scopes' => array(), 'scopes_requested' => array());
		// if (isset($client['scopes'])) $updates['scopes'] = $client['scopes'];
		// if (isset($client['scopes_requested'])) $updates['scopes_requested'] = $client['scopes_requested'];

		foreach($scopes AS $scope => $value) {

			// $value  : true ; request
			// $value  : false; remove

			if (preg_match('/rest_([a-z0-9\-]+)(_([a-z0-9\-]+))?/', $scope, $matches)) {

				$appid = $matches[1];
				Utils::validateID($appid);
				$proxy = APIProxy::getByID($appid);

				$localScope = (isset($matches[2]) ? $matches[2] : null);

				$autoaccept = $proxy->scopePolicyAccept($localScope);

				if ($value) {
					$client->setScope($scope, $autoaccept);
				} else {
					$client->setScope($scope, null);
				}
				

			} else if (preg_match('/app_([a-z0-9\-]+)_user/', $scope, $matches)) {

				// $updates['scopes_requested'] = self::array_add_unique($updates['scopes_requested'], $scope);

			} else {

				if ($value) {
					$client->requestScopes(array($scope));
				} else {
					$client->setScope($scope, null);
				}


			}


		}

		$client->store(array('scopes', 'scopes_requested'));

	}







	/*
	 * Takes an array of scope strings as input and returns only the subset of
	 * scopes that matches a given prefix.
	 */
	public static function filterScopeWithPrefix($prefix, $scopes) {
		$results = array();
		if (empty($scopes)) return $results;
		foreach($scopes AS $scope) {
			if (!strncmp($scope, $prefix, strlen($prefix))) {
				$results[] = $scope;
			}
		}
		return $results;
	}

	/*
			--- o --- o --- o --- o --- o  Static functions
	 */

	public static function getSubIDfromHost($host) {

		// $sid = Utils::getSubID($host);

		$store = new UWAPStore();
		$res = $store->queryOne('clients', array('externalhost' => $host));
		if (!empty($res)) {
			return $res['id'];
		}
		return null;
		// throw new Exception('Application configuration does not yet exists for this domain.');
	}


}