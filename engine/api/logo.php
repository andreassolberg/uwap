<?php

/*
 * This API is reached through
 * 
 * 		appid.uwap.org/_/api/data.php
 *
 * This is used only indirectly through the UWAP core js API.
 * Data is fetched from a remote source and returned, using the specified handler, 
 * such as OAuth1, basic auth or similar
 */

require_once('../../lib/autoload.php');



try {

	if (empty($_REQUEST['app'])) {
		throw new Exception("Missing parameter [url]");
	}

	$c = new Config($_REQUEST['app']);
	$ac = $c->getConfig();
	
	if (!empty($ac["logo"])) {

		header('Content-Type: image/png');
		echo base64_decode($ac["logo"]);
	}


} catch(Exception $error) {


}


