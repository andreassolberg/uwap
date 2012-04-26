<?php

/*
 * This endpoint is where the user is redirected to perform authentication.
 * 
 * 		appid.uwap.org/login
 *
 */

require_once('../lib/autoload.php');

$auth = new Auth();
$config = new Config();


if (!$auth->check()) {
	SimpleSAML_Utilities::redirect("http://app.bridge.uninett.no/login", array(
		'return' => SimpleSAML_Utilities::selfURL(),
		"app" => $config->getID()
	));	
}





if (empty($_REQUEST['return'])) {
	
	$attributes = $as->getAttributes();	
	print_r($attributes);
	exit;
}

SimpleSAML_Utilities::redirect($_REQUEST['return']);

