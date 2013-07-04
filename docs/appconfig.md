# App config doc




Status tags:


	operational
	pendingDAV
	listing
	pendingDelete


Adding a new proxy, POST this config:

	{
		"id" : "soademo",
		"type" : "proxy",
		"name" : "SOA Demo",
		"descr" : "Demoing a soa proxy",
		"uwap-userid" : "andreas@uninett.no",
		"proxies" : {
			"api" : {
				"endpoints" : [
					"https://beta.foodl.org/api/voot2/"
				],
				"scopes" : [
					"foo", "bar"
				],
				"token_hdr" : "X-Auth",
				"token" : "sjdbsn2i47dt2bdjd63.djxi3ur.djdh1psjd",
				"type" : "token",
				"user" : true
			}
		},
		"status" : [
			"operational"
		]
	}


## Data models

Example of client stored in `oauth2-server-clients`:

	{
		"_id" : ObjectId("50bde58fe4f76be6880dcec5"),
		"client_id" : "redmine",
		"client_name" : "UNINETT RedMine",
		"client_secret" : "sdfsdf96sdf765sdf45w8",
		"groups" : [
			"@realm:uninett.no"
		],
		"scopes" : [
			"feedread",
			"feedwrite"
		]
	}

Example of a proxy entry.

	{
		"_id" : ObjectId("5140114212637a161f000000"),
		"descr" : "Description",
		"id" : "baluba2",
		"name" : "Baluba2",
		"status" : [
			"operational"
		],
		"type" : "proxy",
		"uwap-userid" : "andreas@uninett.no",
		"policy": {auto: true},
		"proxy" : {
			"endpoints" : [
				"http://foo.com"
			],
			"scopes": {
				"scope1": {
					name: "Write access",
					policy: {
						auto: false
					}
				}
			},
			"token_hdr" : "UWAP-X-Auth",
			"token" : "13a3505e-0c59-41b4-8896-b51d48cd08f5",
			"type" : "token",
			"user" : true
		}
	}

	


## Methods

All API access requires an OAuth token with scope `appconfig`.


### Query applications

Query a list of relevant applications

	GET /appconfig/apps

Query details about a specific application

	GET /appconfig/app/([a-z0-9\-]+)


### Posting application config

	POST   /appconfig/apps
	GET    /appconfig/app/([a-z0-9\-]+)/status
	POST   /appconfig/app/([a-z0-9\-]+)/status
	GET    /appconfig/app/([a-z0-9\-]+)/clients
	POST   /appconfig/app/([a-z0-9\-]+)/davcredentials
	POST   /appconfig/app/([a-z0-9\-]+)/bootstrap
	POST   /appconfig/app/([a-z0-9\-]+)/authorizationhandler/([a-z0-9\-]+)
	DELETE /appconfig/app/([a-z0-9\-]+)/authorizationhandler/([a-z0-9\-]+)

Checking if an identifier is available.

	GET /appconfig/check/([a-z0-9\-]+)










