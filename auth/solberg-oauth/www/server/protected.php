<?php

/*
 * This file is part of Solberg-OAuth
 * Read more here: https://github.com/andreassolberg/solberg-oauth
 */

// Load the OAuth library
require_once('../../lib/soauth.php');




try {


	$server = new So_Server();
	$token = $server->checkToken();

	if ($token->userid !== 'andreas') throw new Exception('Youre not authorized to access this information.');

	header('Content-Type: application/json');
	echo json_encode(array('poot' => '1', 'userid' => $token->userid));


} catch(Exception $e) {

	error_log('Error on OAuth Provider: '  . $e->getMessage());
	echo '<pre>';
	print_r($e);
	echo '</pre>';
}




