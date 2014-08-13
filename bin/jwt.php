#!/usr/bin/env php
<?php


$uwapBaseDir = dirname(dirname(__FILE__));
$autoload = $uwapBaseDir . '/common/lib/autoload.php';

# error_log("Checking basedir: " . $autoload); exit;

/* Add library autoloader. */
require_once($autoload);

UWAPLogger::init(array('logLevel' => 3));
UWAPLogger::info('bin-update', 'Running command line update.php cron script.');



$command = new Commando\Command();


$command->option()
    ->describedAs('Command to run: default is update.');



$globalconfig = GlobalConfig::getInstance();
$key = $globalconfig->getValue('connect.key', null, true);
$issuer = $globalconfig->getValue('connect.issuer', null, true);

$token = array(
    "iss" => $issuer,
    "aud" => "http://example.com",
    "sub" => "uuid:sldkjfldskjf",
    "iat" => time(),
    "exp" => time() + 3600
);

$jwt = JWT::encode($token, $key);
$decoded = JWT::decode($jwt, $key);

print_r($decoded);

echo("\n- - - \n" . $jwt . "\n- - - \n");

// $command->option('cache-only')
// 	->boolean()
// 	->describedAs('Do not load metadata, only use existing cache.');

// if ($command[0] === 'termcolor') {
// 	phpterm_demo();
// 	exit;
// }



