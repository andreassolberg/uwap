<?php


class Notifications {


	protected $userid, $groups, $store;

	function __construct($userid, $groups) {
		$this->userid = $userid;
		$this->groups = $groups;
		$this->store = new UWAPStore;
	}


	public function itemStr($item) {

		$str = '';

		if (isset($item['user'])) {

			$str .= $item['user']['name'] . ' ';

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


	function read($ago = 2592000) { // 30 days

		$results = array();

		$feed = new Feed($this->userid, null, $this->groups);
		$from = time() - $ago; // (3600*24*4); // 30 days ago.
		$entries = $feed->read(array('from' => $from));

		// echo "===============> QUERY QYERY " . $this->userid . " " . json_encode($this->groups) . "\n\n";

		// print_r($entries['items']);

		foreach($entries['items'] AS $entry) {

			$ne = array('id' => $entry['id'], 'ts' => $entry['ts']);
			$ne['summary'] = $this->itemStr($entry);

			$results[] = $ne;

		}

		// print_r($results); 

		return $results;
	}


}