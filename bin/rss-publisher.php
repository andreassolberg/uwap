#!/usr/bin/env php
<?php


/**
 * This client may authenticate as a client, and update eduFEED with data.
 */

require(dirname(__FILE__) . '/lib/Client.php');
require(dirname(__FILE__) . '/lib/RSS.php');


// $url = "https://www.uninett.no/nyhetsoversikt/feed";
// $url = 'https://www.uninett.no/rss.xml';

$filename = dirname(dirname(__FILE__)) . '/config/rss-publisher.json';
$raw = file_get_contents($filename);


$config = json_decode($raw, true);

if (!is_array($config)) {
	echo "Could not parse config file: " . $filename . "\n";
	exit;
}


function process($c) {
	echo "====> Processing " . $c['client_id'] . "\n";
	$client = new Client($c['client_id'], $c['secret']);
	// $res = $client->oauth_http('http://core.app.bridge.uninett.no/api/feed');

	$rss = new RSS($c['url']);
	$entries = $rss->get();
	foreach($entries AS $entry) {
		$entry['audience'] = $c['audience'];

		// $entry['public'] = true;
		echo "Posting › " . $c['client_id'] . " ›››\n";
		print_r($entry);
		echo json_encode($entry); 
		echo "\n\n\n"; 
		$res = $client->oauth_http('http://core.app.bridge.uninett.no/api/feed/post', $entry);
		// exit;
	}
}


// process($config[2]);

foreach($config AS $c) {

	process($c);

}





