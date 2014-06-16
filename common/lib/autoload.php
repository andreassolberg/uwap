<?php


// App engine core
$base = dirname(__FILE__);


require_once(dirname(dirname($base)) . '/vendor/autoload.php');

require_once($base . '/Utils.php');
require_once($base . '/Exceptions.php');



// SCIM
require_once($base . '/SCIM/SCIMResource.php');
require_once($base . '/SCIM/SCIMSchema.php');
require_once($base . '/SCIM/SCIMSchemaDirectory.php');
require_once($base . '/SCIM/SCIMAttributeDef.php');
require_once($base . '/SCIM/SCIMResourceGroup.php');
require_once($base . '/SCIM/SCIMResourceGroupType.php');
require_once($base . '/SCIM/SCIMResourceRole.php');
require_once($base . '/SCIM/Protocol/SCIMListResponse.php');




require_once($base . '/Auth/AuthorizationPresenter.php');

// Authentication
// require_once($base . '/solberg-oauth/lib/soauth.php');
require_once($base . '/Auth/OAuth/So_Storage.php');
// require_once($base . '/Auth/OAuth/So_StorageUWAP.php');
require_once($base . '/Auth/OAuth/So_StorageServerUWAP.php');

require_once($base . '/Auth/OAuth/So_log.php');
require_once($base . '/Auth/OAuth/So_Utils.php');
require_once($base . '/Auth/OAuth/exceptions.php');

require_once($base . '/Auth/OAuth/messages/So_Message.php');
require_once($base . '/Auth/OAuth/messages/So_Request.php');
require_once($base . '/Auth/OAuth/messages/So_Response.php');

require_once($base . '/Auth/OAuth/messages/So_AuthResponse.php');
require_once($base . '/Auth/OAuth/messages/So_ErrorResponse.php');
require_once($base . '/Auth/OAuth/messages/So_TokenResponse.php');
require_once($base . '/Auth/OAuth/messages/So_AuthenticatedRequest.php');
require_once($base . '/Auth/OAuth/messages/So_AuthRequest.php');
require_once($base . '/Auth/OAuth/messages/So_TokenRequest.php');

require_once($base . '/Auth/OAuth/So_AuthorizationCode.php');
require_once($base . '/Auth/OAuth/So_Authorization.php');
require_once($base . '/Auth/OAuth/So_AccessToken.php');

require_once($base . '/Auth/OAuth/So_Server.php');

require_once($base . '/Auth/OAuth/So_Client.php');




// Groups
require_once($base . '/Groups/GroupConnector.php');
require_once($base . '/Groups/AdHocGroups.php');
require_once($base . '/Groups/ExtGroups.php');



require_once($base . '/Clients/ClientDirectory.php');
require_once($base . '/AppHosting/AppHosting.php');

require_once($base . '/Users/ComplexUserID.php');
require_once($base . '/Users/UserAttributeInput.php');


// Models
require_once($base . '/Models/Model.php');
require_once($base . '/Models/StoredModel.php');
require_once($base . '/Models/User.php');

require_once($base . '/Models/Client.php');
require_once($base . '/Models/App.php');
require_once($base . '/Models/APIProxy.php');
require_once($base . '/Models/ClientAuthorization.php');


require_once($base . '/Sets/Set.php');
require_once($base . '/Sets/RoleSet.php');
require_once($base . '/Sets/ClientSet.php');
require_once($base . '/Sets/UserSet.php');
require_once($base . '/Sets/GroupSet.php');
require_once($base . '/Sets/AuthorizationList.php');

require_once($base . '/Models/Group.php');  
require_once($base . '/Models/AdHocGroup.php');
require_once($base . '/Models/Role.php');

require_once($base . '/Users/UserDirectory.php');


// Feed

require_once($base . '/Feed/Notification.php');
require_once($base . '/Feed/Notifications.php');
require_once($base . '/Feed/Feed.php');
require_once($base . '/Feed/FeedItem.php');
require_once($base . '/Feed/FeedReader.php');



require_once($base . '/GlobalConfig.php');




require_once($base . '/UWAPStore.php');
require_once($base . '/UWAPLogger.php');

require_once($base . '/HTTPClient/HTTPClient.php');
require_once($base . '/HTTPClient/HTTPClientBasic.php');
require_once($base . '/HTTPClient/HTTPClientToken.php');
require_once($base . '/HTTPClient/HTTPClientOAuth1.php');
require_once($base . '/HTTPClient/HTTPClientOAuth2.php');

require_once($base . '/ParentMessenger.php');

require_once($base . '/People.php');
require_once($base . '/Mailer.php');



require_once($base . '/Proxy/REST.php');

require_once($base . '/LogStore.php');




// External libraries
require_once($base . '/xml2json.php');


$UWAP_BASEDIR = dirname(dirname($base));








