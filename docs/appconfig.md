# App config doc


Example of a proxy entry.

	{
		"_id" : ObjectId("4fcdbc6d12637a1e36000008"),
		"descr" : "A description of the test application.",
		"id" : "vootprovider",
		"logo" : "....==",
		"name" : "VOOT Provider",
		"owner" : {
			"displayName" : "Andreas Åkre Solberg",
			"email" : "andreas.solberg@uninett.no"
		},
		"uwap-userid" : "andreas@uninett.no"
		"owner-userid" : "andreas@uninett.no",
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
		"type" : "proxy",

	}

Status tags:


	operational
	pendingDAV
	listing
	pendingDelete


Adding a new one:

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

