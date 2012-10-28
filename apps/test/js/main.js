define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		moment = require('uwap-core/js/moment'),
    	prettydate = require('uwap-core/js/pretty')
    	;

   

	require('uwap-core/bootstrap/js/bootstrap');	
	
	require('uwap-core/bootstrap/js/bootstrap-modal');
	require('uwap-core/bootstrap/js/bootstrap-collapse');
	require('uwap-core/bootstrap/js/bootstrap-button');
	require('uwap-core/bootstrap/js/bootstrap-dropdown');



		function testVoot() {
			$.oajax({
				url: UWAP.utils.getURL("core", "/api/groups/@me"),
				jso_provider: "uwapvoot",
				jso_scopes: ["voot"],
				jso_allowia: true,
				dataType: 'json',
				success: function(response) {
					console.log(response);
					$("div#out").append('<pre>' + JSON.stringify(response, null, 4) + '</pre>');
					
				}
			});
		}

		function weatherresult(response) {
			console.log("Weather response", response);
			$("div#out").append('<pre>' + JSON.stringify(response, null, 4) + '</pre>');
		}



		$("document").ready(function() {

			$("input#smt").on("click", function() {
				UWAP.auth.require(function(data) {
					$("div#out").append("<h2>You are logged in (required check) as - <i>" + data.name + "</i></h2>");
				});
			});

			UWAP.auth.checkPassive(function(data) {
				$("div#out").append("<h2>You are logged in (passive check) as - <i>" + data.name + "</i></h2>");

				// if (navigator.geolocation) {
				// navigator.geolocation.getCurrentPosition(function(position) {
				// 	UWAP.data.get('http://api.yr.no/weatherapi/oceanforecast/0.9/?lat=' + 
				// 		position.coords.latitude  + ';lon=' + position.coords.longitude  + '', 
				// 		{'xml': 1},
				// 		weatherresult);
				// });
				// }

				// UWAP.data.get("https://graph.facebook.com/me/home", {handler: "facebook"}, function(res) {
				// 	console.log("facebook response", res);
				// });


				// UWAP.store.save({"name": "Andreas", "number": Math.random()}, function() {
				// 	console.log("Object saved");
				// });
				// UWAP.store.queryOne({"name": "Andreas"}, function(data) {
				// 	console.log("queryOne()", data);
				// });
				// UWAP.store.queryList({"name": "Andreas"}, function(data) {
				// 	console.log("queryList()", data);
				// });


				// var ng = {
				// 	'title': "Testing to add a new group 2",
				// 	'description': "some descr"
				// };
				// UWAP.groups2.addGroup(ng, function(data) {
				// 	$("div#out").append('<pre>added new group: ' + JSON.stringify(data, null, 4) + '</pre>');
				// });

				// UWAP.groups2.listMyGroups(function(data) {
				// 	$("div#out").append('<pre>My groups: ' + JSON.stringify(data, null, 4) + '</pre>');
				// });

				var u = {
					userid: "andreas@uninett.no",
					name: "Andreas Åkre Solberg",
					admin: true
				};

				var gr = {
					'title': 'Oppdatert tittel',
					'description': 'Oppdatert descr'
				};

				// UWAP.groups2.updateGroup('1b15ba0d-c3b5-4f54-89f5-1876e52f06a4', gr, function(data) {
				// 	$("div#out").append('<pre>result: ' + JSON.stringify(data, null, 4) + '</pre>');
				// } );

				// UWAP.groups2.get('1b15ba0d-c3b5-4f54-89f5-1876e52f06a4', function(data) {
				// 	$("div#out").append('<pre>info: ' + JSON.stringify(data, null, 4) + '</pre>');
				// });

				UWAP.groups.listMyGroups(function(data) {
					$.each(data, function(i, item) {
						var e = '';
						e += '<p><span style="font-size: 16pt">' + item.title + '</span> - <span>' + item.description + '</span></p>';
						e += '<pre>' + JSON.stringify(item) + '</pre>';
						$("div#out").append(e);
					});
				})


				UWAP.people.query('uninett.no', 'andreas', function(data) {
					// $("div#out").append('<pre>Search result: ' + JSON.stringify(data, null, 4) + '</pre>');
					$.each(data, function(i, item) {
						var e = '';
						e += '<h3>' + item.name + '</h3>';
						e += '<p>' + item.o + ' ' + item.mail + '</p>';
						if (item.jpegphoto) {
							e += '<img style="max-height: 64px; border: 1px solid #ccc" src="data:image/jpeg;base64,' + item.jpegphoto + '" />';
						}
						$("div#out").append(e);
					});
				});


				// UWAP.groups2.removeMember('f5be0115-ffd0-4dda-a1fd-ee78b9a62d29', u.userid, function(data) {
				// 	$("div#out").append('<pre>result: ' + JSON.stringify(data, null, 4) + '</pre>');
				// } );




			}, function(response) {
				console.log("Not logged in");
				$("div#out").append('<pre>Not logged in: ' + JSON.stringify(response, null, 4) + '</pre>');
			});


		});




});