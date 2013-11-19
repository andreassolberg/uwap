<?php


class Notifications {

	
	protected $feed, $parents;
	protected $notifications = array();

	public function __construct($feed, $parents) {
		$this->feed = $feed;
		$this->parents = $parents;

		$this->process();

	}


	protected function process() {

		// $items = array();

		$f = $this->feed->getItems();
		foreach($f AS $item) {

			if ($item->has('inresponseto')) {

				$id = $item->get('inresponseto');
				if (!isset($this->notifications[$id])) {
					$this->notifications[$id] = new Notification();					
				}
				$this->notifications[$id]->responses[] = $item;

			} else {

				$id = $item->get('id');
				if (!isset($this->notifications[$id])) {
					$this->notifications[$id] = new Notification();					
				}
				$this->notifications[$id]->item = $item;
				$this->notifications[$id]->debut = true;

			}

		}

		// print_r($this->notifications); exit;

		if ($this->parents) {
			$p = $this->parents->getItems();

			foreach($p AS $item) {
				$id = $item->get('id');
				if (!isset($this->notifications[$id])) {
					continue;
				}
				$this->notifications[$id]->item = $item;
			}
		}


	}

	public function getJSON() {

		$res = array(
			'items' => array(),
			'groups' => array(),
		);
		foreach($this->notifications AS $n) {
			$res['items'][] = $n->getJSON();
			$groups = $n->getGroups();
			if ($groups !== null) {
				foreach($groups AS $g) {
					if (!isset($res['groups'][$g])) {
						$res['groups'][$g] = 0;
					}
					$res['groups'][$g]++;
				}
			}
		}
		return $res;

	}	


}
