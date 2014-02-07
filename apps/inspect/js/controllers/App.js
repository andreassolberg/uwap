define(function(require, exports, module) {

	var 

		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		jso = require('uwap-core/js/oauth'),

		models = require('uwap-core/js/models'),

		AttributeMap = require('./AttributeMap'),

		moment = require('uwap-core/js/moment'),
		hogan = require('uwap-core/js/hogan'),
		prettydate = require('uwap-core/js/pretty')
		;


	require('uwap-core/bootstrap3/js/bootstrap');	
	require('uwap-core/bootstrap3/js/collapse');
	require('uwap-core/bootstrap3/js/button');
	require('uwap-core/bootstrap3/js/dropdown');	



	UWAP.__request = function(method, url, data, options, callback) {
		method = method || 'GET';

		options = options || {};
		options.handler = options.handler || 'plain';
		options.auth = (typeof options.auth !== 'undefined' ) ?  options.auth : true;

		var ar = {
			type: method,
			url: url,
			dataType: 'json',
			jso_provider: "uwap",
			jso_allowia: true,
			success: function(result, textStatus, jqXHR) {
				// console.log('Response _request response reviced()');
				// console.log(result);

				if (result.status === 'ok') {
					if (typeof callback === 'function') {
						var x = result.data;
						console.log(" ====> Result", result);
						console.log(" ====> textStatus", textStatus);
						console.log(" ====> jqXHR", jqXHR);
						console.log(" ====> jqXHR", jqXHR.statusText);
						callback(result, jqXHR.status + ' ' + jqXHR.statusText, jqXHR.getAllResponseHeaders());
					}
				} else if (result.status === 'redirect') {
					// console.log("Redirecting user to " + result.url);
					window.location.href = result.url;
				} else {

					console.error('Data request error (server side): ' + result.message);
					callback(new UWAP.Error('Errir', result.message));
					
				}	
				
			},
			error: function(err) {

				callback(new UWAP.Error(err));
				console.error('Error in API Call [' + method + ' ' + url + ']',  err);

				// if  (typeof errorcallback === 'function') {
				// 	errorcallback(err.responseText + '(' + err.status + ')');
				// }
				// console.error('Data request error (client side): ', err);
				// console.error('Response text');
				// console.error(err.responseText);
			}

		};

		if (data) {

			// data.options = options;

			ar.data = JSON.stringify(data);
			ar.processData = false;
			ar.contentType = 'application/json; charset=UTF-8';
		}


		for(var key in options) {
			if (options.hasOwnProperty(key)) {
				ar[key] = options[key];
			}
		}

		// console.log("UWAP.data _request data ", data, " options", options);

		try {
			if (options.handler === 'plain' && !options.auth) {
				// console.log("Attempt nonauthenticated REST request to ", data.url); 
				$.ajax(ar);

			} else {
				// console.log("Attempt authenticated REST request to ", data.url); return;
				// console.log("UWAP.data authenticated _request data ", data, " options", options);
				$.oajax(ar);
			}
			
		} catch(exception) {
			if (typeof errorcallback === 'function') {
				errorcallback(exception);	
			} else {
				console.error("Error performing XHTTP Request: ", exception.message);
			}

			
		}

	};



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
			"path": "/api/group/{groupid}",
			"method": "get",
			"map": ["groupid"]
		},
		'group-members': {
			"path": "/api/group/{groupid}/members",
			"method": "get",
			"map": ["groupid"]
		},
		'feed': {
			"path": "/api/feed",
			"method": "post"
		},
		'feed-upcoming': {
			"path": "/api/feed/upcoming",
			"method": "post"
		},
		'feed-notifications': {
			"path": "/api/feed/notifications",
			"method": "post"
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

			var path = apiconfig[key].path;
			var url = UWAP.utils.getEngineURL(path);

			if (config.map) {
				that.attributemap = new AttributeMap($("#am"), config.map, function(res) {
					console.log("res", res);

					
					console.log("Path is ", path)

					$.each(config.map, function(i, key) {
						console.log("Replacing {" + key + "} with " + res[key]);
						path = path.replace('{' + key + '}', res[key]);
					});

					console.log("Accessing the following path:", path);

					url = UWAP.utils.getEngineURL(path);

					that.performRequest(apiconfig[key].method, url);

				});
			} else {
				
				that.performRequest(apiconfig[key].method, url);
			}



			
		})

	}

	App.prototype.performRequest = function(method, url) {

		console.log("Performing a request " + method + " " + url);
		UWAP.__request(
		 	method, url,
		 	null,
		 	null, function(data, status, headers) {

		 		console.log("HEADERS", headers);
		 		$("#reqHeaders").empty().text(method.toUpperCase() + ' ' + url);

		 		$("#output").empty().text(JSON.stringify(data, undefined, 4));
		 		$("#respHeaders").empty().text('HTTP/1.1 ' + status + "\r\n" + headers);

		 	}, function(err) {
		 		$("#output").empty().text('ERROR ' + err);
		 	}
		);

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