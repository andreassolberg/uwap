#!/usr/bin/env php
<?php


$uwapBaseDir = dirname(dirname(__FILE__));
$autoload = $uwapBaseDir . '/lib/autoload.php';

# error_log("Checking basedir: " . $autoload); exit;

/* Add library autoloader. */
require_once($autoload);

UWAPLogger::init(array('logLevel' => 3));
UWAPLogger::info('bin-update', 'Running command line update.php cron script.');



$program = array_shift($argv);
if (count($argv) > 0) {
	echo "Wrong number of parameters. Run:   " . $program . " .... TBD\n"; 
	exit(2);
}


// $schema = SCIMSchemaDirectory::get('urn:scim:schemas:core:2.0:Group');
// print_r($schema); exit;



$role = new SCIMResourceRole(json_decode('
{
    "id": "s87d6fds8-sdifusd8f7-sdfuysdifu",
    "displayName": "UNINETT",
    "sourceID": "voot:uninett:fs",
    "public": true,
    "vootRole": "....",
    "notBefore": "2013",
    "notAfter": "",
    "groupActive": true
}
', true));





$s = new SCIMResourceGroup(json_decode('
{
    "id": "s87d6fds8-sdifusd8f7-sdfuysdifu",
    "displayName": "UNINETT",
    "sourceID": "voot:uninett:fs",
    "public": true,
    "vootRole": "....",
    "notBefore": "2013",
    "notAfter": "",
    "groupActive": true
}
', true));



echo "\n\n----\n";
echo "dump result: \n";
print_r($s->getJSON());



$list = new SCIMListResponse(array($s));
echo "\n\n----\n";
echo "dump list: \n";
print_r($list->getJSON());



// $s->validate();