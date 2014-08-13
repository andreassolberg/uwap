<?php


class So_Authorization {
	public $userid, $client_id, $issued, $scope, $stored = false;
	function __construct($userid = null, $client_id = null, $scope = null, $issued = null) {
		$this->userid = $userid;
		$this->client_id = $client_id;
		$this->scope = $scope;
		$this->issued = $issued;
		
		if ($this->issued === null && $this->stored === false) $this->issued = time();
	}
	public function addScopes($scopes) {
		foreach($scopes AS $scope) {
			if (!in_array($scope, $this->scope)) $this->scope[] = $scope;
		}
	}
	static function fromObj($obj) {
		$n = new So_Authorization();
		if (isset($obj['userid'])) $n->userid = $obj['userid'];
		if (isset($obj['client_id'])) $n->client_id = $obj['client_id'];
		if (isset($obj['scope'])) $n->scope = $obj['scope'];
		if (isset($obj['issued'])) $n->issued = $obj['issued']->sec;
		$n->stored = true;
		return $n;
	}
	
	function getScope() {
		return join(' ', $this->scope);
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
	function includeScopes($requiredscopes) {
		if ($requiredscopes === null) return true;
		// echo '<pre>'; print_r($requiredscopes); exit;
		assert('is_array($requiredscopes)');
		foreach($requiredscopes AS $rs) {
			if (!in_array($rs, $this->scope)) return false;
		}
		return true;
	}
	public function remainingScopes($requiredscopes) {
		return array_diff($requiredscopes, $this->scope);
	}
}