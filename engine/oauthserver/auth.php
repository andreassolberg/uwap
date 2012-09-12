<?php

/*
 * This script is the app data proxy. 
 * 
 * 		appid.uwap.org/oauth/auth.php
 *
 */

require_once('../../lib/autoload.php');



try {
	$config = new Config();

	error_log('auth.php endpoint');
	
	$storage = new So_StorageServerUWAP();
	$server = new So_Server($storage);
	$server->runInfo();
	$server->runToken();

	// echo '<pre>'; print_r($auth->getRealUserID()); echo '</pre>';
	
	error_log('Starting authentication... The user might be redirected away for authentication.');

	$auth = new Auth();
	if (!$auth->check()) {
		SimpleSAML_Utilities::redirect("https://" . Config::hostname() . "/login", array(
			'return' => SimpleSAML_Utilities::selfURL(),
			"app" => $config->getID()
		));	
	}
	// $userdata = $auth->getUserdata();

	$verifier = $auth->getVerifier();

	if (isset($_REQUEST["verifier"])) {
		if ($verifier !== $_REQUEST["verifier"]) {
			throw new Exception("Invalid verifier code.");
		}
		// setAuthorization($client_id, $userid, $scopes) ..

		// echo "about to set authorization: ";
		// print_r($config->getID());
		// print_r($auth->getRealUserID());
		
		// exit;

		$scopes = null;
		if (!empty($_REQUEST['scopes'])) {
			$scopes = explode(',', $_REQUEST['scopes']);
		}

		// echo 'about to set scopes '; print_r($scopes);exit;

		// TODO Add support for deciding which scope to use...
		// TODO additional verification that the client_id is not modified by the user.
		$server->setAuthorization($_REQUEST["client_id"], $auth->getRealUserID(), $scopes);
	}
	
	try {
		$server->runAuthenticated($auth->getRealUserID());	
	} catch (So_AuthorizationRequied $e) {
	
		$postdata = array();
		// $postdata["app"] = $_REQUEST["app"];

		$postdata = $_REQUEST;

		$postdata["return"] = SimpleSAML_Utilities::selfURL();
		$postdata["verifier"] = $verifier;
		$posturl = SimpleSAML_Utilities::selfURLNoQuery();

		if (!empty($e->scopes)) {
			$postdata["scopes"] = join(',', $e->scopes);
			$scopes = $e->scopes;
		}

		$data = $config->getConfig();
		
		// $data["verifier"] = 
		// $data["verifierurl"] = SimpleSAML_Utilities::addURLparameter(
		// 	SimpleSAML_Utilities::selfURL(),
		// 	array(
		// 		"verifier" => $data["verifier"]
		// 	)
		// );

		//SimpleSAML_Utilities::redirect($_REQUEST['return']);

		$user = $auth->getUserdata();

		// echo '<pre>authenticated:';
		// echo(var_export($auth->authenticated(), true));
		// exit;

		// print_r($data);

		// echo "About to show authorization grant page...<pre>";
		// print_r($postdata);
		// exit;


		header("Content-Type: text/html; charset: utf-8");
		require_once("../../templates/oauthgrant.php"); exit;

	


	}
	
	
	throw new Exception('404 Router could not determine any known endpoint from the url.');
	
} catch(Exception $e) {
	
	error_log('Error on OAuth Provider: '  . $e->getMessage());
	echo '<pre>';
	print_r($e);
	echo '</pre>';
}
