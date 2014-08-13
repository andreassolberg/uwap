<?php


class So_AuthorizationCode {

	public $issued, $tokenexpiresin, $code, $userid, $client_id, $scope, $redirect_uri;

	function __construct() {
	}
	
	function getObj() {
		$obj = array();
		foreach($this AS $key => $value) {
			if (in_array($key, array('stored'))) continue;
			if ($value === null) continue;
			if (in_array($key, array('issued'))) {
				$obj[$key] = new MongoDate($value);
				continue;
			}
			$obj[$key] = $value;
		}
		return $obj;
	}
	
	static function fromObj($obj) {

		$n = new So_AuthorizationCode();
		if (isset($obj['issued'])) $n->issued = $obj['issued']->sec;
		if (isset($obj['tokenexpiresin'])) $n->tokenexpiresin = $obj['tokenexpiresin'];

		if (isset($obj['client_id'])) $n->client_id = $obj['client_id'];
		if (isset($obj['userid'])) $n->userid = $obj['userid'];

		if (isset($obj['scope'])) $n->scope = $obj['scope'];
		if (isset($obj['redirect_uri'])) $n->redirect_uri = $obj['redirect_uri'];
		if (isset($obj['code'])) $n->code = $obj['code'];

		return $n;

	}
	
	static function generate($client_id, $userid, $scope, $expires_in = 3600, $redirect_uri = null) {

		$n = new So_AuthorizationCode();
		$n->issued = time();
		$n->tokenexpiresin = $expires_in;

		$n->client_id = $client_id;
		$n->userid = $userid;

		$n->scope = $scope;
		$n->redirect_uri = $redirect_uri;
		$n->code = So_Utils::gen_uuid();

		return $n;

	}



}




