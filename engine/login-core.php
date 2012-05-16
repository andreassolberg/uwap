<?php

/*
 * This endpoint is where the user is redirected to perform authentication.
 * 
 * 		uwap.org/login
 *
 */

require_once('../lib/autoload.php');

if (empty($_REQUEST['app'])) {
	throw new Exception("missing required query string parameter: app");
}
$app = $_REQUEST['app'];
error_log("App id provided was " . $_REQUEST["app"]);

$auth = new Auth($app);
$config = new Config($app);





if (!$auth->authenticated() ) {
	if (isset($_REQUEST['passive']) && $_REQUEST['passive'] === 'true') {

		if (!empty($_REQUEST['SimpleSAML_Auth_State_exceptionId'])) {
			SimpleSAML_Utilities::redirect($_REQUEST['return'], array(
				"fail" => "true",
			));
		}

		$auth->authenticatePassive();
	} else {
		$auth->authenticate();
	}
}


if (!$auth->authorized()) {

	$verifier = $auth->getVerifier();

	if (isset($_REQUEST["verifier"])) {

		if ($verifier !== $_REQUEST["verifier"]) {
			throw new Exception("Invalid verifier code.");
		}

		$auth->authorize();

	} else {

		$postdata = array();
		$postdata["app"] = $_REQUEST["app"];
		$postdata["return"] = $_REQUEST["return"];
		$postdata["verifier"] = $verifier;
		$posturl = SimpleSAML_Utilities::selfURLNoQuery();

		$data = $config->getConfig();
		
		$user = $auth->getUserdata();

		header("Content-Type: text/html; charset: utf-8");
		require_once("../templates/consent.php"); exit;

	}

} 


if (!$auth->authorized()) {
	echo '<h1>FAILED TO AUTHROIZE</h1><pre>';
	echo htmlspecialchars($_REQUEST['return']); 
	exit;
}

if (!$auth->authenticated()) {
	echo '<h1>FAILED TO authenticated</h1><pre>';
	echo htmlspecialchars($_REQUEST['return']); 
	exit;
}



if (!empty($_REQUEST['return'])) {
	// echo '<pre>About to return to : ' . $_REQUEST['return']; exit;
	SimpleSAML_Utilities::redirect($_REQUEST['return']);
} else {
	
	echo '<h1>No redirect specified.</h1>';
}



