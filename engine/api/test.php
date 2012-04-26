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

require_once('../../lib/autoload.php');


echo '<pre>';

$config = new Config();
$subhost = $config->getID();

$auth = new Auth();
$auth->req();

$result = array();
$result['status'] = 'ok';

$store = new UWAPStore();

$res = $store->queryOneUser("appdata-test", $auth->getRealUserID(), array("bool" => true));

if (empty($res)) throw new Exception("no entries found");

print_r($res);

$resj = json_decode(json_encode($res), true);

if (isset($resj["_id"])) {
	$resj["_id"] = new MongoId($resj["_id"]['$id']);
}
unset($resj["modified"]) ;

print_r($resj);


$store->store("appdata-test", $auth->getRealUserID(), $resj);





