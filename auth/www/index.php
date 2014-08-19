<?php

/*
 * This is the vhost / endpoints that deals with authentication and authorization, issuing OAuth tokens.
 * OAuth and OpenID Connect.
 * 
 * 		auth.uwap.org/*
 *
 */


require_once(dirname(dirname(__FILE__)) . '/lib/autoload.php');


// error_log("File path: " . $BASE);

// require_once($BASE . '/common/lib/autoload.php');
// require_once($BASE . '/auth/lib/autoload.php');

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: HEAD, GET, OPTIONS, POST, DELETE, PATCH");
header("Access-Control-Allow-Headers: Authorization, X-Requested-With, Origin, Accept, Content-Type");
header("Access-Control-Expose-Headers: Authorization, X-Requested-With, Origin, Accept, Content-Type");

$profiling = microtime(true);
// error_log("Time START    :     ======> " . number_format((microtime(true) - $profiling)));


$parameters = null;

try {

	$globalconfig = GlobalConfig::getInstance();

	if (Utils::route('options', '.*', $parameters)) {
		header('Content-Type: application/json; charset=utf-8');
		exit;
	}



	$response = null;

	/**
	 *  The OAuth endpoints on core, typically the OAuth Server endpoints for communication with clients
	 *  using the API.
	 */
	if (Utils::route(false, '^/oauth', $parameters)) {


		$oauth = new OAuth();

		if (Utils::route('post','^/oauth/authorization$', $parameters)) {
			$oauth->processAuthorizationResponse();

		} else if (Utils::route('get', '^/oauth/authorization$', $parameters)) {
			$oauth->authorization();

		} else if (Utils::route(false, '^/oauth/token$', $parameters)) {
			$oauth->token();

		} else {
			throw new Exception('Invalid request');
		}



	/*
	 *	Testing authentication using the auth libs
	 *	Both API auth and 
	 */
	} else if  (Utils::route('get', '^/providerconfig$', $parameters)) {

		$base = $globalconfig->getBaseURL('auth') . 'oauth/';
		$providerconfig = array(
			'authorization' => $base . 'authorization',
			'token' => $base . 'token'
		);
		$response = $providerconfig;


	// OpenID Connect Discovery 1.0
	// http://openid.net/specs/openid-connect-discovery-1_0.html
	} else if  (Utils::route('get', '^/\.well-known/openid-configuration$', $parameters)) {

		$base = $globalconfig->getBaseURL('auth') . 'oauth/';
		$providerconfig = array(
			'issuer' => $globalconfig->getValue('connect.issuer'),
			'authorization_endpoint' => $base . 'authorization',
			'token_endpoint' => $base . 'token',
			'token_endpoint_auth_methods_supported' => ['client_secret_basic'],
			'token_endpoint_auth_signing_alg_values_supported' => ['RS256'],
			'userinfo_endpoint' =>  $base . 'userinfo',
		);
		$response = $providerconfig;

	/*
	 *	Testing authentication using the auth libs
	 *	Both API auth and 
	 */
	} else if  (Utils::route('get', '^/auth$', $parameters)) {

		$auth = new Authenticator();
		$auth->req(false, true); // require($isPassive = false, $allowRedirect = false, $return = null

		$user = $auth->getUser();

		// $res = $auth->storeUser();
		// 
		$response = array('user' => $user->getJSON());
		
		// $response = array('message' => 'Test');



	} else {

		throw new Exception('Invalid request');
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($response, JSON_PRETTY_PRINT);

	// $profiling = microtime(true);
	$key = Utils::getPathString();
	$timer = round((microtime(true) - $profiling) * 1000.0);
	error_log("Time to run command:   [" . $key . "]  ======> " . $timer . "ms");


// } catch(UWAPObjectNotFoundException $e) {

// 	header("HTTP/1.0 404 Not Found");
// 	header('Content-Type: text/plain; charset: utf-8');
// 	echo "Error stack trace: \n";
// 	print_r($e);


} catch(Exception $e) {

	// TODO: Catch OAuth token expiration etc.! return correct error code.


	header("HTTP/1.0 500 Internal Server Error");
	header('Content-Type: text/html; charset: utf-8');

	// echo "Error stack trace: <pre>\n";
	// print_r($e);



	$data = array();

	$globalconfig = GlobalConfig::getInstance();



	$data['message'] = $e->getMessage();
	if ($globalconfig->getValue('debug', false)) {
		$data['error'] = array(
			'trace' => $e->getTraceAsString(),
			'line' => $e->getLine(),
			'file' => $e->getFile()
		);
	}


	$templateDir = dirname(dirname(dirname(__FILE__))).'/templates';
	$mustache = new Mustache_Engine(array(
		// 'cache' => '/tmp/uwap-mustache',
		'loader' => new Mustache_Loader_FilesystemLoader($templateDir),
		// 'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views/partials'),
	));
	$tpl = $mustache->loadTemplate('exception');
	echo $tpl->render($data);



}



