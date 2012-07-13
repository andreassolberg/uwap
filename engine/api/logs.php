<?php


/*
 * This API is reached through
 * 
 * 		appid.uwap.org/_/api/logs.php
 *
 * This is used only indirectly through the UWAP core js API.
 * This API returns log files.
 */

require_once('../../lib/autoload.php');


try {

	/*
	 * We would like to not log more than errors for requests on this endpoint.
	 * Because if not viewing logs would kind of increase the log intensity.
	 */
	UWAPLogger::init(array('logLevel' => 2));

	$logstore = new LogStore();

	$config = Config::getInstance();
	$subhost = $config->getID();

	$auth = new Auth();
	$auth->req();

	$result = array();
	$result['status'] = 'ok';
	

	$after = (microtime(true)) - 1.0;
	if (isset($_REQUEST['after'])) $after = floatval($_REQUEST['after']);

	$secondsAgo = (microtime(true) - $after);

	// error_log("Requesting logs from staring (seconds ago) " . $secondsAgo);
	
	$max = 100;

	$filters = array();
	if (isset($_REQUEST['filters'])) {
		$filters = json_decode($_REQUEST['filters'], true);
	}

	$appdir = new AppDirectory();
	$apps = null;

	if (!$auth->memberOf('uwapadmin')) {
		$apps = $appdir->getMyAppIDs($auth->getRealUserID());

	}
	// error_log('My apps ' . json_encode($apps));

	$query = LogStore::processFilter($filters, $apps);

	$result['data'] = $logstore->getLogs($after, $query, $max);


	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($result);
	
} catch(Exception $error) {

	$result = array();
	$result['status'] = 'error';
	$result['message'] = $error->getMessage();
	echo json_encode($result);

}







