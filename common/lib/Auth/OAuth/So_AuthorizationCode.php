<?php


class So_AuthorizationCode {
	public $issued, $validuntil, $tokenexpiresin, $code, $userid, $userdata, $client_id, $scope;
	function __construct() {
	}
	
	function getObj() {
		$obj = array();
		foreach($this AS $key => $value) {
			if (in_array($key, array())) continue;
			if ($value === null) continue;
			$obj[$key] = $value;
		}
		return $obj;
	}
	
	static function fromObj($obj) {
		$n = new So_AuthorizationCode();
		if (isset($obj['issued'])) $n->issued = $obj['issued'];
		if (isset($obj['validuntil'])) $n->validuntil = $obj['validuntil'];
		if (isset($obj['tokenexpiresin'])) $n->tokenexpiresin = $obj['tokenexpiresin'];
		if (isset($obj['code'])) $n->code = $obj['code'];
		if (isset($obj['userid'])) $n->userid = $obj['userid'];
		if (isset($obj['userdata'])) $n->userdata = $obj['userdata'];
		if (isset($obj['client_id'])) $n->client_id = $obj['client_id'];
		if (isset($obj['scope'])) $n->scope = $obj['scope'];
		return $n;
	}
	
	static function generate($client_id, $userid, $userdata, $scope, $expires_in = 3600) {
		$n = new So_AuthorizationCode();
		$n->client_id = $client_id;
		$n->userid = $userid;
		$n->userdata = $userdata;
		$n->scope = $scope;

		$n->tokenexpiresin = $expires_in;
		$n->issued = time();
		$n->validuntil = time() + 3600;
		$n->code = So_Utils::gen_uuid();

		return $n;
	}
}