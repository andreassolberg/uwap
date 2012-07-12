<?php

/*
 * This file is part of Solberg-OAuth
 * Read more here: https://github.com/andreassolberg/solberg-oauth
 */



// Loading SimpleSAMLphp for doing authentication at the OAuth provider.
// Read more about SimpleSAMLphp here: http://simplesamlphp.org/
require_once('/var/simplesamlphp-foodle/lib/_autoload.php');



// Load the OAuth library
require_once('../../lib/soauth.php');
require_once('../../lib/sostoragemysql.php');



try {
	
	$as = new SimpleSAML_Auth_Simple('saml');
	
	$server = new So_Server();
	$server->runInfo();
	$server->runToken();
	
	error_log('Starting authentication... The user might be redirected away for authentication.');
	
	/*
	 * Use SimpleSAMLphp to authenticate using a given authentication source 
	 * (using example-userpass for simplicity in this demo)
	 * Then get the attributes, and assume an attribute 'uid' identifies the user.
	 */
	$as->requireAuth();
	$attributes = $as->getAttributes();
	$uid = $attributes['uid'][0];
	
	// Run the part of the provider that requires the user to be authenticated.
	$server->runAuthenticated($uid);
	
	throw new Exception('404 Router could not determine any known endpoint from the url.');
	
} catch(Exception $e) {
	
	error_log('Error on OAuth Provider: '  . $e->getMessage());
	echo '<pre>';
	print_r($e);
	echo '</pre>';
}
