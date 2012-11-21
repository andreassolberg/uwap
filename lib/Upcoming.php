<?php


class Upcoming {


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


	function read($selector, $ago = 2592000000, $ref = false) { // 30 days

		// queryListUser($collection, $userid, $groups, $criteria = array(), $fields = array(), $options = array() ) {

		$results = array();
		$feed = new Feed($this->userid, null, $this->groups, $this->subscriptions);

		$selector['future'] = true;
		$selector['class'] = array('event');

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



		$response = array(
			'items' => $entries['items'],
			'range' => $entries['range'],
			// 'unreadcount' => $unreadcount,
		);

		return $response;
	}


}