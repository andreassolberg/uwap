# Users



Example input data:

	{
		"eduPersonPrincipalName": ["testuser1@uninett.no"],
		"displayName": ["Andreas _TestUser1_ Solberg"],
		"idp": ["https://idp.feide.no"],
		"mail": ["andreas@uninett.no"],
		"norEduPersonNIN": ["101080"]
	}

Results in an account id: `feide:testuser1@uninett.no`, and a normalized account data:

	{
		"name": "Andreas _TestUser1_ Solberg"",
		"mail": "andreas@uninett.no",
		"custom": {
			"idp": "https://idp.feide.no",
			"realm": ""
		}
	}


Which may map into this user object:


	{
		"userid": "uuid:sdojfsdkjfhdskjfl",
		"accounts": {
			"feide:andreas@uninett.no": {
				"mail": "andreas.solberg@uninett.no",
				"custom": {

				}
			}
		},
		"preferences": {

		}
	}

