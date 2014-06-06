<?php

function uwapfeedsort($a, $b) {
	// return $a['lastActivity'] < $b['lastActivity'];
	return ($a->lastActivity < $b->lastActivity) ? -1 : 1;
}



class Feed extends Set {
	
	protected $users = array();
	protected $clients = array();

	protected $idrefs = array();

	protected $range;

	function add(Model $entry) {
		if (!$entry instanceof FeedItem) throw new Exception('Trying to add invalid Model to a Feed set');

		if (isset($this->idrefs[$entry->get('id')])) {
			return;
		}


		parent::add($entry);

		if ($entry->user) {
			$this->users[$entry->user->get('userid')] = $entry->user;
		}

		if ($entry->client) {
			$this->clients[$entry->client->get('id')] = $entry->client;
		}

		$this->idrefs[$entry->get('id')] = $entry;

		// header('Content-Type: text/plain'); print_r($entry); 
		// print_r( $entry->client->get('id'));
		// exit;

		// $clientid = $entry->client->get('id');
		// if (!isset($clients[$clientid])) {
		// 	$this->clients[$clientid] = $entry->client;
		// }
		// $appid = $entry->targetApp->get('id');
		// if (!isset($targetApps[$appid])) {
		// 	$this->targetApps[$appid] = $entry->targetApp;
		// }
	}


	public function filterAwayItemsByID($ids) {

		foreach($this->items AS $k => $item) {
			if (isset($ids[$item->get('id')])) {
				unset($this->items[$k]);
				unset($this->idrefs[$item->get('id')]);
			}
		}

	}

	public function filterAwayItemsByUserID($userid) {
		foreach($this->items AS $k => $item) {
			// print_r($item);
			if ($item->user && $userid === $item->user->get('userid')) {
				unset($this->items[$k]);
				unset($this->idrefs[$item->get('id')]);
			}
		}
	}

	public function addData($properties) {

		// if (!isset($properties['clientid'])) throw new Exception('Cannot add authorization data without a client');
		// if (!isset($properties['targetApp'])) throw new Exception('Cannot add authorization data without a targetApp');

		// $client = $properties['client']; unset($properties['client']);
		// $targetApp = $properties['targetApp']; unset($properties['targetApp']);

		// Utils::dump('add data entry', $properties, false);

		$fi = new FeedItem($properties);
		$this->add($fi);
	}

	public function addDataList($list) {
		parent::addDataList($list);

		// $this->postprocess();
	}

	public function postprocess () {

		$range = array('from' => null, 'to' => null);
		if (empty($this->items)) return;

		foreach($this->items AS $item) {
			$id = $item->get('id');

			if ($range['to'] === null) $range['to'] = $id;
			if ($range['from'] === null) $range['from'] = $id;
			if ($id > $range['to']) $range['to'] = $id;
			if ($id < $range['from']) $range['from'] = $id;

		}
		$this->range = $range;


		foreach($this->items AS $item) {

			if ($item->has('inresponseto')) {
				$id = $item->get('inresponseto');
				if (isset($this->idrefs[$id])) {
					// echo "Inresponse to is set , and lloking AND $id found\n";
					$this->idrefs[$id]->responses[] = $item;
				} else {
					// echo "Inresponse to is set , and lloking up but dod not  $id found\n";
					// print_r(array_keys($this->idrefs));
				}
			}

		}
		foreach($this->items AS $item) {
			$item->calculateLastActivity();
		}


		usort($this->items, 'uwapfeedsort');

	}

	public function missingParentItems() {

		$missingParents = array();

		if (!empty($this->items)) {
			foreach($this->items AS $item) {
				if ($item->has('inresponseto')) {
					// echo "CHECKING in response to " . $item->get('inresponseto');
					if (!isset($this->idrefs[$item->get('inresponseto')])) {
						// echo "HAS in response to " . $item->get('inresponseto');
						$missingParents[] = $item->get('inresponseto');
					}
				}
			}
		}
		return $missingParents;

	}

	public function itemsMayHaveResponses() {
		/*
		 * try to fetch all responses to the objects that needs it.
		 */
		// $uniqueids = array(); // Log all ids used, and merge only those that are not already fetched later on.
		$events = array();
		if (!empty($this->items)) {
			foreach($this->items AS $item) {
				// $uniqueids[$item->get('id')] = 1;
				if ($item->hasClass('event')) {
					$events[] = $item->get('id');
				}
			}
		}

		return $events;


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
	}



	public function getJSON($opts = array()) {

		$result = parent::getJSON($opts);

		if (!empty($this->clients)) {
			$result['clients'] = array();
			foreach($this->clients AS $clientid => $client) {
				$result['clients'][$clientid] = $client->getJSON(array_merge($opts, array('type'=> 'basic')));
			}
		}

		if (!empty($this->users)) {
			$result['users'] = array();
			foreach($this->users AS $userid => $user) {
				$result['users'][$userid] = $user->getJSON(array_merge($opts, array('type'=> 'basic')));
			}			
		}

		if (isset($this->range)) {
			$result['range'] = $this->range;
		}
		return $result;
	}

}