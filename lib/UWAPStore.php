<?php

class UWAPStore {
	
	protected $db;
	protected $USER = 'uwap';
	protected $PASS = 'xQf0jbKKUOS1kp';


	public function __construct() {

		$dbc = new Mongo("mongodb://" . $this->USER . ":" .  $this->PASS . "@staff.mongohq.com:10098/uwap");;
		$this->db = $dbc->uwap;
	}

	public function store($collection, $userid = null, $obj, $expiresin = null) {
		
		if (isset($userid)) {
			$obj["uwap-userid"] = $userid;	
		}
		

		if (isset($obj["_id"]) && !is_object($obj["_id"]) && isset($obj["_id"]['$id'])) {
			$obj["_id"] = new MongoId($obj["_id"]['$id']);
		}
		if ($expiresin !== null) {
			$obj["expires"] = time() + $expiresin;
		}

		error_log("store() " . var_export($obj, true));
		// try {
		$this->db->{$collection}->save($obj, array("safe" => true));	


		error_log("STORING Object: ". var_export($obj, true));
		// } catch (Exception $e) {
		// 	print_r($e);
		// }
		

		// save() returns an array of info about success of the save. TODO: translate to exception if needed.

	}


	public function remove($collection, $userid, $obj) {
		if (isset($obj["_id"]) && !is_object($obj["_id"]) && isset($obj["_id"]['$id'])) {
			$obj["_id"] = new MongoId($obj["_id"]['$id']);
		}

		if (isset($userid)) {
			$obj["uwap-userid"] = $userid;
		}

		// echo '<pre>Removing token: ';
		// print_r($obj); exit;

		$result = $this->db->{$collection}->remove($obj, array("safe" => true));
		if (is_array($result)) {
			foreach($result AS $r) {
				if ($r === false) throw new Exception('Error removing object from MongoDB storage');
			}
		} else if (is_bool($result)) {
			if (!$result) throw new Exception('Error removing object from MongoDB storage');
		}
		return true;
	}

	public function getACL($userid, $groups = array()) {
		$grps = array_keys($groups);
		$grps[] = '!public';
		$criteria = array();
		$criteria[] = array("uwap-userid" => $userid);
		$criteria[] = array(
			"uwap-acl-read" => array(
				'$in' => $grps,
			),
		);
		return $criteria;
	}

	public function queryOneUser($collection, $userid, $groups, $criteria = array()) {
		// $criteria["uwap-userid"] = $userid;
		$criteria['$or'] = $this->getACL($userid, $groups);
		return $this->queryOne($collection, $criteria);
	}
	public function queryListUser($collection, $userid, $groups, $criteria = array()) {
		// $criteria["uwap-userid"] = $userid;
		$criteria['$or'] = $this->getACL($userid, $groups);
		return $this->queryList($collection, $criteria);
	}

	public function queryOne($collection, $criteria) {
		error_log("queryOne: (" . $collection . ") " . var_export($criteria, true));

		$cursor = $this->db->{$collection}->find($criteria);
		if ($cursor->count() < 1) return null;
		return $cursor->getNext();
	}

	public function queryList($collection, $criteria) {
		// echo "\n\n"; print_r($criteria); exit;
		$cursor = $this->db->{$collection}->find($criteria);
		if ($cursor->count() < 1) return null;
		
		$result = array();
		foreach($cursor AS $element) $result[] = $element;
		return $result;
	}

}