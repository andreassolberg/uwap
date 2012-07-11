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

	$logstore = new LogStore();

	$config = Config::getInstance();
	$subhost = $config->getID();

	$auth = new Auth();
	$auth->req();

	$result = array();
	$result['status'] = 'ok';
	



	$after = microtime() - 1000;
	if (isset($_REQUEST['after'])) $before = $_REQUEST['after'];

	$max = 100;

	$result['data'] = $logstore->getLogs($after, $max);


	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($result);
	
} catch(Exception $error) {

	$result = array();
	$result['status'] = 'error';
	$result['message'] = $error->getMessage();
	echo json_encode($result);

}







