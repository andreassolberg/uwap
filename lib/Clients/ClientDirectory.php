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