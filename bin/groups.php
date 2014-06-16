#!/usr/bin/env php
<?php

require(dirname(dirname(__FILE__)) . '/common/lib/autoload.php');


$command = new Commando\Command();


$command->option()
    ->require()
    ->describedAs('Command to run: showuser, showgroups');

$command->option('u')
	->aka('user')
	->describedAs('The userid of the current user.');



if (!empty($command['user'])) {
	$directory = new UserDirectory();
	$search = $directory->lookupKey($command['user']);
	if (count($search) < 0) {
		echo "Did not find any results matching this userid", PHP_EOL;
	} else if (count($search) > 1) {
		echo "Found multiple results matching this userid. Showing the first match", PHP_EOL;
	}
	$user = $search[0];
}


if ($command[0] === 'user') {

	if (empty($command['user'])) throw new Exception('Userid not specified');
	echo "Looking up user {$command['user']}.", PHP_EOL;
	echo json_encode($user->getJSON(), JSON_PRETTY_PRINT) ;
}

if ($command[0] === 'groups') {

	if (empty($command['user'])) throw new Exception('Userid not specified');
	$groupconnector = new GroupConnector($user);
	$groupResponse = $groupconnector->getGroupsListResponse();
	$response = $groupResponse->getJSON();
	echo json_encode($response, JSON_PRETTY_PRINT) ;
}

