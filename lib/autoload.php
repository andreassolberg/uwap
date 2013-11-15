<?php


// App engine core
$base = dirname(__FILE__);

require_once($base . '/Utils.php');
require_once($base . '/Exceptions.php');


// Authentication
require_once(dirname($base) . '/solberg-oauth/lib/soauth.php');
require_once($base . '/Auth/OAuth.php');
require_once($base . '/Auth/AuthenticatedToken.php');
require_once($base . '/Auth/Authenticator.php');


// Groups
require_once($base . '/Groups/GroupConnector.php');
require_once($base . '/Groups/AdHocGroups.php');
require_once($base . '/Groups/ExtGroups.php');
require_once($base . '/Groups/GroupSet.php');



require_once($base . '/Clients/ClientDirectory.php');

require_once($base . '/AppHosting/AppHosting.php');



// Models
require_once($base . '/Models/Model.php');
require_once($base . '/Models/StoredModel.php');
require_once($base . '/Models/User.php');

require_once($base . '/Models/Client.php');
require_once($base . '/Models/App.php');
require_once($base . '/Models/APIProxy.php');
require_once($base . '/Models/ClientAuthorization.php');

require_once($base . '/Sets/Set.php');
require_once($base . '/Sets/ClientSet.php');
require_once($base . '/Sets/UserSet.php');
require_once($base . '/Sets/GroupSet.php');
require_once($base . '/Sets/AuthorizationList.php');

require_once($base . '/Models/Group.php');  
require_once($base . '/Models/AdHocGroup.php');
require_once($base . '/Models/Role.php');



// Feed

require_once($base . '/Feed/Notification.php');
require_once($base . '/Feed/Notifications.php');
require_once($base . '/Feed/Feed.php');
require_once($base . '/Feed/FeedItem.php');
require_once($base . '/Feed/FeedReader.php');



require_once($base . '/GlobalConfig.php');




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





