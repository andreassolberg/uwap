<?php


/**
 * Point the non-https app sites to this script, it will redirect to the https version.
 */

require_once(dirname(dirname(__FILE__)) . '/lib/autoload.php');

$config = new Config();

$url = 'https://' . $config->getID() . '.' . Config::hostname() . '/';

Utils::redirect($url);

