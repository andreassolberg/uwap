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

$config = Config::getInstance();




if (!$auth->check()) {
	UWAPLogger::info('auth', 'User does not have an app-local session, needs to send the user to the core.');

	if (isset($_REQUEST['passive']) && $_REQUEST['passive'] === 'true') {

		if (isset($_REQUEST['fail']) && $_REQUEST['fail'] === 'true') {

			$message = array(
				"type" => "passiveAuth",
				"status" =>"fail",
				"message" => "Failed to perform passive authentication. (tried now)",
			);
			UWAPLogger::debug('auth', 'Failed to perform passive authentication', $message);
			ParentMessenger::send($message);		

		// User has attempted to login using passive authentication recently, will not try again.
		} else if (isset($_SESSION['passiveAttempt']) && $_SESSION['passiveAttempt'] > (time() - 60)) {

			error_log("passiveAttempt attempted recently, not retrying within one minute since last time.");
			// echo "FAILED (tried before)"; exit;
			$message = array(
				"type" => "passiveAuth",
				"status" =>"fail",
				"message" => "Failed to perform passive authentication. (tried before, wont retry)",
			);
			UWAPLogger::debug('auth', 'Failed to perform passive authentication', $message);
			ParentMessenger::send($message);

		} else {
			$_SESSION['passiveAttempt'] = time();
			// echo '<pre></pre>passiveattempt is ' . $_SESSION['passiveAttempt'];
			// exit;
			UWAPLogger::debug('auth', 'Have not yet tried passive authentication, trying now, and redirects to core.uwap.org/login');
			SimpleSAML_Utilities::redirect(GlobalConfig::scheme() . '://core.' . GlobalConfig::hostname() . '/login', array(
				'return' => SimpleSAML_Utilities::selfURL(),
				"app" => $config->getID(),
				"passive" => "true",
			));	

		}

	} else {
		UWAPLogger::debug('auth', 'About to start authentication, ', $message);
		SimpleSAML_Utilities::redirect(GlobalConfig::scheme() . '://core.' . GlobalConfig::hostname() . '/login', array(
			'return' => SimpleSAML_Utilities::selfURL(),
			"app" => $config->getID()
		));	
	}

}



UWAPLogger::info('auth', 'User does have an app-local session. Now will send back to return (App)');

if (empty($_REQUEST['return'])) {

	if (isset($_REQUEST['passive']) && $_REQUEST['passive'] === 'true') {
		UWAPLogger::debug('auth', 'Return parameter is missing, expecting that this is an iframe passive request.');
		ParentMessenger::send(array(
			"type" => "passiveAuth",
			"status" =>"success",
			"message" => "Succeeded to perform authentication. Do a new check AJAX call to get user data.",
		));	
		exit;
	}

	UWAPLogger::warn('auth', 'Missing [return] parameter, and not a passive request. We-re showing an error to the user');

	// echo '<pre>You are authenticated as this user:'; print_r($auth->getUserdata()); exit;
	throw new Exception('Return parameter was missing to /login endpoint.');
}

UWAPLogger::debug('auth', 'Redirecting the user back to after authentication: ', $_REQUEST['return']);

SimpleSAML_Utilities::redirect($_REQUEST['return']);

