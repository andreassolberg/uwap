<?php


// DEPRECATED: Will be deleted when completely migrated to OAuth implcit flow for user authentication.


/*
 * This endpoint is where the user is redirected to perform authentication.
 * 
 * 		uwap.org/login
 *
 */

require_once('../../lib/autoload.php');

UWAPLogger::info('auth', 'User is accessing core authentication endpint core.uwap.org/login .');

if (empty($_REQUEST['app'])) {
	UWAPLogger::error('auth', "missing required query string parameter: app");	
	throw new Exception("missing required query string parameter: app");
}


$app = $_REQUEST['app'];
$auth = new Auth($app);



if (!$auth->authenticated() ) {

	UWAPLogger::debug('auth', "User is not authenticated");

	if (isset($_REQUEST['passive']) && $_REQUEST['passive'] === 'true') {

		if (!empty($_REQUEST['SimpleSAML_Auth_State_exceptionId'])) {

			UWAPLogger::info('auth', "Received an SAML Error as response to a passive request. This is normal. Returning to", 
				$_REQUEST['return']);
			SimpleSAML_Utilities::redirect($_REQUEST['return'], array(
				"fail" => "true",
			));
		}

		UWAPLogger::info('auth', "Initating a passive authentication request using SAML.");
		$auth->authenticatePassive();
	} else {
		UWAPLogger::info('auth', "Initating a normal authentication request using SAML.");
		$auth->authenticate();
	}
}

UWAPLogger::info('auth', "User is authenticate. Now ready to check authorization.");

if (!$auth->authorized()) {

	$verifier = $auth->getVerifier();

	if (isset($_REQUEST["verifier"])) {

		
		if ($verifier !== $_REQUEST["verifier"]) {
			UWAPLogger::error('auth', "User provided a bad verifier code. This should not happen.");
			throw new Exception("Invalid verifier code.");
		}
		UWAPLogger::debug('auth', "User provided a valid verifier code. Storing authorization.");
		$auth->authorize();

	} else {

		

		$postdata = array();
		$postdata["app"] = $_REQUEST["app"];
		$postdata["return"] = $_REQUEST["return"];
		$postdata["verifier"] = $verifier;
		$posturl = SimpleSAML_Utilities::selfURLNoQuery();
		
		$user = $auth->getUserdata();

		UWAPLogger::info('auth', "Asking the user for authorization. Postdata:", $postdata);

		header("Content-Type: text/html; charset: utf-8");
		require_once("../templates/consent.php"); exit;

	}

} 


if (!$auth->authorized()) {
	UWAPLogger::error('auth', "User is not authorizated.");
	echo '<h1>FAILED TO AUTHROIZE</h1><pre></pre>';
	echo htmlspecialchars($_REQUEST['return']); 
	exit;
}

if (!$auth->authenticated()) {
	UWAPLogger::error('auth', "User is not authenticated.");
	echo '<h1>FAILED TO authenticated</h1><pre></pre>';
	echo htmlspecialchars($_REQUEST['return']); 
	exit;
}



if (!empty($_REQUEST['return'])) {
	// echo '<pre>About to return to : ' . $_REQUEST['return']; exit;
	UWAPLogger::info('auth', "User is authenticated and authorized, and we now return to", $_REQUEST['return']);
	SimpleSAML_Utilities::redirect($_REQUEST['return']);
} else {
	UWAPLogger::error('auth', "User is authenticated and authorized, but we do not have a return url.");
	echo '<h1>No redirect specified.</h1>';
}



