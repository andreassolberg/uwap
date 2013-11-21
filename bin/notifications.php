#!/usr/bin/env php
<?php


/**
 * This client may authenticate as a client, and update eduFEED with data.
 */

// require(dirname(__FILE__) . '/lib/Client.php');


require(dirname(__FILE__) . '/lib/NotificationPost.php');
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
	"client_id" => 'feed'
);
$authz = $store->queryList('oauth2-server-authorization', $query);
$client = Client::getByID('feed');

// print_r($authz); exit;


foreach($authz AS $a) {

	// $testusers = array('andreas@uninett.no', 'armaz@uninett.no', 'anders@uninett.no', 'simon@uninett.no', 'hallen@uninett.no', 'navjord@uninett.no');
	// $testusers = array('andreas@uninett.no', 'anders@uninett.no', 'simon@uninett.no', 'hallen@uninett.no', 'navjord@uninett.no', 'bjorn@uninett.no');

	// $testusers = array('andreas@uninett.no');

	$user = User::getByID($a['userid']);
	$userid = $user->get('userid');
	if (!$user->isMemberOf('uwap:grp-ah:7ea1c555-583c-4a1f-9ae2-1273b0c66ebc')) {
		echo "  › Skipping user " . $userid . " (not member of early adopter group)\n";
		continue;
	}	

	// if ($userid !== 'andreas@uninett.no') continue;
	// if (!in_array($userid, $testusers)) {
	// 	echo "  › Skipping user " . $userid . "\n\n";
	// 	continue;
	// } 




	echo "   [Processing " . $userid . " >\n";
	// echo "Memeber of groups"; print_r($user['groups']); print_r($user['subscriptions']);
	// echo json_encode($user->getJSON(), 4);


	$feedReader = new FeedReader($client, $user);


	// $notifications = $feedReader->readNotifications(array(), 3600000);
	$notifications = $feedReader->readNotifications(array(), 3600000);

	$entries = $notifications->getJSON();

	// print_r($entries); exit;

	$ids = array();
	// $feedReader->markNotificationsRead($ids);


	// $feed = new Feed($userid, $user['groups'], $user['subscriptions']);
	// $no = new Notifications($userid, $user['groups'], $user['subscriptions']);
	// $response = $no->read(array(), 3600000, true); // 3600000 is one hour. 432000000 is five days.
	// $entries = $response['items'];

	// 	exit;

	if (empty($entries)) {
		echo "No updates...\n\n";
		continue;
	}

	foreach($entries['items'] AS $k => $entry) {
		// echo "   Entry › " . json_encode($entry) . "\n\n";
		echo " › " . $entry['summary'] . "\n";

		// $entries[$k]['ref'] = $feed->read(array('id' => $entry['id']));
		// print_r($entries[$k]); exit;
	}

	if(count($entries['items']) < 1) {
		echo "No updates for this user\n\n"; continue;
	}

	if (!$user->has('mail')) {
		echo "Skipping user because mail field is missing.\n";
		continue;
	}

	echo "   [Sending mail to " . $user->get('mail') . ".\n\n";

	$np = new NotificationPost($notifications, $user->get('mail'), $user);
	// $np = new NotificationPost($notifications, 'andreas@uninett.no', $user);
	$np->send();



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

