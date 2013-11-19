<?php


class FeedReader {
	
	protected $store;

	protected $user = null;
	protected $client = null;

	public function __construct(Client $client, User $user = null) {

		$this->client = $client;
		$this->user = $user;

		$this->store = new UWAPStore();

	}


	protected function getQuery($selector) {

		$query = array();

		if (isset($selector['id_'])) {
			$query['$or'] = array(
				array('_id' => new MongoID($selector['id_'])), 
				array('inresponseto' => $selector['id_']),
			);


		} else if (isset($selector['id'])) {
			$query['_id'] = new MongoID($selector['id']);


		} else if (isset($selector['ids'])) {

			$ids = array();
			foreach($selector['ids'] AS $i) {
				$ids[] = new MongoID($i);
			}
			$query['_id'] = array('$in' => $ids);

		} else if (isset($selector['inresponseto'])) {

			$query['inresponseto'] = $selector['inresponseto'];

		} else if (isset($selector['inresponsetos'])) {

			$query['inresponseto'] = array('$in' => $selector['inresponsetos']);

		}


		if (isset($selector['user'])) {
			if ($selector['user'] === '@me' && $this->user) {
				$query['uwap-userid'] = $this->user->get('userid');
			} else {
				$query['uwap-userid'] = $selector['user'];
			}
		}


		$groups = $this->user->getGroups();
		$subscriptions = $this->user->getSubscriptions();
		if (isset($selector['group'])) {

			if (isset($groups[$selector['group']]) || isset($subscriptions[$selector['group']])) {
				// $qgroups = array($selector['group'] => $this->groups[$selector['group']]);
				$query['audience.groups'] = array(
					'$in' => array($selector['group']),
				);
			}

		}


		if (isset($selector['class'])) {
			$query['class'] = array(
				'$in' => $selector['class']
			);
		}

		
		if (isset($selector['created-from'])) {

			$query['created'] = array(
				'$gt' => $selector['created-from'],
			);

		}	

		if (isset($selector['from'])) {
			// $query['ts'] = array(
			// 	'$gt' => new MongoInt64((string)$selector['from']),
			// );
			$query['_id'] = array(
				'$gt' => new MongoID((string)$selector['from']),
			);
		}

		$now = time();
		if (isset($selector['future'])) {
			$query['dtstart'] = array(
				'$gt' => $now,
			);
		}


		return $query;
	}



	public function post($properties) {

		$feedItem = FeedItem::generate($properties, $this->user, $this->client);

		return $feedItem;

	}

	public function respond($properties) {


		$parent = FeedItem::getByID($properties['inresponseto']);

		$query = array(
			'class' => array(
				'$in' => array('response')
			),
			'inresponseto' => $properties['inresponseto'],
			'uwap-userid' => $this->user->get('userid'),
		);
		// queryOne($collection, $criteria = array(), $fields = array()) {
		$data = $this->store->queryOne('feed', $query);

		if (!empty($data)) {

			// echo "About to update with"; print_r($properties); exit;

			$response = new FeedItem($data);
			$response->update($properties, $this->user, $this->client);

		} else {

			// header('Content-type: text/plain');
			// echo "About to create new"; print_r($properties); 
			// print_r($query);
			// echo json_encode($query);
			// exit;
			$response = FeedItem::generate($properties, $this->user, $this->client);

		}	
		
		return $this->read(array('id_' => $properties['inresponseto']));

	}

	public function delete($id) {
		$item = FeedItem::getByID($id);
		if ($item->user->get('userid') !== $this->user->get('userid')) {
			throw new Exception('You cannot delete a feed entry owned by another person');
		}

		$query = $this->getQuery(array('id_' => $item->get('id')));

		// echo "Query delete: "; print_r($query); exit;

		return $this->store->remove("feed", null, $query);

	}





