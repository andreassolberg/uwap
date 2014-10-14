<?php

/**
* ClientAuthorizationHelper helps one to obtain information about the permissions
* that a client has.
*/
class ClientAuthorizationHelper {
	protected $client;
	protected $globalScopes;

	function __construct(Client $client) {
		$this->client = $client;
		$this->globalScopes = array(
			'userinfo' => array(
				'type' => 'userinfo'
			),
			'longterm' => array(
				'type' => ''
			)
		);
	}

	function getInfo() {

		$data = array();

		$scopes = $this->client->get('scopes');

		$data['global'] = array(); 
		$data['apps'] = array();
		$apps = array();


		foreach($scopes AS $scope) {



			if (preg_match('/rest_([a-z0-9\-]+)(_([a-z0-9\-]+))?/', $scope, $matches)) {

				$appid = $matches[1];
				Utils::validateID($appid);

				if (!isset($data['apps'][$appid])) {
					$apps[$appid] = array('localScopes' => array());
				}

				if (isset($matches[2])) {
					$apps[$appid]['localScopes'][] = $matches[2];
				}

				// $proxy = APIProxy::getByID($appid);

				// $localScope = (isset($matches[2]) ? $matches[2] : null);


			} else {

				$data['global'][$scope] = 1; 

			}

		}


		foreach($apps AS $appid => $v) {

			Utils::validateID($appid);
			$proxy = APIProxy::getByID($appid);

			$ainfo = array(
				'id' => $appid,
				'title' => $proxy->get('name'),
				'descr' => $proxy->get('descr'),
				'perms'	=> $proxy->getScopeInfo()
			);

			if ($proxy->has('owner-descr')) {
				$ainfo['owner-descr'] = $proxy->get('owner-descr');
			} else {
				$owner = User::getByID($proxy->get('uwap-userid'));
				$ainfo['owner'] = $owner->getJSON(array('type' => 'basic'));
			}

			$data['apps'][] = $ainfo;

		}

		return $data;


	}




}
