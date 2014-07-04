<?php

/**
* 
*/
class HostedService extends Client {
	
	public function getHost() {
		$ext = $this->get('externalhost', null);
		if ($ext !== null) {
			return $ext;
		}
		return $this->get('id') . '.' . GlobalConfig::hostname();
	}


	
	public function controlsScope($scope) {

		$searchForPrefix = 'rest_' . $this->get('id');

		return (strpos($scope, $searchForPrefix) === 0);
	}


}

