<?php


class Notifications {


	protected $userid, $groups, $store, $subscriptions;

	function __construct($userid, $groups, $subscriptions) {
		$this->userid = $userid;
		$this->groups = $groups;
		
		$this->subscriptions = array();
		if (!empty($subscriptions)) {
			$this->subscriptions = $subscriptions;	
		}

		
		$this->store = new UWAPStore;
	}


	public function itemStr($item) {

		$str = '';

		if (isset($item['user'])) {

			$str .= $item['user']['name'] . ' ';

			if (isset($item['refs'])) {
				$c = count($item['refs']);
				if ($c > 0) {
					$str .= ' and ' . $c . ' more ';
				}
			}

		} else if (isset($item['client'])) {
		
			$str .= $item['client']['client_name'] . ' ';

		}

		if (!isset($item['class'])) $item['class'] = array();
		if (is_string($item['class'])) $item['class'] = array($item['class']);
		if (in_array('message', $item['class'])) {

			$str .= 'posted a message';

		} else if (in_array('article', $item['class'])) {

			$str .= 'posted an article';

		} else if (in_array('file', $item['class'])) {

			$str .= 'uploaded a file';

		} else if (in_array('media', $item['class'])) {

			$str .= 'uploaded media content';

		} else if (in_array('event', $item['class'])) {

			$str .= 'posted an event';

		} else if (in_array('response', $item['class'])) {

			$str .= 'responded to an event';

		} else if (in_array('comment', $item['class'])) {

			$str .= 'replied with a comment';

		} else {
			$str .= 'posted some content';
		}

		if (!isset($item['groups'])) $item['groups'] = array();
		if  (count($item['groups']) === 1) {
			// print_r($item['groups']);
			if (isset($this->groups[$item['groups'][0]])) {
				$str .= ' to ' . $this->groups[$item['groups'][0]];
			}

		}
		// print_r($item['groups']);

		return $str;
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


	function markread($ids) {

		$readnotifications = $this->store->queryListUser('notifications', $this->userid, null, array());

		$remaining = self::array_remove($ids, $readnotifications);

		// print_r("rmaining"); print_r($remaining); exit;

		foreach($remaining AS $id) {
			$query = array('id' => $id);
			$this->store->store('notifications', $this->userid, $query, 2592000); // 30 days
		}

		return $remaining;
	}

	function read($selector, $ago = 2592000000, $ref = false) { // 30 days

		// queryListUser($collection, $userid, $groups, $criteria = array(), $fields = array(), $options = array() ) {


		$readnotifications = $this->store->queryListUser('notifications', $this->userid, null, array());



		$readids = array();
		if (!empty($readnotifications)) {
			foreach($readnotifications AS $item) {

				$readids[$item['id']] = 1;
				// print_r($item); 
			}
		}
		
		// exit;
		// print_r($readids); exit;

		$results = array();
		$feed = new Feed($this->userid, null, $this->groups, $this->subscriptions);

		$selector['from'] = floor(microtime(true)*1000.0) - $ago;

		// print_r($selector); exit;

		$entries = $feed->read($selector);




		// foreach($entries['items'] AS $k => $entry) {

		// 	if (isset($entry['uwap-userid']) && $entry['uwap-userid'] === $this->userid) {
		// 		unset($entries['items'][$k]);
		// 	}

		// 	$ne = array('id' => $entry['id'], 'ts' => $entry['ts']);

		// 	$isread = isset($readids[$entry['id']]);
		// 	$ne['isread'] = $isread;
		// 	if (!$isread) {++$unreadcount;}

		// 	if (isset($entry['inresponseto'])) $ne['inresponseto'] = $entry['inresponseto'];

		// 	$ne['summary'] = $this->itemStr($entry);
		// 	$ne['timehuman'] = date('D, d M Y H:i:s', $entry['ts']);

		// 	$results[] = $ne;

		// }





		/*
		 * Walk through notifications items to merge comments on same item...
		 */
		$refs = array();
		if (empty($entries['items'])) return null;
		foreach($entries['items'] AS $k => $entry) {

			if (isset($entry['uwap-userid']) && $entry['uwap-userid'] === $this->userid) continue;

			if (isset($entry['inresponseto'])) {

				if (isset($refs[$entry['inresponseto']])) {

					$refs[$entry['inresponseto']]['refs'][] = $entry;
					unset($entries['items'][$k]);

				} else {

					$refs[$entry['inresponseto']] =& $entries['items'][$k];
					$entries['items'][$k]['refs'] = array();
					// $refs[$entry['inresponseto']]['refs'] = array();

				}

			}

		}
		// print_r($refs); exit;
		// print_r($entries['items']); exit;


		$unreadcount = 0;

		foreach($entries['items'] AS $entry) {

			if (isset($entry['uwap-userid']) && $entry['uwap-userid'] === $this->userid) continue;

			$ne = array('id' => $entry['id'], 'ts' => $entry['ts']);

			$isread = isset($readids[$entry['id']]);
			$ne['isread'] = $isread;
			if (!$isread) {++$unreadcount;}

			if (isset($entry['inresponseto'])) $ne['inresponseto'] = $entry['inresponseto'];
			if (isset($entry['class'])) $ne['class'] = $entry['class'];

			if (isset($entry['refs'])) {
				$ne['refs'] = count($entry['refs']);
			}

			$ne['summary'] = $this->itemStr($entry);
			$ne['timehuman'] = date('D, d M Y H:i:s', $entry['ts']);

			if ($ref) {
				$ne['ref'] = $entry;
			}

			$results[] = $ne;

		}

		$response = array(
			'items' => $results,
			'range' => $entries['range'],
			'unreadcount' => $unreadcount,
		);

		return $response;
	}


}