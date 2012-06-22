define([
	"controllers/appPicker", "controllers/newApp", "controllers/frontpage", "controllers/AppDashboard"
], function(appPicker, newApp, frontpage, AppDashboard) {

	$("document").ready(function() {

		UWAP.auth.require(function(user) {

			var picker = new appPicker($("ul.applicationlist"));

			console.log("Logged in", user);

			$("span#username").html(user.name);

			UWAP.appconfig.list(function(list) {
				console.log(list);
				picker.addList(list);
			});
			console.log(picker);
			picker.bind('selected', function(appid) {
				console.log("Selected an app.");
				$("div#appmaincontainer").empty();

				UWAP.appconfig.get(appid, function(appconfig) {
					var adash = new AppDashboard($("div#appmaincontainer"), appconfig);
				});
				
			});

			var fpage = new frontpage($("div#appmaincontainer"));

			$(".newAppBtn").bind("click", function() {
				var na = new newApp($("body"), function(no) {
					// console.log("Created new...", no);
					UWAP.appconfig.store(no, function() {
						console.log("Successully stored new app");
					}, function(err) {
						console.log("Error storing new app.");
					});
				});
				
				// na.bind("submit", function(no) {
				// 	console.log("Creating new app", no);
				// });
				na.activate();

			});

		});


		// UWAP.data.get('http://www.vegvesen.no/trafikk/xml/savedsearch.xml?id=600', 
		// 	{'xml': 1},
		// 	vegmelding);
		
		// UWAP.data.get('https://foodl.org/api/activity', {handler: "foodle"}, activity);
		// UWAP.data.get('http://foo/rest.php', {handler: "plain"}, generic);

	});

});

