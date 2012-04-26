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
	$auth->authenticate();
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


// 
// if (!$as->isAuthenticated()) {
// 	SimpleSAML_Utilities::redirect('http://app.bridge.uninett.no/login', array(
// 		'return' => SimpleSAML_Utilities::getSelfURL()
// 	));
// }



if (!empty($_REQUEST['return'])) {
	SimpleSAML_Utilities::redirect($_REQUEST['return']);
} else {
	
	echo '<h1>No redirect specified.</h1>';
	
	print_r($attributes);	
}