	public function readQuery($query, $fill = true) {

		$options = array(
			'limit' => 50, 
			'sort' => array('_id' => -1)
		);

		if ($this->user !== null) {
			$groups = $this->user->getGroups();
			$list = $this->store->queryListUserAdvanced("feed", 
				$this->user, $query, 
				array(), $options);

		} else {
			$groups = array();
			$list = $this->store->queryListClient("feed", $this->client->get('id'), $groups, $query, array(), $options);	
		}

		if (empty($list['items'])) {
			$list['items'] = array();
		}

		if (isset($list['time'])) {
			error_log("Time to complete query for feed items", $list['time']);
		}

		$feed = new Feed($list['items']);


		if ($fill) {

			$missingParents = $feed->missingParentItems();

			if (!empty($missingParents)) {
				$parents = $this->read(array('ids' => $missingParents), false);
				$parents->mergeInto($feed);
			}

			$mayHaveResponses = $feed->itemsMayHaveResponses();
			if (!empty($mayHaveResponses)) {
				$responses = $this->read(array('inresponsetos' => $mayHaveResponses), false);
				$responses->mergeInto($feed);
			}

			// echo "May have "; print_r($mayHaveResponses);
			// echo "Missing parents "; print_r($missingParents);

		}

		$feed->postprocess();


		// Utils::dump('Query seletor', $query, false);
		// Utils::dump('feed obejct', $feed->getJSON());
		// Utils::dump('Result from feed query', $list);
		return $feed;


	}



	
	public function read($selector, $fill = true) {

		$query = $this->getQuery($selector);


		return $this->readQuery($query, $fill);

	}

	public function readUpcoming($selector) {

		$selector['future'] = true;
		$selector['class'] = array('event');

		$query = $this->getQuery($selector);

		// Utils::dump('query', $query);
		


		return $this->readQuery($query, false);
	}





	// 2592000000 is 30 days
	public function readNotifications($selector, $ago = 2592000000) {


		$readnotifications = $this->store->queryListUser('notifications', $this->user->get('userid'), null, array());
		$readids = array();
		if (!empty($readnotifications)) {
			foreach($readnotifications AS $item) {
				$readids[$item['id']] = 1;
			}
		}
		// print_r($readids); exit;
		


		$selector['created-from'] = floor(microtime(true)*1000.0) - $ago;

		$query = $this->getQuery($selector);


		$feed = $this->readQuery($query, false);
		$feed->filterAwayItemsByID($readids);
		$feed->filterAwayItemsByUserID($this->user->get('userid'));

		// print_r($query);

		$parents = null;
		$missingParents = $feed->missingParentItems();

		// print_r($feed->getJSON());
		// print_r($missingParents); exit;

		if (!empty($missingParents)) {
			$parents = $this->read(array('ids' => $missingParents), false);

			// print_r($parents->getJSON()); exit;

			// $parents->mergeInto($feed);
		}

		$n = new Notifications($feed, $parents);


		return $n;

	}

	public function getReadIDs() {
		$readnotifications = $this->store->queryListUser('notifications', $this->user->get('userid'), null, array());

		$readids = array();
		foreach($readnotifications AS $item) {
			$readids[$item['id']] = 1;
		}
		return array_keys($readids);
	}


	public function markNotificationsRead($ids) {

		$readnotifications = $this->getReadIDs();

		// echo "readNotifications"; print_r($readnotifications); 




		$remaining = self::array_remove($ids, $readnotifications);

		// print_r("rmaining"); print_r($remaining); exit;

		foreach($remaining AS $id) {
			$object = array('id' => $id);

			// echo "about to store " . $this->user->get('userid') . " " . var_export($object, true); exit;

			$this->store->store('notifications', $this->user->get('userid'), $object, 2592000); // 30 days
		}

		return $remaining;

	}



	static function array_remove($a1, $a2) {
		$r = array();
		if (empty($a2)) return $a1;
		foreach($a1 AS $k => $v) {
			if (!in_array($v, $a2)) {
				$r[] = $v;
			}
		}
		return $r;

	}


}