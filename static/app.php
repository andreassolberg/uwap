<?php


/**
 * Point the non-https app sites to this script, it will redirect to the https version.
 */

require_once(dirname(dirname(__FILE__)) . '/lib/autoload.php');

$config = Config::getInstance();

$url = 'https://' . $config->getID() . '.' . GlobalConfig::hostname() . '/';

Utils::redirect($url);

