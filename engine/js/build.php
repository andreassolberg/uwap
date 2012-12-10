<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/lib/autoload.php');
header('Content-Type: application/javascript');

$config = Config::getInstance();
$hostname = $config->getHostname();


$corengine = GlobalConfig::scheme() . '://core.' . GlobalConfig::hostname();
$hosturl = GlobalConfig::scheme() . '://' . $hostname;
 // UWAP.utils.scheme + '://core.' + UWAP.utils.enginehostname +

$p = $_SERVER['PATH_INFO'];
if(empty($p)) throw new Exception('Invalid parameters');
if(strlen($p) <= 1) throw new Exception('Invalid parameters');

$p = substr($p, 1);

if (!preg_match('/^[a-zA-Z0-9\.\-_]+$/', $p)) {
	throw new Exception('Invalid parameters');
}

$file = $config->getAppPath('/js/' . $p . '.build.js');


// echo '<pre>'; print_r($_SERVER); exit;

header('Content-Type: application/javascript; charset: utf-8');

if (file_exists($file)) {

	$caching = GlobalConfig::getValue('cache', true);

	$data = file_get_contents($file);
	$timestamp = filemtime($file);
	$tsstring = gmdate('D, d M Y H:i:s ', $timestamp) . 'GMT';
	$etag = md5($data);

	$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
	$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;

	header('Cache-Control: max-age=290304000, public');

	if ($caching && $if_none_match && ($if_none_match === $etag) ) {

		header('X-Cache-Match: etag');
		header('HTTP/1.1 304 Not Modified');
		exit();

	} else if ($caching && $if_modified_since && $if_modified_since === $tsstring) {

		header('X-Cache-Match: modified-since');
		header('HTTP/1.1 304 Not Modified');
		exit();

	} else {

		header('X-Cache-Etag: ' . $if_none_match . ' != ' . $etag);
		header('X-Cache-Modified: ' . $if_modified_since . ' != ' . $tsstring);

	    header("Last-Modified: $tsstring");
	    header("ETag: \"{$etag}\"");

	}



	echo $data;

} 



?>