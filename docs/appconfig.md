# App config doc


Example of a proxy entry.

	{
	    "_id": "4fcdbc6c12637a1e36000003",
	    "descr": "A description of the test application.",
	    "id": "proxydemo",
	    "logo": "...==",
	    "name": "UWAP Proxydemo",
	    "owner": {
	        "displayName": "Andreas Åkre Solberg",
	        "email": "andreas.solberg@uninett.no"
	    },
	    "owner-userid": "andreas@uninett.no",
	    "proxies": {
	        "api": {
	            "endpoints": [
	                "http://bridge.uninett.no/"
	            ],
	            "scopes": [
	                "foo"
	            ],
	            "token_hdr": "X-Token",
	            "token": "Foo",
	            "user": true
	        }
	    },
	    "status": [
	        "pendingDAV"
	    ],
	    "type": "proxy",
	    "uwap-userid": "andreas@uninett.no"
	}

Status tags:


	operational
	pendingDAV
	listing
	pendingDelete

