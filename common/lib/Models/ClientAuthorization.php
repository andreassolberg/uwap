<?php

/**
 * The ClientAuthorization represents a set of requested and / or granted scopes from 
 * one client (client) to a specific target application (targetApp).
 *
 * An example:
 *
 * 	Client "meetingroomwidget" is authorized to access "connectapi" in the following way:
 * 		Granted access to:
 * 			rest_connectapi
 * 		Requested access to:
 * 			rest_connectapi_admin
 * 			rest_connectapi_delete
 */
class ClientAuthorization extends Model {
	
	public $client, $targetApp;
	protected static $validProps = array('scopes', "scopes_requested");

	
	public function __construct($properties, Client $client, HostedService $targetApp) {

		// if (!$user instanceof User) throw new Exception('Creating new role without a proper User object');
		// if (!$group instanceof Group) throw new Exception('Creating new role without a proper Group object');

		$this->client = $client;
		$this->targetApp = $targetApp;

		parent::__construct($properties);
	}

	public function getJSON($opts = array()) {


		$result = parent::getJSON($opts);

		if (isset($this->client)) {
			$result['client'] = $this->client->get('id');
		}
		if (isset($this->targetApp)) {
			$result['targetApp'] = $this->targetApp->get('id');
		}

		return $result;

		// if (isset($opts['type']) && $opts['type'] === 'key') {
		// 	return $this->group->get('id');
		// }

		// $data = $this->group->getJSON($opts);
		// return array_merge($data, $this->properties);
	}

}