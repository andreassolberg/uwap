<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/lib/external/swift/swift_required.php');


class NotificationPost {

	protected $notifications, $mailto, $message;

	function __construct(Notifications $notifications, $mailto) {
		$this->notifications = $notifications;
		$this->mailto = $mailto;

		$this->process();
	}



	function getResponse($summary, $item) {

		$html = '';
		$classes = array('item', 'basicItem');
		if(isset($item['promoted']) && $item['promoted']) {
			$classes[] = 'promoted';
		}



		// if (item.user) {
		// 	item.user.profileimg = UWAP.utils.getEngineURL('/api/media/user/' + item.user.a);
		// }
		// if (item.client) {
		// 	item.client.profileimg = UWAP.utils.getEngineURL('/api/media/logo/client/' + item.client['client_id']);
		// }


		if (isset($item['user'])) {
			$html .= '<img class="profileimg img-polaroid" src="https://core.uwap.org/api/media/user/' . $item['user']['a'] . '" />';
		}
		if (isset($item['client'])) {
			$html .= '<img class="profileimg img-polaroid" src="https://core.uwap.org/api/media/logo/client/' . $item['client']['client_id'] . '" />';
		}


		if (isset($summary['summary'])) {
			$html .= '<p>' . $summary['summary'] . '</p>';
		}

		$html .= '<span style="margin-right: 2em" class=""><i class="glyphicon glyphicon-share-alt"></i><a target="_blank" href="https://feed.uwap.org/#!/item/' . $item['id'] . '">Open item at UWAP</a></span> ' ;

		$footer = '';

		if (isset($item['author'])) {
			$footer .= '<span><i class=" glyphicon glyphicon-user"></i> ' . $item['author'] . '</span>';
		}

		if (isset($item['user'])) {
			$footer .= '<span><i class=" glyphicon glyphicon-user"></i> ' . $item['user']['name'] . '</span>';
		}

		if (isset($item['client'])) {
			$footer .= '<span><i class=" glyphicon glyphicon-briefcase"></i> ' . $item['client']['client_name'] . '</span>';
		}


		if (isset($item['ts'])) {
			$footer .= '<span class=""><i class=" glyphicon glyphicon-time"></i> ' . date('D, d M H:i:s', floor($item['ts']/1000))  .  '</span>';
		}


		$html .= '<div class="footer">'  . $footer . '</div>';



		$html = '<div id="${id}" class="' . join(' ', $classes) . '">' . $html . '</div>';

		return $html;
	}

	function getPost($item) {
		$html = '';


		$baseurl = GlobalConfig::getBaseURL();

		// print_r($item); exit;

		$classes = array('item', 'basicItem');
		if(isset($item['promoted']) && $item['promoted']) {
			$classes[] = 'promoted';
		}

		// print_r($item); exit;

		// if (item.user) {
		// 	item.user.profileimg = UWAP.utils.getEngineURL('/api/media/user/' + item.user.a);
		// }
		// if (item.client) {
		// 	item.client.profileimg = UWAP.utils.getEngineURL('/api/media/logo/client/' + item.client['client_id']);
		// }


		if (isset($item['user'])) {
			$html .= '<img class="profileimg img-polaroid" src="https://core.uwap.org/api/media/user/' . $item['user']['a'] . '" />';
		}
		if (isset($item['client'])) {
			$html .= '<img class="profileimg img-polaroid" src="https://core.uwap.org/api/media/logo/client/' . $item['client']['client_id'] . '" />';
		}

		if (isset($item['title'])) {
			$html .= '<h4>' . $item['title'] . '</h4>';
		}

		if (isset($item['message'])) {
			$html .= '<p>' . $item['message'] . '</p>';
		}

		if (isset($item['thumbnail'])) {
			$html .= '<div><img class="thumb" src="' . $item['thumbnail'] . '" /></div>';
		}

		if (isset($item['links'])) {
			$l = '';
			foreach($item['links'] AS $link) {
				$l .= '<span style="margin-right: 2em" class=""><i class="icon-share-alt"></i><a target="_blank" href="' . $link['href'] . '">' . $link['text'] . '</a></span> ';
			}
			
			$html .= $l;
		}


		$html .= '<span style="margin-right: 2em" class=""><i class="icon-share-alt"></i><a target="_blank" href="https://feed.uwap.org/#!/item/' . $item['id'] . '">Open item at UWAP</a></span> ' ;

		$footer = '';

		if (isset($item['author'])) {
			$footer .= '<span><i class=" icon-user"></i> ' . $item['author'] . '</span>';
		}

		if (isset($item['user'])) {
			$footer .= '<span><i class=" icon-user"></i> ' . $item['user']['name'] . '</span>';
		}

		if (isset($item['client'])) {
			$footer .= '<span><i class=" icon-briefcase"></i> ' . $item['client']['client_name'] . '</span>';
		}

		if (isset($item['groupnames']) ) {
			foreach($item['groupnames'] AS $group) {
				$footer .= '<span class="label">' . $group . '</span> ';
			}
		}

		if (isset($item['public']) && $item['public']) {
			$footer .= '<span class="label label-inverse"><i class="icon-eye-open icon-white"></i> Public</span>';
		}

		if (isset($item['promoted']) && $item['promoted']) {
			$footer .= '<span class="label label-inverse"><i class="icon-eye-open icon-white"></i> Promoted</span>';
		}

		if (isset($item['ts'])) {
			$footer .= '<span class=""><i class=" icon-time"></i> ' . date('D, d M H:i:s', floor($item['ts']/1000))  .  '</span>';
		}


		$html .= '<div class="footer">'  . $footer . '</div>';



		$html = '<div id="${id}" class="' . join(' ', $classes) . '">' . $html . '</div>';

		return $html;
	}

	function getItem($item) {
		// print_r($item['ref']); exit;

		// if (isset($item['ref']['inresponseto'])) {
		// 	// return '<div class="basicItem">' . $item['summary'] . '</div>';
		// 	return $this->getResponse($item, $item['ref']);
		// } else {
		// 	return $this->getPost($item['ref']);
		// }

		return $this->getPost($item);

		
	} 


	function process() {

		$this->message = ''; 

		$nots = $this->notifications->get();



		foreach($nots AS $notification) {


			$this->message .= $notification->getHTML();
			// '<div class="basicItem">' . $item['summary'] . '</div>';

		}


		$this->message = '<div class="feedcontainer">' . $this->message . '</div>';


	}


	function getHTML() {
		$m = new Mailer($this->mailto);
		$m->setBody($this->message);
		return $m->getHTML();
	}

	function send() {
		$m = new Mailer($this->mailto);
		$m->setBody($this->message);
		$m->send();


	}

	
}