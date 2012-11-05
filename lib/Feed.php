<?php

class Feed {

	protected $store, $clientid, $userid, $groups;

	public function __construct($userid = null, $clientid = null, $groups = array()) {

		$this->userid = $userid;
		$this->clientid = $clientid;
		$this->groups = $groups;
		$this->store = new UWAPStore();

	}

	static public function array_remove($remove, $ar) {
		$r = array();
		foreach($ar AS $e) {
			if ($e !== $remove) $r[] = $e;
		}
		return $r;
	}

	public function readItem($id) {
		$query = array('_id' => $id);
		// r($collection, $userid, $groups, $criteria = array(), $fields = array()) {
		// print_r($query); exit;
		// $item = $this->store->queryOneUser("feed", $this->userid, $this->groups, $query);

		return $this->read(array('id_' => $id));

	}

	public function read($selector) {

		// print_r($selector); exit;

		$query = array(
		);

		if (isset($selector['id_'])) {
			$query['$or'] = array(
				array('_id' => new MongoID($selector['id_'])), 
				array('inresponseto' => $selector['id_']),
			);
		}
		

		if (isset($selector['user'])) {
			if ($selector['user'] === '@me' && $this->userid) {
				$query['uwap-userid'] = $this->userid;
			} else {
				$query['uwap-userid'] = $selector['user'];
			}
		}


		$qgroups = $this->groups;
		if (isset($selector['group'])) {

			if (isset($this->groups[$selector['group']])) {
				// $qgroups = array($selector['group'] => $this->groups[$selector['group']]);
				$query['uwap-acl-read'] = array(
					'$in' => array($selector['group']),
				);
			}
			
		}


		if (isset($selector['class'])) {
			$query['class'] = array(
				'$in' => $selector['class']
			);
		}

		if (isset($selector['from'])) {
			$query['ts'] = array(
				'$gt' => $selector['from'],
			);
		}


		// echo 'groups'; print_r($this->groups); exit;
		$auth = new AuthBase();
		if ($this->userid) {
			$list = $this->store->queryListUser("feed", $this->userid, $this->groups, $query, array(), array('limit' => 50, 'sort' => array('ts' => -1)));	
		} else {
			$list = $this->store->queryListClient("feed", $this->clientid, $this->groups, $query, array(), array('limit' => 50, 'sort' => array('ts' => -1)));	
		}

		$range = array('from' => null, 'to' => null);
		
		if (empty($list)) return array();
		foreach($list AS $k => $v) {
			if (!empty($v['uwap-acl-read'])) {
				$list[$k]['groups'] = $v['uwap-acl-read'];
			}
			if (empty($list[$k]['groups'])) {
				$list[$k]['groups'] = array();
			}

			$list[$k]['public'] = false;
			if (in_array('!public', $list[$k]['groups'])) {
				$list[$k]['groups'] = self::array_remove('!public', $list[$k]['groups']);
				$list[$k]['public'] = true;
			}

			if (!empty($list[$k]['class'])) {
				if (is_string($list[$k]['class'])) {
					$list[$k]['class'] = array($list[$k]['class']);
				}
			} else {
				$list[$k]['class'] = array('message');
			}

			if (!empty($v['uwap-userid'])) {
				$list[$k]['user'] = $auth->getUserBasic($v['uwap-userid']);
			}
			if (!empty($v['uwap-clientid'])) {
				$list[$k]['client'] = $auth->getClientBasic($v['uwap-clientid']);
			}
			$list[$k]['id'] = $v['_id']->{'$id'};

			if ($range['to'] === null) $range['to'] = $list[$k]['ts'];
			if ($range['from'] === null) $range['from'] = $list[$k]['ts'];
			if ($list[$k]['ts'] > $range['to']) $range['to'] = $list[$k]['ts'];
			if ($list[$k]['ts'] < $range['from']) $range['from'] = $list[$k]['ts'];
		}

		$response = array(
			'items' => array_reverse($list),
			'range' => $range,
		);


		return $response;
	}

	public function delete($oid) {

		return $this->store->remove('feed', $this->userid, array('_id' => array('$id' => $oid)));

	}

	public function post($msg, $groups = array()) {
		if (!is_array($groups)) throw new Exception("Provided groups must be an array");


		// Perform access control on who can post to which group, and also if the user is a superuser.
		// filter accepted properties on object.


		if (isset($msg['public'])) {
			if ($msg['public']) {
				$groups[] = '!public';
			}
			unset($msg['public']);
		}

		$msg['uwap-acl-read'] = $groups;

		// unset($groups);

		if (!empty($msg['oid'])) {
			if ($this->store->queryOne('feed', array('oid' => $msg['oid']))) {
				return false;
			}
		}

		if (empty($msg['ts'])) {
			$msg['ts'] = time();	
		}
		
		if (!empty($this->clientid)) {
			$msg['uwap-clientid'] = $this->clientid;
		}

		return $this->store->store("feed", $this->userid, $msg);
		// store($collection, $userid = null, $obj, $expiresin = null) {
	}



/*

	case 'remove':
		if (empty($parameters['object'])) throw new Exception("Missing required parameter [object] object to save");
		$store->remove("appdata-" . $targetapp, $userid, $parameters['object']);
		break;

	case 'save':
		if (empty($parameters['object'])) throw new Exception("Missing required parameter [object] object to save");
		$store->store("appdata-" . $targetapp, $userid, $parameters['object']);
		break;

		// TODO: Clean output before returning. In example remove uwap- namespace attributes...
	case 'queryOne':
		if (empty($parameters['query'])) throw new Exception("Missing required parameter [query] query");
		$response['data'] = $store->queryOneUser("appdata-" . $targetapp, $userid, $groups, $parameters['query']);
		break;

	case 'queryList':
		if (empty($parameters['query'])) throw new Exception("Missing required parameter [query] query");
		$response['data'] = $store->queryListUser("appdata-" . $targetapp, $userid, $groups, $parameters['query']);
		break;

 */

}

