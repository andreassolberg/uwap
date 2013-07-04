# OAuth and UWAP


## Scopes

app_APPID_user
: Gives access to application data for applicatiuon APPID.

appconfig
: Give access to manage set of applicaitons. Used by 'dev' application.

feedread
: Gives read access to activity stream API

feedwrite
: Gives write access to activity stream API

userinfo
: Gives access to authentication, and user attributes.

longterm
: Longer validity on token..



rest_APPID
: Basic access to a proxied API

rest_APPID_SCOPE
: A specific API specific scope defined in the rest proxy configuration.




### Default set of scopes for applications

These scopes are default:

* app_APPID_user
* userinfo

In addition, it adds scopes from appconfiguration.





### App as a client configuration

	{
		"client_id"		: "app_APPID",
		"client_name"	: "Name of client",				// From config["name"]
		"uwap-userid"	: "andreas@uninett.no", 		// From config["uwap-userid"]
		"redirect_uri" 	: "https://APPID.uwap.org/",
		"scopes"		: ["app_APPID_user", "userinfo"],
		"scopes.requested": [""]
	}

### Indepdent client configuration

Stored in `oauth2-server-clients` collection.

	"client_id" : "voottest1",
	"client_name" : "Test client with implicit grant flow to test VOOT interface",
	"owner" : {
		"displayName" : "Andreas Åkre Solberg",
		"email" : "andreas.solberg@uninett.no"
	},
	"redirect_uri" : "http://andreas.solweb.no/",
	"scopes" : [
		"voot"
	]



### SOA Proxy configuration

This property indicates 

	{
		"descr" : "A description of the test application.",
		"id" : "vootprovider",
		"name" : "VOOT Provider",
	//	"owner" : {
	//		"displayName" : "Andreas Åkre Solberg",
	//		"email" : "andreas.solberg@uninett.no"
	//	},
		"uwap-userid" : "andreas@uninett.no",
	//	"owner-userid" : "andreas@uninett.no",
		"proxies" : {
			"api" : {
				"endpoints" : [
					"https://beta.foodl.org/api/voot2/"
				],
				"scopes" : [
					"voot"
				],
				"token_hdr" : "X-Auth",
				"token" : "s2d72mfbsixh36fgdm39ssmx.shd1z",
				"user" : true
			}
		},
		"status" : [
			"pendingDAV"
		],
		"type" : "proxy"
	}


