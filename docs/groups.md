# Groups


## Information model







## API

Adding new group


	var ng = {
		'title': "Testing to add a new group 2",
		'description': "some descr"
	};
	UWAP.groups2.addGroup(ng, function(data) {
		$("div#out").append('<pre>added new group: ' + JSON.stringify(data, null, 4) + '</pre>');
	});


List all groups


	UWAP.groups2.listMyGroups(function(data) {
	 	$("div#out").append('<pre>My groups: ' + JSON.stringify(data, null, 4) + '</pre>');
	});


Adding a new member to a group

	var u = {
		userid: "andreas@uninett.no",
		name: "Andreas Åkre Solberg",
		admin: true
	};


Update a group

	var gr = {
		'title': 'Oppdatert tittel',
		'description': 'Oppdatert descr'
	};
	UWAP.groups2.updateGroup('1b15ba0d-c3b5-4f54-89f5-1876e52f06a4', gr, function(data) {
		$("div#out").append('<pre>result: ' + JSON.stringify(data, null, 4) + '</pre>');
	} );

Showing information about a group

	UWAP.groups2.get('1b15ba0d-c3b5-4f54-89f5-1876e52f06a4', function(data) {
		$("div#out").append('<pre>info: ' + JSON.stringify(data, null, 4) + '</pre>');
	});







Getting information about a group

	{
	    "_id": {
	        "$id": "50448b9d6209a9d4240000b1"
	    },
	    "title": "Collaboration group about fish",
	    "description": "Fish is good",
	    "id": "10383ba6-44b0-4fdf-8ecb-639bda37e9b7",
	    "members": [
	        "andreas@uninett.no",
	        "anders@uninett.no",
	        "olavmo@uninett.no"
	    ],
	    "admins": [],
	    "uwap-userid": "andreas@uninett.no",
	    "userlist": {
	        "andreas@uninett.no": {
	            "_id": {
	                "$id": "506024c112637a5f77000004"
	            },
	            "name": "Andreas Åkre Solberg",
	            "userid": "andreas@uninett.no",
	            "mail": "andreas.solberg@uninett.no",
	            "a": "17ac8b04-9dde-4f42-bc09-3cecd7603164"
	        },
	        "anders@uninett.no": {
	            "_id": {
	                "$id": "5061579912637a1230000020"
	            },
	            "name": "Anders Lund",
	            "userid": "anders@uninett.no",
	            "mail": "anders.lund@uninett.no",
	            "a": "4c066ef8-92c9-4cf9-8207-aae579320992"
	        },
	        "olavmo@uninett.no": {
	            "_id": {
	                "$id": "506bf6e96209a96e38000001"
	            },
	            "name": "Olav Morken",
	            "userid": "olavmo@uninett.no",
	            "mail": "olav.morken@uninett.no",
	            "a": "cf1fb0d8-de31-41b4-99da-bd7d04a78944"
	        }
	    },
	    "you": {
	        "owner": true,
	        "admin": false,
	        "member": true
	    }
	}

Userlist should not be stored in db.


API.

	UWAP.groups = {
		listMyOwnGroups: function(callback, errorcallback) {
			UWAP._request(
			 	'GET', 
			 	'/_/api/groups.php/groups?filter=admin',
			 	null,
			 	null, callback, errorcallback);
		},
		listMyGroups: function(callback, errorcallback) {
			UWAP._request(
			 	'GET', 
			 	'/_/api/groups.php/groups',
			 	null,
			 	null, callback, errorcallback);
		},
		addGroup: function(object, callback, errorcallback) {
			UWAP._request(
			 	'POST', 
			 	'/_/api/groups.php/groups',
			 	object, 
			 	null, callback, errorcallback);
		},
		removeGroup: function(groupid, callback, errorcallback) {
			 UWAP._request(
			 	'DELETE', 
			 	'/_/api/groups.php/group/' + groupid,
			 	null,
			 	null, callback, errorcallback);
		},
		get: function(groupid, callback, errorcallback) {
			 UWAP._request(
			 	'GET', 
			 	'/_/api/groups.php/group/' + groupid,
			 	null,
			 	null, callback, errorcallback);
		},
		addMember: function(groupid, user, callback, errorcallback) {
			 UWAP._request(
			 	'POST', 
			 	'/_/api/groups.php/group/' + groupid + '/members',
			 	user, 
			 	null, callback, errorcallback);
		},
		removeMember: function(groupid, userid, callback, errorcallback) {
			 UWAP._request(
			 	'DELETE', 
			 	'/_/api/groups.php/group/' + groupid + '/member/' + userid,
			 	null,
			 	null, callback, errorcallback);
		}

	};



