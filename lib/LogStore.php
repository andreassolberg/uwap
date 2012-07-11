<?php

class LogStore {

	protected $store;

	public function __construct() {
		$this->store = new UWAPStore;
	}

	public function getLogs($after, $max = 100) {

		$query = array(
			'time' => array(
				'$gt' => $after,
			),
		);

		error_log("Query:  " . json_encode($query));

		$res = $this->store->queryList('log', $query);
		$result = array(
			'data' => $res,
		);
		if (count($res) > 0) {
			$result['to'] = $res[0]['time'];
			$result['from'] = $res[count($result)-1]['time'];
		}
		return $result;
	}

}