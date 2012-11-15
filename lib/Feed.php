<?php

		function uwapfeedsort($a, $b) {
			// return $a['lastActivity'] < $b['lastActivity'];
			return ($a['lastActivity'] < $b['lastActivity']) ? -1 : 1;
		}

class Feed {

	protected $store, $clientid, $userid, $groups, $subscriptions;

	public function __construct($userid = null, $clientid = null, $groups = array(), $subscriptions = array()) {

		$this->userid = $userid;
		$this->clientid = $clientid;
		$this->groups = $groups;
		$this->store = new UWAPStore();

		$this->subscriptions = array();
		if (!empty($subscriptions)) {
			$this->subscriptions = $subscriptions;	
		}
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

		// print_r($selector); 

		$query = array(
		);

		if (isset($selector['id_'])) {
			$query['$or'] = array(
				array('_id' => new MongoID($selector['id_'])), 
				array('inresponseto' => $selector['id_']),
			);
		}
		
		if (isset($selector['id'])) {
			$query['_id'] = new MongoID($selector['id']);
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

		
		// echo "Performing query:\n";
		// print_r($query);
		//  exit;
		// echo 'groups'; print_r($this->groups); exit;
		// 
		$auth = new AuthBase();
		if ($this->userid) {
			$list = $this->store->queryListUserAdvanced("feed", $this->userid, $this->groups, $this->subscriptions, $query, array(), array('limit' => 50, 'sort' => array('ts' => -1)));	
		} else {
			$list = $this->store->queryListClient("feed", $this->clientid, $this->groups, $query, array(), array('limit' => 50, 'sort' => array('ts' => -1)));	
		}


		



		/*
		 * try to fetch all responses to the objects that needs it.
		 */
		$uniqueids = array(); // Log all ids used, and merge only those that are not already fetched later on.
		$events = array();
		if (!empty($list)) {
			foreach($list AS $k => $v) {
				$id =  $v['_id']->{'$id'};
				$uniqueids[$id] = 1;
				if (isset($v['class']) && is_array($v['class']) && in_array('event', $v['class'])) {
					$events[] = $id;
				}
			}
		}

		$rquery = array(
			'class' => array(
				'$in' => array('response'),
			),
			'inresponseto' => array(
				'$in' => $events,
			),
		);
		if ($this->userid) {
			$responses = $this->store->queryListUserAdvanced("feed", $this->userid, $this->groups, $this->subscriptions, $query, array(), array('limit' => 50, 'sort' => array('ts' => -1)));	
		} else {
			$responses = $this->store->queryListClient("feed", $this->clientid, $this->groups, $query, array(), array('limit' => 50, 'sort' => array('ts' => -1)));	
		}

		if (!empty($responses)) {
			foreach($responses AS $v) {
				$id =  $v['_id']->{'$id'};
				if (!isset($uniqueids[$id])) {
					$list[] = $v;
				}
			}  
		}
		












		// Set up a list with references...
		$references = array();
		if (!empty($list)) {
			foreach($list AS $k => $v) {
				$id =  $v['_id']->{'$id'};
				unset($list[$k]['_id']);
				$list[$k]['id'] = $id;
				$references[$id] = $k;
			}

		}

		if (!empty($list)) {
			foreach($list AS $k => $v) {
				if (isset($v['inresponseto']) && isset($references[$v['inresponseto']]) && isset($list[$references[$v['inresponseto']]]) ) {

					$resolved =& $list[$references[$v['inresponseto']]];

					if (!isset($resolved['linked'])) {
						$resolved['linked'] = array();
					}
					$resolved['linked'][] = $v['id'];

				}
				
			}
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

			$list[$k]['lastActivity'] = $list[$k]['ts'];


			if (isset($list[$k]['linked']) && is_array($list[$k]['linked'])) {
				foreach($list[$k]['linked'] AS $refid) {
					$ref =& $list[$references[$refid]];
					// print_r($ref); exit;
					if ($ref['ts'] > $list[$k]['lastActivity']) {
						$list[$k]['lastActivity'] = $ref['ts'];
					}
				}
			}

			if ($range['to'] === null) $range['to'] = $list[$k]['ts'];
			if ($range['from'] === null) $range['from'] = $list[$k]['ts'];
			if ($list[$k]['ts'] > $range['to']) $range['to'] = $list[$k]['ts'];
			if ($list[$k]['ts'] < $range['from']) $range['from'] = $list[$k]['ts'];
		}


		usort($list, 'uwapfeedsort');

		$response = array(
			'items' => $list,
			'range' => $range,
		);


		return $response;
	}

	public function delete($oid) {

		return $this->store->remove('feed', $this->userid, array('_id' => array('$id' => $oid)));

	}

	public function respond($msg) {

		$msg['class'] = array('response');

		$query = array(
			'class' => array(
				'$in' => array('response')
			),
			'inresponseto' => $msg['inresponseto'],
		);
		// $collection, $userid, $groups, $criteria = array(), $fields = array()) {
		$existing = $this->store->queryOneUser('feed', $this->userid, array(), $query);

		if (!empty($existing)) {
			$existing['status'] = $msg['status'];
			$existing['updated'] = floor(microtime(true)*1000.0);
			$res = $this->store->store('feed', $this->userid, $existing);
		} else {
			$msg['ts'] = floor(microtime(true)*1000.0);
			$res = $this->store->store('feed', $this->userid, $msg);
		}	
		return $this->store->queryOneUser('feed', $this->userid, array(), $query);
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
			$msg['ts'] = floor(microtime(true)*1000.0);
		}
		
		if (!empty($this->clientid)) {
			$msg['uwap-clientid'] = $this->clientid;
		}

		// return $msg;

		return $this->store->store("feed", $this->userid, $msg);

              // store($collection, $userid = null, $obj, $expiresin = null) {
      }


}
