<?php

class RSS {
	protected $url;
	public function __construct($url) {
		$this->url = $url;
	}

	public function get() {
		$rss = simplexml_load_file($this->url);
		$p = array();
		if ($rss) {
			// echo '<h1>'.$rss->channel->title.'</h1>';
			// echo '<li>'.$rss->channel->pubDate.'</li>';
			$items = $rss->channel->item;
			foreach($items as $item){
				$title = $item->title;
				$link = $item->link;
				$published_on = $item->pubDate;
				$description = substr(strip_tags($item->description), 0, 300);

				$ts = 1000*strtotime((string) $published_on);

				// echo "TS " . $ts . "\n";
				// echo "Ts " . (string) $published_on . "\n";

				$p[] = array(
					"title" => (string) $title,
					"message" => (string) $description,
					"ts" => $ts,
					"links" => array(
						array(
							"href" => (string) $link,
							"text" => "read more",
						)
					),
					"oid" => sha1($link),
				);
			}
		}
		return $p;

	}


}