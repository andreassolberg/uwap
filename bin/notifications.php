#!/usr/bin/env php
<?php


/**
 * This client may authenticate as a client, and update eduFEED with data.
 */

// require(dirname(__FILE__) . '/lib/Client.php');
// require(dirname(__FILE__) . '/lib/RSS.php');



require(dirname(dirname(__FILE__)) . '/lib/autoload.php');


$filename = dirname(dirname(__FILE__)) . '/config/notifications.json';
$raw = file_get_contents($filename);
$config = json_decode($raw, true);

if (!is_array($config)) {
	echo "Could not parse config file: " . $filename . "\n";
	exit;
}

$store = new UWAPStore();

$query = array(
	"client_id" => 'app_feed'
);
$authz = $store->queryList('oauth2-server-authorization', $query);

foreach($authz AS $a) {

	$testusers = array('andreas@uninett.no', 'armaz@uninett.no', 'anders@uninett.no', 'simon@uninett.no', 'hallen@uninett.no', 'navjord@uninett.no');
	// $testusers = array('andreas@uninett.no');


	$user = $store->queryOne('users', array('userid' => $a['userid']));
	$userid = $user['userid'];
	// if ($userid !== 'andreas@uninett.no') continue;
	if (!in_array($userid, $testusers)) {
		echo "  › Skipping user " . $userid . "\n\n";
		continue;
	} 

	echo "   [Processing " . $userid . " >\n";


	$no = new Notifications($userid, $user['groups']);
	$response = $no->read(array(), 3600);
	$entries = $response['items'];

	if (empty($entries)) {
		echo "No updates...\n\n";
		continue;
	}

	foreach($entries AS $entry) {
		// echo "   Entry › " . json_encode($entry) . "\n\n";
		echo " › " . $entry['summary'] . "\n";
	}

	echo "   [Sending mail to " . $user['mail'] . ".\n\n";


	$m = new Mailer($user['mail']);
	$m->setNotifications($entries);
	$m->send();


	// echo "User groups "; print_r($user['groups']);

	// $feed = new Feed($userid, null, $user['groups']);
	// $from = time() - (3600*24*30); // 30 days ago.
	// $entries = $feed->read(array('from' => $from));

	// echo " [Processing " . $user['name'] . " ››› \n";


	// echo "\n";

	// echo "Authorization ›››: "; 
	// print_r($a); 
	// print_r($user);
	// echo "\n\n";
}



echo "____\n";

