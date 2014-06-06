<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/common/lib/autoload.php');

$base = dirname(__FILE__);

require_once(dirname($base) . '/solberg-oauth/lib/soauth.php');
require_once($base . '/Auth/OAuth.php');

require_once($base . '/So_StorageUWAP.php');
require_once($base . '/So_StorageServerUWAP.php');



/* --- SimpleSAMLphp ---- */

/* This is the base directory of the simpleSAMLphp installation. */
$baseDir = dirname($base) . '/simplesamlphp';

/* Add library autoloader. */
require_once($baseDir . '/lib/_autoload.php');





// require_once($baseDir . '/modules/oauth/libextinc/OAuth.php');