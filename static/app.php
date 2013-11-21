<?php


/**
 * Point the non-https app sites to this script, it will redirect to the https version.
 */

require_once(dirname(dirname(__FILE__)) . '/lib/autoload.php');

$globalconfig = GlobalConfig::getInstance();
$app = $globalconfig->getApp();
$url = 'https://' . $app->getHost() . '/';


Utils::redirect($url);

