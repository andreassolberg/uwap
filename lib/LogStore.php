<?php

class LogStore {

	protected $store;

	public function __construct() {
		$this->store = new UWAPStore;
	}


	public static function processFilter($filters = array(), $applications = null) {

		// error_log("----------------------");
		// error_log('Filter from ' . json_encode($filters));

		$query = array();
		foreach($filters AS $filter) {

			foreach($filter AS $attr => $def) {

				foreach($def AS $value => $include) {

					// error_log("Processing [" . $attr . ", " . var_export($value, true) . ", " . $include) ;

					if (!array_key_exists($attr, $query)) $query[$attr] = array();

					if ($include == 1) {
						if (!array_key_exists('$in', $query[$attr])) {
							$query[$attr]['$in'] = array();
						}
						$query[$attr]['$in'][] = $value;
					} else if ($include == 0) {
						if (!array_key_exists('$nin', $query[$attr])) {
							$query[$attr]['$nin'] = array();
						}
						$query[$attr]['$nin'][] = $value;
					} else {
						// error_log("Invalid value : " . var_export($value, true));
					}

				}

			}

		}
		// error_log('Filter to1 ' . json_encode($query));
		if (!empty($applications)) {
			// error_log('Processing apps ' . json_encode($applications));
			if (isset($query['subid']) && isset($query['subid']['$in'])) {

				$query['subid']['$in'] = array_intersect($query['subid']['$in'], $applications);

			} else {

				if (!isset($query['subid'])) $query['subid'] = array();
				if (!isset($query['subid']['$in'])) $query['subid']['$in'] = $applications;

			}

		}
		// error_log('Filter to2 ' . json_encode($query));

		return $query;
	}


	public function getLogs($after, $query = array(), $max = 100) {

		


		$query['time'] = array(
			'$gt' => $after,
		);

		$options = array(
			'limit' => $max,
			'sort' => array('time' => -1),
		);

		// UWAPLogger::error('log', 'Query logs', array(
		// 	'query' => $query,
		// 	'options' => $options,
		// ));

		// error_log("Query " . json_encode($query

		// 	));

		$res = $this->store->queryList('log', $query, array(), $options);
		$res2 = array();
		$result = array(
			'data' => null,
		);
		if (count($res) > 0) {

			$lastTimestamp = number_format($res[count($res)-1]['time'], 6, '.', '');

			foreach($res AS $r) {
				if (number_format($r['time'], 4, '.', '') !== $lastTimestamp) {
					$res2[] = $r;
				}
			}

			$result['to'] = number_format($res2[0]['time'], 6, '.', '');
			$result['from'] = number_format($res2[count($res2)-1]['time'], 6, '.', '');
			$result['data'] = $res2;
		}
		return $result;
	}

}
