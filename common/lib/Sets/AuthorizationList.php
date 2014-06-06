<?php


/*

	authorization

		for a specific "client"

		a list of "clients" with associated scopes.


 */


class AuthorizationList extends Set {
	
	protected $clients = array();
	protected $targetApps = array();


	function add(Model $entry) {
		if (!$entry instanceof ClientAuthorization) throw new Exception('Trying to add invalid Model to an AuthorizationList set');
		parent::add($entry);

		// header('Content-Type: text/plain'); print_r($entry); 
		// print_r( $entry->client->get('id'));
		// exit;

		$clientid = $entry->client->get('id');
		if (!isset($clients[$clientid])) {
			$this->clients[$clientid] = $entry->client;
		}
		$appid = $entry->targetApp->get('id');
		if (!isset($targetApps[$appid])) {
			$this->targetApps[$appid] = $entry->targetApp;
		}
	}

	public function addData($properties) {

		if (!isset($properties['client'])) throw new Exception('Cannot add authorization data without a client');
		if (!isset($properties['targetApp'])) throw new Exception('Cannot add authorization data without a targetApp');

		$client = $properties['client']; unset($properties['client']);
		$targetApp = $properties['targetApp']; unset($properties['targetApp']);

		$ca = new ClientAuthorization($properties, $client, $targetApp);
		$this->add($ca);
	}


	public function getJSON($opts = array()) {

		$result = parent::getJSON($opts);

		$result['clients'] = array();
		foreach($this->clients AS $clientid => $client) {
			$result['clients'][$clientid] = $client->getJSON(array_merge($opts, array('type'=> 'basic')));
		}

		$result['targetApps'] = array();
		foreach($this->targetApps AS $targetAppID => $targetApp) {
			$result['targetApps'][$targetAppID] = $targetApp->getJSON(array_merge($opts, array('type'=> 'basic')));
		}
		return $result;
	}

}