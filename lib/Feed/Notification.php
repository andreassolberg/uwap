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


	function getHTML() {

		$html = '';
		$baseurl = GlobalConfig::getBaseURL();
		// print_r($this);
		// 
		// 
		$classes = array('item', 'basicItem');
		if(isset($item['promoted']) && $item['promoted']) {
			$classes[] = 'promoted';
		}

		if (isset($this->item->user) && $this->item->user->has('a')) {
			// if (!$this->item->user->has('a')) {
			// 	print_r($this->item->user); exit;
			// }
			$html .= '<img class="profileimg img-polaroid" src="' . $baseurl . 'api/media/user/' . $this->item->user->get('a') . '" />';
		} else  if (isset($this->item->client)) {
			$html .= '<img class="profileimg img-polaroid" src="' . $baseurl . 'api/media/logo/app/' . $this->item->client->get('id') . '" />';
		}


		if ($this->debut) {
			$html .= '<p style="font-weight: bold; font-size: 90%">' . $this->item->getSummary() . '</p>';	
		}

		if (!empty($this->responses)) {
			$html .= '<p style="font-size: 90%">' . count($this->responses) . ' new responses</p>';	
		}

		if ($this->debut) {

			// echo '<pre>'; print_r($this); echo '</pre>';

			// if (isset($summary['summary'])) {
			// 	$html .= '<p>' . $summary['summary'] . '</p>';
			// }
		}

		if ($this->item->has('title')) {
			$html .= '<h4>' . $this->item->get('title') . '</h4>';
		}

		if ($this->item->has('message')) {
			$html .= '<p>' . $this->item->get('message') . '</p>';
		}


		$html .= '<span style="margin-right: 2em" class=""><span class="glyphicon glyphicon-share-alt"></span> <a target="_blank" href="https://feed.uwap.org/#!/item/' . $this->item->get('id') . '">Open item at UWAP</a></span> ' ;



		$footer = '';

		// if (isset($item['author'])) {
		// 	$footer .= '<span><i class=" icon-user"></i> ' . $item['author'] . '</span>';
		// }

		if (isset($this->item->user)) {
			$footer .= '<span><span class=" glyphicon glyphicon-user"></span> ' . $this->item->user->get('name') . '</span> ';
		}

		if (isset($this->item->client)) {
			$footer .= '<span><span class=" glyphicon glyphicon-briefcase"></span> ' . $this->item->client->get('name') . '</span> ';
		}


		if ($this->item->has('ts')) {
			$footer .= '<span class=""><span class=" glyphicon glyphicon-time"></span> ' . date('D, d M H:i:s', floor($this->item->get('ts')/1000))  .  '</span> ';
		}


		$html .= '<div class="footer">'  . $footer . '</div>';

		$id = $this->item->get('id');
		$html = '<div id="${id}" class="' . join(' ', $classes) . '">' . $html . '</div>';

		// print_r($this); echo($html); exit;

		return $html;
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