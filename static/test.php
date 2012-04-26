<?php



/*
 * This API is reached through
 * 
 * 		appid.uwap.org/_/api/storage.php
 *
 * This is used only indirectly through the UWAP core js API.
 * This API checks if the user is authenticated, and returns userdata if so.
 * If the user is not authenticated, nothing is returned ({status: error}).
 */

require_once('../../../lib/autoload.php');


$config = new Config();
$subhost = $config->getID();

$auth = new Auth();
$auth->req();

$result = array();
$result['status'] = 'ok';

$store = new UWAPStore();

$res = $store->queryOne("appdata-test", $auth->getRealUserID(), array("bool" => true));

echo '<pre>';
print_r($res);



