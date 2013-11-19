<?php


class Notification {

	public $debut = false;
	public $item = null;
	public $responses = array();

	function __construct() {



	}

	function getGroups() {

		if (!$this->item->has('audience')) {
			return null;
		}

		$audience = $this->item->get('audience');
		if (!$audience['groups']) return null;
		return $audience['groups'];
	}


	function getJSON() {

		$item = array();
		if ($this->debut) {

			$item['summary'] = $this->item->getSummary();
			$item['timestamp'] = $this->item->get('created');

		} else {

			if (count($this->responses) > 1) {
				$item['summary'] = count($this->responses) . ' responded';
			} else {
				$item['summary'] = $this->responses[0]->getSummary();
			}

			$item['timestamp'] = $this->responses[0]->get('created');
			
		}
		$item['item'] = $this->item->getJSON();
		if (!empty($this->responses)) {
			$item['responses'] = array();
			foreach($this->responses AS $r) {
				$item['responses'][] = $r->getJSON();
			}
		}



		return $item;

	}



}