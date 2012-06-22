<?php

class UWAPStore {
	
	protected $db;
	protected $USER = 'uwap';
	protected $PASS = 'xQf0jbKKUOS1kp';


	public function __construct() {

		$dbc = new Mongo("mongodb://" . $this->USER . ":" .  $this->PASS . "@staff.mongohq.com:10098/uwap");;
		$this->db = $dbc->uwap;
	}

	public function getStats($collection) {
		$result = $this->db->execute('db["appdata-' . $collection . '"].stats()');
		// echo '<pre>'; print_r($result); exit;
		if (!$result['ok']) return null;
		if (!$result['retval']['ok']) return null;
		return $result['retval'];
	}


	// Use the $set operator to set a particular value. The $set operator requires the following syntax:
	// 
	// 		db.collection.update( { field: value1 }, { $set: { field1: value2 } } );
	// 
	// This statement updates in the document in collection where field matches value1 by replacing the value of 
	// the field field1 with value2. This operator will add the specified field or fields if they do not exist in 
	// this document or replace the existing value of the specified field(s) if they already exist.
	public function update($collection, $userid, $criteria, $updates) {
		if (isset($userid)) {
			$criteria["uwap-userid"] = $userid;	
		}

		$updatestmnt = array('$set' => $updates);

		$return = $this->db->{$collection}->update($criteria, $updatestmnt, array("safe" => true));
		error_log("Return on update() : " . var_export($return, true));
		return $return;
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

	public function queryOneUser($collection, $userid, $groups, $criteria = array(), $fields = array()) {
		// $criteria["uwap-userid"] = $userid;
		if ($userid !== null) {
			$criteria['$or'] = $this->getACL($userid, $groups);
		}
		
		return $this->queryOne($collection, $criteria, $fields);
	}
	public function queryListUser($collection, $userid, $groups, $criteria = array(), $fields = array()) {
		// $criteria["uwap-userid"] = $userid;
		$criteria['$or'] = $this->getACL($userid, $groups);
		return $this->queryList($collection, $criteria, $fields);
	}

	public function count($collection, $criteria = array()) {
		return $this->db->{$collection}->count($criteria);
	}

	public function queryOne($collection, $criteria = array(), $fields = array()) {
		error_log("queryOne: (" . $collection . ") " . var_export($criteria, true));

		$cursor = $this->db->{$collection}->find($criteria, $fields);
		if ($cursor->count() < 1) return null;
		return $cursor->getNext();
	}

	public function queryList($collection, $criteria, $fields = array()) {
		// echo "\n\n"; print_r($criteria); exit;
		$cursor = $this->db->{$collection}->find($criteria, $fields);
		if ($cursor->count() < 1) return null;
		
		$result = array();
		foreach($cursor AS $element) $result[] = $element;
		return $result;
	}

}