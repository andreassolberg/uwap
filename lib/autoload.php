<?php


// App engine core
$base = dirname(__FILE__);
require_once($base . '/Config.php');
require_once($base . '/Utils.php');
require_once($base . '/Exceptions.php');

require_once($base . '/Auth/Authenticator.php');



// Replace this.

require_once($base . '/GroupManager.php');
// require_once($base . '/AuthBase.php');
// require_once($base . '/Auth.php');


require_once(dirname($base) . '/solberg-oauth/lib/soauth.php');
require_once($base . '/Auth/OAuth.php');
require_once($base . '/Auth/AuthenticatedToken.php');


require_once($base . '/Groups/GroupConnector.php');
require_once($base . '/Groups/AdHocGroups.php');
require_once($base . '/Groups/ExtGroups.php');
require_once($base . '/Groups/GroupSet.php');


// require_once($base . '/GroupFetcherAgora.php');
// require_once($base . '/GroupFetcherRedmine.php');


// Models
require_once($base . '/Models/Model.php');
require_once($base . '/Models/User.php');
require_once($base . '/Models/Group.php');  
require_once($base . '/Models/AdHocGroup.php');
require_once($base . '/Models/Role.php');

require_once($base . '/GlobalConfig.php');

require_once($base . '/Feed.php');
require_once($base . '/Notifications.php');
require_once($base . '/Upcoming.php');


require_once($base . '/AppDirectory.php');
require_once($base . '/UWAPStore.php');
require_once($base . '/UWAPLogger.php');

require_once($base . '/HTTPClient.php');
require_once($base . '/HTTPClientBasic.php');
require_once($base . '/HTTPClientToken.php');
require_once($base . '/HTTPClientOAuth1.php');
require_once($base . '/HTTPClientOAuth2.php');

require_once($base . '/ParentMessenger.php');

require_once($base . '/People.php');
require_once($base . '/Mailer.php');



require_once($base . '/Static/File.php');
require_once($base . '/Proxy/REST.php');

require_once($base . '/LogStore.php');


require_once($base . '/So_StorageUWAP.php');
require_once($base . '/So_StorageServerUWAP.php');


// External libraries
require_once($base . '/xml2json.php');


$UWAP_BASEDIR = dirname($base);


/* --- SimpleSAMLphp ---- */

/* This is the base directory of the simpleSAMLphp installation. */
$baseDir = dirname($base) . '/simplesamlphp';

/* Add library autoloader. */
require_once($baseDir . '/lib/_autoload.php');
require_once($baseDir . '/modules/oauth/libextinc/OAuth.php');





