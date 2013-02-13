
/**
 * @package UWAP
 * @description The core UWAP javascript library communicates with the UWAP server using the REST API.
 * @author Andreas Åkre Solberg
 * @copyright UNINETT AS
 * @version 1.0
 */


define(function(require) {

	var 
		jso = require('uwap-core/js/oauth'),
		models = require('uwap-core/js/models');

	UWAP = {};
	UWAP.utils = {};
	UWAP.utils.enginehostname = requirejs.enginehostname;
	UWAP.utils.hostname = requirejs.hostname;
	UWAP.utils.scheme = requirejs.scheme;
	UWAP.utils.appid = requirejs.appid;

	UWAP.utils.jso_configure = function(conf) {
		jso.jso_configure(conf);
	};

	/*
	 * Setup and install the ChildBrowser plugin to Phongap/Cordova.
	 */
	if(window.isPhonegap) {
		ChildBrowser.install();
		console.log("Installing childbrowser...")

		// Use ChildBrowser instead of redirecting the main page.
		// jso.jso_registerRedirectHandler(window.plugins.childBrowser.showWebPage);
		// console.log(window.plugins.childBrowser.showWebPage);
		// window.plugins.childBrowser.showWebPage('http://vg.no');
		// jso.jso_registerRedirectHandler(window.plugins.childBrowser.showWebPage);
		jso.jso_registerRedirectHandler(function(p, callback) {
			// alert('opening url ' + p);
			window.plugins.childBrowser.showWebPage(p, {
				showLocationBar:true,
				showAddress: true,
				showNavigationBar: true
			});

	        /*
	         * Register a handler on the childbrowser that detects redirects and
	         * lets JSO to detect incomming OAuth responses and deal with the content.
	         */
			window.plugins.childBrowser.onLocationChange = function(url){
	            url = decodeURIComponent(url);
	            console.log("Checking location: " + url);
	            jso.jso_checkfortoken('uwap', url, function() {
	                console.log("Closing child browser, because a valid response was detected.");
	                // alert('closing childbrowser now');
	                setTimeout(function() {
						window.plugins.childBrowser.close();
						if (typeof callback === 'function') callback();
	                }, 800);
	                
	            });
	            // window.plugins.childBrowser.close();
	        };
			window.plugins.childBrowser.onClose = function(){
	            $("div#out").empty().append(JSON.stringify(window.plugins.childBrowser));
	        };

			// var x = window.open(p);
			// x.focus();
		});
		console.log("Registering redirect handler...");



	} else {
		// jso.jso_registerRedirectHandler(function(p) {

		// 	// if (window.cxcount++ > 1 ) {
		// 	// 	alert('Multiple childbrowsers ' + window.cxcount);
		// 	// 	return;
		// 	// }

		// 	// alert('opening url ' + p);
		// 	// window.plugins.childBrowser.showWebPage(p);
		// 	var x = window.open(p);
		// 	x.focus();
		// });
		console.log("Registering redirect handler...");
	}


	UWAP.utils.addQueryParam = function (url, key, value) {
		var delimiter = ((url.indexOf('?') != -1) ? '&' : '?');
		if (url.charAt(url.length-1) === '?') {
			delimiter = '';
		}
		return url + delimiter + encodeURIComponent(key) + '=' + encodeURIComponent(value);
	};
	UWAP.utils.goAndReturn = function(url) {
		// console.log("About to redirect to: " + UWAP.utils.addQueryParam(url, 'return', document.URL));
		var base = UWAP.utils.scheme + '://core.' + UWAP.utils.hostname + '/';
		window.location = UWAP.utils.addQueryParam(url, 'return', document.URL);	
	}

	UWAP.utils.getEngineURL = function(path) {
		var base = UWAP.utils.scheme + '://core.' + UWAP.utils.enginehostname + '';
		return base + path;
	}

	UWAP.utils.getAppURL = function(path) {
		var base = UWAP.utils.scheme + '://' + UWAP.utils.hostname + '';
		return base + path;
	}

	UWAP.utils.loadCSS = function (url) {
		var link = document.createElement("link");
		link.type = "text/css";
		link.rel = "stylesheet";
		link.href = require.toUrl(url);
		// console.log("CSS ››››› Loading CSS : " + link.href);
		document.getElementsByTagName("head")[0].appendChild(link);
	}


	UWAP.token = null;

	UWAP.messenger = {};
	UWAP.messenger.send = function(msg) {
		if (UWAP.messenger.receiver) {
			UWAP.messenger.receiver(msg);
		} else {
			console.error("Could not deliver message from iframe, because listener was not setup.");
		}
	};


	var redirect_uri = window.location.protocol + '//' + window.location.hostname + 
		window.location.pathname;
	var passive_redirect_uri = window.location.protocol + '//' + window.location.hostname + '/_/passiveResponse';

	// console.log("Redirect URI is " + redirect_uri);

	var client_id = 'app_' + UWAP.utils.appid;
	jso.jso_configure({
		"uwap": {
			client_id: client_id,
			authorization: UWAP.utils.getEngineURL('/api/oauth/authorization'),
			redirect_uri: redirect_uri,
			passive_redirect_uri: passive_redirect_uri
		}
	}, {debug: 1});



	/**
	 * A generic protocol request wrapper function.
	 * @param  {string}   method        The HTTP Method to use. GET is default
	 * @param  {string}   url           The relative URL
	 * @param  {object}   data          Optionally an object to send.
	 * @param  {object}   options       A set of options
	 * @param  {Function} callback      Success callback
	 * @param  {Function} errorcallback Error callback
	 * @return {void}                 Returns undefined
	 */
	UWAP._request = function(method, url, data, options, callback, errorcallback, dataprocess) {
		method = method || 'GET';

		options = options || {};
		options.handler = options.handler || 'plain';

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

						if (typeof dataprocess === 'function') {
							dataprocess(result.data);
						}
						callback(result.data);
					}
				} else if (result.status === 'redirect') {
					// console.log("Redirecting user to " + result.url);
					window.location.href = result.url;
				} else {
					if  (typeof errorcallback === 'function') {
						errorcallback(result.message);
					}
					console.error('Data request error (server side): ' + result.message);
				}
				
			},
			error: function(err) {
				if  (typeof errorcallback === 'function') {
					errorcallback(err.responseText + '(' + err.status + ')');
				}
				console.error('Data request error (client side): ', err);
				console.error('Response text');
				console.error(err.responseText);
			}
		};

		if (data) {
			ar.data = JSON.stringify(data);
			ar.processData = false;
			ar.contentType = 'application/json; charset=UTF-8';
		}


		for(var key in options) {
			if (options.hasOwnProperty(key)) {
				ar[key] = options[key];
			}
		}


		try {
			if (options.handler === 'plain') {
				console.log("trying to use plain handler for this dataset", data, options); return;
				$.ajax(ar);
			} else {
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



	UWAP.auth = {

		require: function (callbackSuccess, options) {
			options = options || {};
			UWAP._request(
				'GET', 
				UWAP.utils.getEngineURL("/api/userinfo"),
				options, null, callbackSuccess);
		},
		check: function (callbackSuccess, callbackNo) {
			UWAP._request(
				'GET', 
				UWAP.utils.getEngineURL("/api/userinfo"),
				null, {
					"jso_allowia": false
				}, callbackSuccess, callbackNo);
		},
		logout: function() {
			jso.jso_wipe();
		},

		// TODO: Upgrade to support OAUTH
		checkPassive: function (callbackSuccess, callbackNo) {
			// console.log("checkPassive()");
			UWAP._request(
				'GET', 
				UWAP.utils.getEngineURL("/api/userinfo"),
				null, {
					"jso_allowia": false
				}, callbackSuccess, function() {

					// console.log("callbackFailed passive");

					jso.jso_ensureTokensPassive({"uwap": false}, function() {
						// console.log("Callback success from jso_ensureTokensPassive() ")

						UWAP._request(
							'GET', 
							UWAP.utils.getEngineURL("/api/userinfo"),
							null, {
								"jso_allowia": false
							}, callbackSuccess, callbackNo);

					}, function(error) {
						// console.log("Callback failed from jso_ensureTokensPassive() ");
						callbackNo(error);
					});


					// UWAP.messenger.receiver = function(msg) {

					// 	if (msg.type === "passiveAuth" && msg.status === "success") {

					// 		UWAP.auth.check(callbackSuccess, callbackNo)

					// 	} else {
					// 		callbackNo();
					// 	}

					// 	// console.log("Received response. Juhu ", msg);
					// 	delete UWAP.messenger.receiver;
					// 	// $("body iframe.uwap_messenger_iframe").remove();

					// };
					// $("body").prepend('<iframe class="uwap_messenger_iframe" style="display: none" src="/_/login?passive=true"></iframe>');

				});


		}

	};

	UWAP.store = {
		save: function(object, callback, errorcallback) {
			UWAP._request(
				'POST', UWAP.utils.getEngineURL("/api/store"),
				{
					op: "save",
					object: object
				}, 
				null, callback, errorcallback);
		},
		remove: function(object, callback, errorcallback) {
			UWAP._request(
				'POST', UWAP.utils.getEngineURL("/api/store"),
				{
					op: "remove",
					object: object
				}, 
				null, callback, errorcallback);
		},
		queryOne: function(query, callback, errorcallback) {
			UWAP._request(
				'POST', UWAP.utils.getEngineURL("/api/store"),
				{
					op: "queryOne",
					query: query
				}, 
				null, callback, errorcallback);
		},
		queryList: function(query, callback, errorcallback) {
			UWAP._request(
				'POST', UWAP.utils.getEngineURL("/api/store"),
				{
					op: "queryList",
					query: query
				}, 
				null, callback, errorcallback);
		}
	};

	UWAP.feed = {
		notificationsMarkRead: function(ids, callback, errorcallback) {
			UWAP._request(
				'POST', UWAP.utils.getEngineURL("/api/feed/notifications/markread"),
				ids, 
				null, callback, errorcallback);
		},
		upcoming: function(selector, callback, errorcallback) {
			UWAP._request(
				'POST', UWAP.utils.getEngineURL("/api/feed/upcoming"),
				selector, 
				null, callback, errorcallback, function(data) {
					var items = data.items;
					if (data.items && data.items.length) {
						data.items = [];
					
						for(var i = 0; i < items.length; i++) {
							// data.items.push('1');
							data.items.push(new models.FeedItem(items[i]));
						}						
					}
				});
		},
		notifications: function(selector, callback, errorcallback) {
			UWAP._request(
				'POST', UWAP.utils.getEngineURL("/api/feed/notifications"),
				selector, 
				null, callback, errorcallback);
		},
		post: function(object, callback, errorcallback) {
			UWAP._request(
				'POST', UWAP.utils.getEngineURL("/api/feed/post"),
				{
					msg: object
				}, 
				null, callback, errorcallback);
		},
		respond: function(object, callback, errorcallback) {
			UWAP._request(
				'POST', UWAP.utils.getEngineURL("/api/feed/item/" + object.inresponseto + '/respond'),
				{
					msg: object
				}, 
				null, callback, errorcallback);
		},
		delete: function(oid, callback, errorcallback) {
			UWAP._request(
				'DELETE', UWAP.utils.getEngineURL("/api/feed/item/" + oid),
				null, 
				null, callback, errorcallback);
		},
		read: function(selector, callback, errorcallback) {
			UWAP._request(
				'POST', UWAP.utils.getEngineURL("/api/feed"),
				selector, 
				null, callback, errorcallback, function(data) {
					var items = data.items;
					if (data.items && data.items.length) {
						data.items = [];
					
						for(var i = 0; i < items.length; i++) {
							// data.items.push('1');
							data.items.push(new models.FeedItem(items[i]));
						}						
					}
				});
		},
		readItem: function(oid, callback, errorcallback) {
			UWAP._request(
				'GET', UWAP.utils.getEngineURL("/api/feed/item/" + oid),
				null, 
				null, callback, errorcallback);
		}
	};


	UWAP.data = {

		get: function (url, options, callback, errorcallback) {

			var data = {};
			data.url = url;
			data.returnTo = window.location.href;
			data.appid = UWAP.utils.appid;

			options = options || {};
			


			UWAP._request(
				'POST', UWAP.utils.getEngineURL("/api/rest"),
				data,
				options, 
				callback, errorcallback);
		}
		
	};

	UWAP.people = {
		query: function(realm, query, callback, errorcallback) {
			UWAP._request(
				'GET', UWAP.utils.getEngineURL("/api/people/query/" + realm + '?query=' + encodeURIComponent(query)),
				null, 
				null, callback, errorcallback);
		},
		listRealms: function(callback, errorcallback) {
			UWAP._request(
				'GET', UWAP.utils.getEngineURL("/api/people/realms"),
				null, 
				null, callback, errorcallback);
		}
	};

	UWAP.groups = {
		get: function(gid, callback, errorcallback) {
			UWAP._request(
				'GET', UWAP.utils.getEngineURL("/api/group/" + gid),
				null, 
				null, callback, errorcallback);
		},
		listPublic: function(callback, errorcallback) {
			UWAP._request(
			 	'GET', UWAP.utils.getEngineURL("/api/groups/public"),
			 	null,
			 	null, callback, errorcallback);
		},
		subscribe: function(groupid, callback, errorcallback) {
			UWAP._request(
			 	'POST', UWAP.utils.getEngineURL("/api/group/" + groupid + '/subscription'),
			 	true,
			 	null, callback, errorcallback);
		},
		unsubscribe: function(groupid, callback, errorcallback) {
			UWAP._request(
			 	'POST', UWAP.utils.getEngineURL("/api/group/" + groupid + '/subscription'),
			 	false,
			 	null, callback, errorcallback);
		},
		listMyGroups: function(callback, errorcallback) {
			UWAP._request(
			 	'GET', UWAP.utils.getEngineURL("/api/groups"),
			 	null,
			 	null, callback, errorcallback);
		},
		addGroup: function(object, callback, errorcallback) {
			UWAP._request(
			 	'POST', UWAP.utils.getEngineURL("/api/groups"),
			 	object, 
			 	null, callback, errorcallback);
		},
		updateGroup: function(groupid, object, callback, errorcallback) {
			UWAP._request(
			 	'POST', UWAP.utils.getEngineURL("/api/group/" + groupid),
			 	object, 
			 	null, callback, errorcallback);
		},
		removeGroup: function(groupid, callback, errorcallback) {
			UWAP._request(
			 	'DELETE', UWAP.utils.getEngineURL("/api/group/" + groupid),
			 	null,
			 	null, callback, errorcallback);
		},
		addMember: function(groupid, user, callback, errorcallback) {
			UWAP._request(
			 	'POST', UWAP.utils.getEngineURL("/api/group/" + groupid + '/members'),
			 	user, 
			 	null, callback, errorcallback);
		},
		removeMember: function(groupid, userid, callback, errorcallback) {
			UWAP._request(
			 	'DELETE', UWAP.utils.getEngineURL("/api/group/" + groupid + '/member/' + userid),
			 	null,
			 	null, callback, errorcallback);
		},
		updateMember: function(groupid, userid, obj, callback, errorcallback) {
			UWAP._request(
			 	'POST', UWAP.utils.getEngineURL("/api/group/" + groupid + '/member/' + userid),
			 	obj,
			 	null, callback, errorcallback);
		}
	};






	UWAP.appconfig = {
		list: function(callback, errorcallback) {
			UWAP._request(
				'GET', UWAP.utils.getEngineURL("/api/appconfig/apps"),
				null, 
				null, callback, errorcallback);
		},
		store: function(object, callback, errorcallback) {
			UWAP._request(
				'POST', UWAP.utils.getEngineURL("/api/appconfig/apps"),
				object, 
				null, callback, errorcallback);
		},
		updateStatus: function(id, object, callback, errorcallback) {
			 UWAP._request(
			 	'POST', 
			 	UWAP.utils.getEngineURL('/api/appconfig/app/' + id + '/status'),
			 	object, 
			 	null, callback, errorcallback);
		},
		bootstrap: function(id, template, callback, errorcallback) {
			UWAP._request(
				'POST', 
				UWAP.utils.getEngineURL('/api/appconfig/app/' + id + '/bootstrap'),
				template, 
				null, callback, errorcallback);
		},
		updateAuthzHandler: function(id, object, callback, errorcallback) {
			UWAP._request(
				'POST', 
				UWAP.utils.getEngineURL('/api/appconfig/app/' + id + '/authorizationhandler/' + object.id),
				object, 
				null, callback, errorcallback);
		},
		deleteAuthzHandler: function(appid, objectid, callback, errorcallback) {
			UWAP._request(
				'DELETE', 
				UWAP.utils.getEngineURL('/api/appconfig/app/' + id + '/authorizationhandler/' + object.id),
				null, 
				null, callback, errorcallback);
		},
		check: function(id, callback, errorcallback) {
			UWAP._request(
				'GET', 
				UWAP.utils.getEngineURL('/api/appconfig/check/' + id),
				null, 
				null, callback, errorcallback);
		},
		get: function(id, callback, errorcallback) {
			UWAP._request(
				'GET', 
				UWAP.utils.getEngineURL('/api/appconfig/app/' + id),
				null, 
				null, callback, errorcallback);
		}
	};




	/*
	 * ------- ------- ------- ------- ------- --------
	 * The rest is used only by internal built-in apps!
	 * ------- ------- ------- ------- ------- -------- 
	 */



	UWAP.logs = {
		get: function(after, filters, callback, errorcallback) {

			filters = filters || [];
			// console.log("Fitlers", filters);
			UWAP._request(
			 	'GET', 
			 	'/_/api/logs.php?after=' + after + '&filters=' + encodeURIComponent(JSON.stringify(filters)),
			 	null,
			 	null, callback, errorcallback);
		}
	}


	UWAP.appconfig2 = {
		list: function(callback, errorcallback) {
			
			$.ajax({
				type: 'GET',
				url: '/_/api/appconfig.php/apps',
				dataType: 'json',
				// data: JSON.stringify({ "command": "on" }),
				// processData: false,
				success: function(result, textStatus, jqXHR) {
					// console.log('Response appconfig get()');
					// console.log(result);
					if (result.status === 'ok') {
						callback(result.data);
					} else {
						if  (typeof errorcallback === 'function') {
							errorcallback(result.message);
						}
						// console.log('Data request error (server side): ' + result.message);
					}

				},
				error: function(err) {
					if  (typeof errorcallback === 'function') {
						errorcallback(err);
					}
					// console.log('Data request error (client side): ' + err);
				}
			});

		},

		store: function(object, callback, errorcallback) {

			$.ajax({
				type: 'POST',
				url: '/_/api/appconfig.php/apps',
				dataType: 'json',
				contentType: "application/json",
				data: JSON.stringify(object),
				processData: false,
				success: function(result, textStatus, jqXHR) {
					// console.log('Response data save()');
					// console.log(result);
					if (result.status === 'ok') {
						callback(result.data);
					} else {
						if  (typeof errorcallback === 'function') {
							errorcallback(result.message);
						}
						// console.log('Data request error (server side): ' + result.message);
					}

				},
				error: function(err) {
					if  (typeof errorcallback === 'function') {
						errorcallback(err);
					}
					// console.log('Data request error (client side): ' + err);
				}
			});

		},
		updateStatus: function(id, object, callback, errorcallback) {
			 UWAP._request(
			 	'POST', 
			 	'/_/api/appconfig.php/app/' + id + '/status',
			 	object, 
			 	null, callback, errorcallback);
		},
		bootstrap: function(id, template, callback, errorcallback) {
			UWAP._request(
				'POST', 
				'/_/api/appconfig.php/app/' + id + '/bootstrap',
				template, 
				null, callback, errorcallback);
		},

		updateAuthzHandler: function(id, object, callback, errorcallback) {
			$.ajax({
				type: 'POST',
				url: '/_/api/appconfig.php/app/' + id + '/authorizationhandler/' + object.id,
				dataType: 'json',
				contentType: "application/json",
				data: JSON.stringify(object),
				processData: false,
				success: function(result, textStatus, jqXHR) {
					// console.log('Response data save()');
					// console.log(result);
					if (result.status === 'ok') {
						callback(result.data);
					} else {
						if  (typeof errorcallback === 'function') {
							errorcallback(result.message);
						}
						// console.log('Data request error (server side): ' + result.message);
					}

				},
				error: function(err) {
					if  (typeof errorcallback === 'function') {
						errorcallback(err);
					}
					// console.log('Data request error (client side): ' + err);
				}
			});

		},
		deleteAuthzHandler: function(appid, objectid, callback, errorcallback) {
			
			$.ajax({
				type: 'DELETE',
				url: '/_/api/appconfig.php/app/' + appid + '/authorizationhandler/' + objectid,
				dataType: 'json',
				contentType: "application/json",
				// data: JSON.stringify(object),
				// processData: false,
				success: function(result, textStatus, jqXHR) {
					// console.log('Response data save()');
					// console.log(result);
					if (result.status === 'ok') {
						callback(result.data);
					} else {
						if  (typeof errorcallback === 'function') {
							errorcallback(result.message);
						}
						// console.log('Data request error (server side): ' + result.message);
					}

				},
				error: function(err) {
					if  (typeof errorcallback === 'function') {
						errorcallback(err);
					}
					// console.log('Data request error (client side): ' + err);
				}
			});

		},
		check: function(id, callback, errorcallback) {

			$.getJSON('/_/api/appconfig.php/check/' + id, null, function(result, textStatus, jqXHR) {
				// console.log('Response apiconfig check');
				// console.log(result);
				if (result.status === 'ok') {
					callback(result.data);
				} else {
					if  (typeof errorcallback === 'function') {
						errorcallback(result.message);
					}
					// console.log('Data request error (server side): ' + result.message);
				}

			}, function(err) {
				if  (typeof errorcallback === 'function') {
					errorcallback(err);
				}
				// console.log('Data request error (client side): ' + err);
			});
		},
		get: function(id, callback, errorcallback) {

			$.getJSON('/_/api/appconfig.php/app/' + id, null, function(result, textStatus, jqXHR) {
				// console.log('Response data queryOne()');
				// console.log(result);
				if (result.status === 'ok') {
					callback(result.data);
				} else {
					if  (typeof errorcallback === 'function') {
						errorcallback(result.message);
					}
					// console.log('Data request error (server side): ' + result.message);
				}

			}, function(err) {
				if  (typeof errorcallback === 'function') {
					errorcallback(err);
				}
				// console.log('Data request error (client side): ' + err);
			});
		}
	};

	UWAP.applisting = {
		list: function(callback, errorcallback) {

			$.getJSON('/_/api/applisting.php', {}, function(result, textStatus, jqXHR) {
				// console.log('Response applisting get()');
				// console.log(result);
				if (result.status === 'ok') {
					callback(result.data);
				} else {
					if  (typeof errorcallback === 'function') {
						errorcallback(result.message);
					}
					// console.log('Data request error (server side): ' + result.message);
				}

			}, function(err) {
				if  (typeof errorcallback === 'function') {
					errorcallback(err);
				}
				// console.log('Data request error (client side): ' + err);
			});
		}
	};

	return UWAP;

});


