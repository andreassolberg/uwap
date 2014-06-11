
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

	UWAP.utils.hasToken = function() {
		var token = jso.jso_getToken("uwap");
		return (token !== null);
	}

	UWAP.utils.hash = function(str){
		var hash = 0;
		if (str.length == 0) return hash;
		for (i = 0; i < str.length; i++) {
			char = str.charCodeAt(i);
			hash = ((hash<<5)-hash)+char;
			hash = hash & hash; // Convert to 32bit integer
		}
		return Math.abs(hash).toString(36);
		// return hash.toString(36);
	}

	/*
	 * Returns a random string
	 */
	UWAP.utils.uuid = function() {
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    		var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
		    return v.toString(16);
		});
	}


	UWAP.utils.escape = function(string) {
		var pre = document.createElement('pre');
		var text = document.createTextNode( string );
		pre.appendChild(text);
		return pre.innerHTML;
	}

	UWAP.utils.stack = function(msg) {
		var e = new Error('dummy');
		var stack = e.stack.replace(/^[^\(]+?[\n$]/gm, '')
		    .replace(/^\s+at\s+/gm, '')
		    .replace(/^Object.<anonymous>\s*\(/gm, '{anonymous}()@')
		    .split('\n');
		console.log(msg, stack);
	}


	UWAP.Error = function(message) {
		this.message = message;
	}

	// jso.jso_registerRedirectHandler(function(p, callback) {

	// 	console.log("About to redirect to: ", p);
	// 	// callback();

	// });


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
		// console.log("Registering redirect handler...");
	}


	UWAP.utils.addQueryParam = function (url, key, value) {
		var delimiter = ((url.indexOf('?') != -1) ? '&' : '?');
		if (url.charAt(url.length-1) === '?') {
			delimiter = '';
		}
		return url + delimiter + encodeURIComponent(key) + '=' + encodeURIComponent(value);
	};
	// UWAP.utils.goAndReturn = function(url) {
	// 	// console.log("About to redirect to: " + UWAP.utils.addQueryParam(url, 'return', document.URL));
	// 	var base = UWAP.utils.scheme + '://auth.' + UWAP.utils.hostname + '/';
	// 	window.location = UWAP.utils.addQueryParam(url, 'return', document.URL);	
	// }

	// UWAP.utils.getAPIurl = function(path) {
	// 	var base = UWAP.utils.scheme + '://api.' + UWAP.utils.enginehostname + '';
	// 	return base + path;
	// }

	UWAP.utils.getAuthURL = function(path) {
		var base = UWAP.utils.scheme + '://auth.' + UWAP.utils.enginehostname + '';
		return base + path;
	}

	UWAP.utils.getAPIurl = function(path) {
		var base = UWAP.utils.scheme + '://api.' + UWAP.utils.enginehostname + '';
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


	var redirect_uri = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
	var passive_redirect_uri = window.location.protocol + '//' + window.location.hostname + '/_/passiveResponse';

	// console.log("Redirect URI is " + redirect_uri);

	var client_id = UWAP.utils.appid;
	jso.jso_configure({
		"uwap": {
			client_id: client_id,
			authorization: UWAP.utils.getAuthURL('/oauth/authorization'),
			redirect_uri: redirect_uri,
			passive_redirect_uri: passive_redirect_uri
		}
	}, {debug: false});



	/**
	 * A generic protocol request wrapper function.
	 * @param  {string}   method        The HTTP Method to use. GET is default
	 * @param  {string}   url           The relative URL
	 * @param  {object}   data          Optionally an object to send.
	 * @param  {object}   options       A set of options
	 * @param  {Function} callback      Success callback
	 * @return {void}                 Returns undefined
	 */
	UWAP._request = function(method, url, data, options, callback, dataprocess) {
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

				// TODO: Not only 200 is OK. How to check if status is within the OK range?
				// IS the error callback called always then the status is not ok? Probably. then this should work.
				// Remote the if sentence then,
				if (jqXHR.status === 200) {

					console.log("=====> jqXHR.status === 200");
					if (typeof callback === 'function') {
						var x = result;
						if (typeof dataprocess === 'function') {
							var x = dataprocess(result);
						} 
						callback(x, jqXHR.status + ' ' + jqXHR.statusText, jqXHR.getAllResponseHeaders());		
					}


					// REDIRECT
				} else if (false) {


					alert('When is this thing used??? --');

					// console.log("Redirecting user to " + result.url);
					window.location.href = result.url;

				} else {

					console.log("=====> jqXHR.status === 200");
					var msg = jqXHR.status + ' ' + jqXHR.statusText;
					if (result && result.message) {
						msg += ': ' + result.message;
					}
					console.error('Data request error (server side 1): ' + msg);
					callback(new UWAP.Error(msg));

				}


				// if (result.status === 'ok') {

				// } else if (result.status === 'redirect') {

				// } else {

				// }	
				
			},
			error: function(jqXHR, textStatus, errorThrown) {

				var msg = jqXHR.status + ' ' + jqXHR.statusText + ' ' + textStatus;

				console.error('Data request error (server side 2): ' + msg);
				callback(new UWAP.Error(msg));


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

			callback(new UWAP.Error(exception));		
			console.error("Error performing XHTTP Request: ", exception);

		}

	};





	UWAP.auth = {

		getProviderConfig: function(callbackSuccess) {
			var url = UWAP.utils.getAuthURL("/providerconfig");
			console.log("About to get ", url);
			$.getJSON(url, callbackSuccess);
		},
		require: function (callbackSuccess, options) {
			options = options || {};
			UWAP._request(
				'GET', 
				UWAP.utils.getAPIurl("/userinfo"),
				null, options, callbackSuccess);
		},
		check: function (callbackSuccess) {
			var options = {
				"jso_allowia": false
			};
			if (!UWAP.utils.hasToken()) {
				callbackNo();
				return;
			}
			UWAP._request(
				'GET', 
				UWAP.utils.getAPIurl("/userinfo"),
				null,options, callbackSuccess);
		},
		logout: function() {
			jso.jso_wipe();
		},

		// TODO: Upgrade to support OAUTH
		checkPassive: function (callbackSuccess, callbackNo) {
			

			console.log("checkPassive()");
			UWAP._request(
				'GET', 
				UWAP.utils.getAPIurl("/userinfo"),
				null, {
					"jso_allowia": false
				}, function(response) {

					console.log("UWAP._request callback 1", response);

					if (! (response instanceof UWAP.Error)) {
						console.log("Not an error object.");
						return callbackSuccess(response);
					}


					jso.jso_ensureTokensPassive({"uwap": false}, function() {

						console.log(" [====] Callback success from jso_ensureTokensPassive() ")

						UWAP._request(
							'GET', 
							UWAP.utils.getAPIurl("/userinfo"),
							null, {
								"jso_allowia": false
							}, function(response) {

								if (response instanceof UWAP.Error) {
									return callbackNo();
								}
								return callbackSuccess(response);

							}
						);

					}, function(error) {
						console.log(" [====] Callback failed from jso_ensureTokensPassive() ");
						callbackNo(error);
					});


					// UWAP.messenger.receiver = function(msg) {

					// 	if (msg.type === "passiveAuth" && msg.status === "success") {

					// 		UWAP.auth.check(callbackSuccess)

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
		save: function(object, callback) {
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/store"),
				{
					op: "save",
					object: object
				}, 
				null, callback);
		},
		remove: function(object, callback) {
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/store"),
				{
					op: "remove",
					object: object
				}, 
				null, callback);
		},
		queryOne: function(query, callback) {
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/store"),
				{
					op: "queryOne",
					query: query
				}, 
				null, callback);
		},
		queryList: function(query, callback) {
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/store"),
				{
					op: "queryList",
					query: query
				}, 
				null, callback);
		}
	};

	UWAP.feed = {
		notificationsMarkRead: function(ids, callback) {
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/feed/notifications/markread"),
				ids, 
				null, callback);
		},
		upcoming: function(selector, callback) {
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/feed/upcoming"),
				selector, 
				null, callback, function(data) {

					return new models.Feed(data);

					// var items = data.items;
					// if (data.items && data.items.length) {
					// 	data.items = [];
					
					// 	for(var i = 0; i < items.length; i++) {
					// 		// data.items.push('1');
					// 		data.items.push(new models.FeedItem(items[i]));
					// 	}						
					// }
				});
		},
		notifications: function(selector, callback) {
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/feed/notifications"),
				selector, 
				null, callback);
		},
		post: function(object, callback) {
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/feed/post"),
				object, 
				null, callback);
		},
		respond: function(object, callback) {
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/feed/item/" + object.inresponseto + '/response'),
				object, 
				null, callback, function(data) {
					// console.log("    ==> CREATING new feed.");
					var x = new models.Feed(data);
					// console.log(x);
					return x;
				});
		},
		delete: function(oid, callback) {
			UWAP._request(
				'DELETE', UWAP.utils.getAPIurl("/feed/item/" + oid),
				null, 
				null, callback);
		},
		read: function(selector, callback) {
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/feed"),
				selector, 
				null, callback, function(data) {
					console.log("    ==> CREATING new feed.");
					var x = new models.Feed(data);
					// console.log(x);
					return x;
				}
			);
		},
		readItem: function(oid, callback) {
			UWAP._request(
				'GET', UWAP.utils.getAPIurl("/feed/item/" + oid),
				null, 
				null, callback, function(data) {
					// console.log("    ==> CREATING new feed.");
					var x = new models.Feed(data);
					// console.log(x);
					return x;
				}
			);
		}
	};


	UWAP.data = {

		get: function (url, options, callback) {

			var data = {};
			data.url = url;
			data.returnTo = window.location.href;
			data.appid = UWAP.utils.appid;

			options = options || {};
			options.auth = false;
			if (options.handler) {
				data.handler = options.handler;
			}

			console.log("UWAP.data options", options);
			
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/rest"),
				data,
				options, 
				callback);
		},
		soa: function (url, options, callback) {

			var data = {};
			data.url = url;
			data.returnTo = window.location.href;
			data.appid = UWAP.utils.appid;



			options = options || {};
			data.options = options;

			console.log("UWAP.data [" + url + "] options", options);
			
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/soa"),
				data,
				options, 
				callback);
		}
		
	};

	UWAP.people = {
		query: function(realm, query, callback) {
			UWAP._request(
				'GET', UWAP.utils.getAPIurl("/people/query/" + realm + '?query=' + encodeURIComponent(query)),
				null, 
				null, callback);
		},
		listRealms: function(callback) {
			UWAP._request(
				'GET', UWAP.utils.getAPIurl("/people/realms"),
				null, 
				null, callback);
		}
	};

	UWAP.groups = {
		get: function(gid, callback) {
			UWAP._request(
				'GET', UWAP.utils.getAPIurl("/group/" + gid),
				null, 
				null, callback);
		},
		getMembers: function(gid, callback) {
			UWAP._request(
				'GET', UWAP.utils.getAPIurl("/group/" + gid + "/members"),
				null, 
				null, callback);
		},
		listPublic: function(callback) {
			UWAP._request(
			 	'GET', UWAP.utils.getAPIurl("/groups/public"),
			 	null,
			 	null, callback);
		},
		listSubscriptions: function(callback) {
			UWAP._request(
			 	'GET', UWAP.utils.getAPIurl("/userinfo/subscriptions"),
			 	null,
			 	null, callback);
		},
		subscribe: function(groupid, callback) {
			UWAP._request(
			 	'POST', UWAP.utils.getAPIurl("/group/" + groupid + '/subscription'),
			 	{subscribe: true},
			 	null, callback);
		},
		unsubscribe: function(groupid, callback) {
			UWAP._request(
			 	'POST', UWAP.utils.getAPIurl("/group/" + groupid + '/subscription'),
			 	{subscribe: false},
			 	null, callback);
		},
		listMyGroups: function(callback) {
			UWAP._request(
			 	'GET', UWAP.utils.getAPIurl("/groups"),
			 	null,
			 	null, callback);
		},
		addGroup: function(object, callback) {
			UWAP._request(
			 	'POST', UWAP.utils.getAPIurl("/groups"),
			 	object, 
			 	null, callback);
		},
		updateGroup: function(groupid, object, callback) {
			UWAP._request(
			 	'POST', UWAP.utils.getAPIurl("/group/" + groupid),
			 	object, 
			 	null, callback);
		},
		removeGroup: function(groupid, callback) {
			UWAP._request(
			 	'DELETE', UWAP.utils.getAPIurl("/group/" + groupid),
			 	null,
			 	null, callback);
		},
		addMember: function(groupid, user, callback) {
			UWAP._request(
			 	'POST', UWAP.utils.getAPIurl("/group/" + groupid + '/members'),
			 	user, 
			 	null, callback);
		},
		removeMember: function(groupid, userid, callback) {
			UWAP._request(
			 	'DELETE', UWAP.utils.getAPIurl("/group/" + groupid + '/member/' + userid),
			 	null,
			 	null, callback);
		},
		updateMember: function(groupid, userid, obj, callback) {
			UWAP._request(
			 	'POST', UWAP.utils.getAPIurl("/group/" + groupid + '/member/' + userid),
			 	obj,
			 	null, callback);
		}
	};






	UWAP.appconfig = {
		list: function(callback) {
			UWAP._request(
				'GET', UWAP.utils.getAPIurl("/appconfig/clients"),
				null, 
				null, callback);
		},
		get: function(id, callback) {
			UWAP._request(
				'GET', 
				UWAP.utils.getAPIurl('/appconfig/client/' + id),
				null, 
				null, callback);
		},
		updateStatus: function(id, object, callback) {
			UWAP._request(
				'POST', 
				UWAP.utils.getAPIurl('/appconfig/client/' + id + '/status'),
				object, 
				null, callback);
		},
		bootstrap: function(id, template, callback) {
			UWAP._request(
				'POST', 
				UWAP.utils.getAPIurl('/appconfig/client/' + id + '/bootstrap'),
				template, 
				null, callback);
		},
		check: function(id, callback) {
			UWAP._request(
				'GET', 
				UWAP.utils.getAPIurl('/appconfig/check/' + id),
				null, 
				null, callback);
		},
		updateAuthzHandler: function(id, object, callback) {
			UWAP._request(
				'POST', 
				UWAP.utils.getAPIurl('/appconfig/client/' + id + '/authorizationhandler/' + object.id),
				object, 
				null, callback);
		},
		
		deleteAuthzHandler: function(appid, objectid, callback) {
			UWAP._request(
				'DELETE', 
				UWAP.utils.getAPIurl('/appconfig/client/' + appid + '/authorizationhandler/' + objectid),
				null, 
				null, callback);
		},

		store: function(object, callback) {
			UWAP._request(
				'POST', UWAP.utils.getAPIurl("/appconfig/clients"),
				object, 
				null, callback);
		},

		updateProxy: function(id, proxy, callback) {
			UWAP._request(
				'POST', 
				UWAP.utils.getAPIurl('/appconfig/client/' + id + '/proxy'),
				proxy, 
				null, callback);
		},

		getAppClients: function(id, callback) {
			UWAP._request(
				'GET', 
				UWAP.utils.getAPIurl('/appconfig/client/' + id + '/clients'),
				null, 
				null, callback);
		},

		authorizeClient: function(id, clientid, authz, callback) {
			UWAP._request(
				'POST', 
				UWAP.utils.getAPIurl('/appconfig/client/' + id + '/client/' + clientid + '/authorization'),
				authz, 
				null, callback);
		},

		requestScopes: function(clientid, scopes, callback) {
			UWAP._request(
				'POST', 
				UWAP.utils.getAPIurl('/appconfig/client/' + clientid + '/scopes'),
				scopes, 
				null, callback);
		},


		getPublicAPIs: function(clientid, query, callback) {
			UWAP._request(
				'POST', 
				UWAP.utils.getAPIurl('/appconfig/client/' + clientid + '/publicapis'),
				query, 
				null, callback);
		},
		getAuthorizedAPIs: function(clientid, callback) {
			UWAP._request(
				'GET', 
				UWAP.utils.getAPIurl('/appconfig/client/' + clientid + '/authorizedapis'),
				null, 
				null, callback);
		},


		// query: function(object, callback) {
		// 	console.error('››› NOT IMPLEMENTED updates to this API call for appconfig after refactoring');
		// 	UWAP._request(
		// 		'POST', UWAP.utils.getAPIurl("/appconfig/apps/query"),
		// 		object, 
		// 		null, callback);
		// },

		// storeClient: function(object, callback) {
		// 	console.error('››› NOT IMPLEMENTED updates to this API call for appconfig after refactoring');
		// 	UWAP._request(
		// 		'POST', UWAP.utils.getAPIurl("/appconfig/clients"),
		// 		object, 
		// 		null, callback);
		// },




		// getClient: function(id, callback) {
		// 	console.error('››› NOT IMPLEMENTED updates to this API call for appconfig after refactoring');
		// 	UWAP._request(
		// 		'GET', 
		// 		UWAP.utils.getAPIurl('/appconfig/client/' + id),
		// 		null, 
		// 		null, callback);
		// },

		getView: function(id, callback) {
			console.error('››› NOT IMPLEMENTED updates to this API call for appconfig after refactoring');
			UWAP._request(
				'GET', 
				UWAP.utils.getAPIurl('/appconfig/view/' + id),
				null, 
				null, callback);
		}


	};




	/*
	 * ------- ------- ------- ------- ------- --------
	 * The rest is used only by internal built-in apps!
	 * ------- ------- ------- ------- ------- -------- 
	 */



	UWAP.logs = {
		get: function(after, filters, callback) {

			filters = filters || [];
			// console.log("Fitlers", filters);
			UWAP._request(
			 	'GET', 
			 	'/_/logs.php?after=' + after + '&filters=' + encodeURIComponent(JSON.stringify(filters)),
			 	null,
			 	null, callback);
		}
	}


	UWAP.appconfig2 = {
		list: function(callback) {
			
			$.ajax({
				type: 'GET',
				url: '/_/appconfig.php/apps',
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

		store: function(object, callback) {

			$.ajax({
				type: 'POST',
				url: '/_/appconfig.php/apps',
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
		updateStatus: function(id, object, callback) {
			 UWAP._request(
			 	'POST', 
			 	'/_/appconfig.php/app/' + id + '/status',
			 	object, 
			 	null, callback);
		},
		bootstrap: function(id, template, callback) {
			UWAP._request(
				'POST', 
				'/_/appconfig.php/app/' + id + '/bootstrap',
				template, 
				null, callback);
		},

		updateAuthzHandler: function(id, object, callback) {
			$.ajax({
				type: 'POST',
				url: '/_/appconfig.php/app/' + id + '/authorizationhandler/' + object.id,
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
		deleteAuthzHandler: function(appid, objectid, callback) {
			
			$.ajax({
				type: 'DELETE',
				url: '/_/appconfig.php/app/' + appid + '/authorizationhandler/' + objectid,
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
		check: function(id, callback) {

			$.getJSON('/_/appconfig.php/check/' + id, null, function(result, textStatus, jqXHR) {
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
		get: function(id, callback) {

			$.getJSON('/_/appconfig.php/app/' + id, null, function(result, textStatus, jqXHR) {
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
		list: function(callback) {

			$.getJSON('/_/applisting.php', {}, function(result, textStatus, jqXHR) {
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


