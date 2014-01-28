define(function(require, exports, module) {

	var 

		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		jso = require('uwap-core/js/oauth'),

		models = require('uwap-core/js/models'),



		moment = require('uwap-core/js/moment'),
		hogan = require('uwap-core/js/hogan'),
		prettydate = require('uwap-core/js/pretty')
		;


	require('uwap-core/bootstrap3/js/bootstrap');	
	require('uwap-core/bootstrap3/js/collapse');
	require('uwap-core/bootstrap3/js/button');
	require('uwap-core/bootstrap3/js/dropdown');	


	// var tmpl = {
	// 	"subscriptionList": require('uwap-core/js/text!templates/subscriptionList.html'),
	// 	"upcomingItem":  require('uwap-core/js/text!templates/upcomingItem.html')
	// };



	var apiconfig = {
		'userinfo': {
			"path": "/api/userinfo",
			"method": "get"
		},
		'groups-public': {
			"path": "/api/groups/public",
			"method": "get"
		},
		'groups': {
			"path": "/api/groups",
			"method": "get"
		},
		'group-info': {
			"path": "/api/group{group.id}",
			"method": "get"
		},
		'group-members': {
			"path": "/api/group{group.id}/members",
			"method": "get"
		}
	};

	



	var App = function(el) {
		var that = this;
		this.el = el;
		$('.dropdown-toggle').dropdown()

		$(".loader-hideOnLoad").hide();
		$(".loader-showOnLoad").show();


		for(var key in apiconfig) {
			var x = $('<div class="apiselection"><a href="">' + apiconfig[key].method.toUpperCase() + ' ' + apiconfig[key].path + "</div>").data('apikey', key);
			$("#apilist").append(x);
		}


		$("#apilist").on('click', '.apiselection', function(e) {
			e.preventDefault();
			e.stopPropagation();

			var key = $(e.currentTarget).data('apikey');
			var config = apiconfig[key];

			UWAP._request(
			 	apiconfig[key].method, UWAP.utils.getEngineURL(apiconfig[key].path),
			 	null,
			 	null, function(data) {

			 		$("#output").empty().text(JSON.stringify(data, undefined, 4));

			 	}, function(err) {
			 		$("#output").empty().text('ERROR ' + err);
			 	});


			
		})

	}


	App.prototype.setauth = function(user) {
		this.user = user;

		$(".myname").empty().append(user.name);


	}

	App.prototype.getUser = function () {
		return this.user;
	}
	App.prototype.getGroups = function () {
		return this.groups;
	}


	App.init = function() {
		var app;
		$("document").ready(function() {
			// console.log("App.init()");
			UWAP.auth.require(function(data) {
				// console.log("Is authenticated, now start the app.");
				app = new App($("body"))

				var user = new models.User(data);
				app.setauth(user);
			});
		});
	};


	return App;


});