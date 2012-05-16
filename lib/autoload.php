<?php


// App engine core
$base = dirname(__FILE__);
require_once($base . '/Utils.php');
require_once($base . '/Config.php');
require_once($base . '/Auth.php');
require_once($base . '/UWAPStore.php');
require_once($base . '/HTTPClient.php');
require_once($base . '/HTTPClientUserAuth.php');
require_once($base . '/HTTPClientBasic.php');
require_once($base . '/HTTPClientToken.php');
require_once($base . '/HTTPClientOAuth1.php');
require_once($base . '/HTTPClientOAuth2.php');

require_once($base . '/ParentMessenger.php');

require_once($base . '/Static/File.php');
require_once($base . '/Proxy/REST.php');


require_once(dirname($base) . '/solberg-oauth/lib/soauth.php');

require_once($base . '/So_StorageUWAP.php');
require_once($base . '/So_StorageServerUWAP.php');



/* --- SimpleSAMLphp ---- */

/* This is the base directory of the simpleSAMLphp installation. */
$baseDir = dirname($base) . '/simplesamlphp';

/* Add library autoloader. */
require_once($baseDir . '/lib/_autoload.php');
require_once($baseDir . '/modules/oauth/libextinc/OAuth.php');


