<?php

/**
 * This endpoint is where the user is redirected to perform authentication.
 * 
 * 		appid.uwap.org/login
 * 		
 * 	This endpoint takes two parameters:
 * 	
 * 		passive 	true or false. Should passive authentication be used.
 * 		return 		The return url to return to after authentication is complete.
 *
 *	If passive=true and return is unset, this page will send a response to parent frame, 
 *	assuming that this endpoint is loaded in an hidden iFrame.
 */

session_start();
require_once('../lib/autoload.php');

$auth = new Auth();
$config = new Config();


function parentMessage() {

	
	
}


if (!$auth->check()) {

	if (isset($_REQUEST['passive']) && $_REQUEST['passive'] === 'true') {

		if (isset($_REQUEST['fail']) && $_REQUEST['fail'] === 'true') {

			ParentMessenger::send(array(
				"type" => "passiveAuth",
				"status" =>"fail",
				"message" => "Failed to perform passive authentication. (tried now)",
			));		

		// User has attempted to login using passive authentication recently, will not try again.
		} else if (isset($_SESSION['passiveAttempt']) && $_SESSION['passiveAttempt'] > (time() - 60)) {

			error_log("passiveAttempt attempted recently, not retrying within one minute since last time.");
			// echo "FAILED (tried before)"; exit;

			ParentMessenger::send(array(
				"type" => "passiveAuth",
				"status" =>"fail",
				"message" => "Failed to perform passive authentication. (tried before, wont retry)",
			));

		} else {
			$_SESSION['passiveAttempt'] = time();
			// echo '<pre>passiveattempt is ' . $_SESSION['passiveAttempt'];
			// exit;
			
			SimpleSAML_Utilities::redirect('https://core.' . Config::hostname() . '/login', array(
				'return' => SimpleSAML_Utilities::selfURL(),
				"app" => $config->getID(),
				"passive" => "true",
			));	

		}

	} else {

		SimpleSAML_Utilities::redirect('https://core.' . Config::hostname() . '/login', array(
			'return' => SimpleSAML_Utilities::selfURL(),
			"app" => $config->getID()
		));	
	}

}





if (empty($_REQUEST['return'])) {

	if (isset($_REQUEST['passive']) && $_REQUEST['passive'] === 'true') {
		ParentMessenger::send(array(
			"type" => "passiveAuth",
			"status" =>"success",
			"message" => "Succeeded to perform authentication. Do a new check AJAX call to get user data.",
		));	
		exit;
	}

	echo '<pre>You are authenticated as this user:'; print_r($auth->getUserdata()); exit;
	throw new Exception('Return parameter was missing to /login endpoint.');
}

SimpleSAML_Utilities::redirect($_REQUEST['return']);

