
/**
 * @package UWAP
 * @description The core UWAP javascript library communicates with the UWAP server using the REST API.
 * @author Andreas Åkre Solberg
 * @copyright UNINETT AS
 * @version 1.0
 */

<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/lib/autoload.php');
header('Content-Type: application/javascript');

?>

UWAP = {};
UWAP.utils = {};
UWAP.utils.hostname = '<?php echo Config::hostname(); ?>';
UWAP.utils.scheme = '<?php echo Config::scheme(); ?>';

UWAP.utils.addQueryParam = function (url, key, value) {
	var delimiter = ((url.indexOf('?') != -1) ? '&' : '?');
	if (url.charAt(url.length-1) === '?') {
		delimiter = '';
	}
	return url + delimiter + encodeURIComponent(key) + '=' + encodeURIComponent(value);
};
UWAP.utils.goAndReturn = function(url) {
	console.log("About to redirect to: " + UWAP.utils.addQueryParam(url, 'return', document.URL));
	var base = UWAP.utils.scheme + '://core.' + UWAP.utils.hostname + '/';
	window.location = UWAP.utils.addQueryParam(url, 'return', document.URL);	
}



UWAP.messenger = {};
UWAP.messenger.send = function(msg) {
	if (UWAP.messenger.receiver) {
		UWAP.messenger.receiver(msg);
	} else {
		console.error("Could not deliver message from iframe, because listener was not setup.");
	}

};

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
UWAP._request = function(method, url, data, options, callback, errorcallback) {
	method = method || 'GET';

	var ar = {
		type: method,
		url: url,
		dataType: 'json',
		success: function(result, textStatus, jqXHR) {
			console.log('Response _request response reviced()');
			console.log(result);
			if (result.status === 'ok') {
				if  (typeof callback === 'function') {
					callback(result.data);
				}
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
		}
	};

	if (data) {
		ar.data = JSON.stringify(data);
		ar.processData = false;
		ar.dataType = 'json';
	}
	$.ajax(ar);

};


UWAP.auth = {

	require: function (callbackSuccess) {
		
		$.getJSON('/_/api/auth.php', function(data, textStatus, jqXHR) {
			console.log('Response auth require()');
			console.log(data);
			if (data.status === 'ok') {
				callbackSuccess(data.user);
			} else {
				console.log("goAndReturn");
				UWAP.utils.goAndReturn('/_/login');
			}

		});
		
	},

	checkPassive: function (callbackSuccess, callbackNo) {

		$.getJSON('/_/api/auth.php', function(data, textStatus, jqXHR) {
			console.log('Response auth check()');
			console.log(data);
			if (data.status === 'ok') {
				callbackSuccess(data.user);
			} else {
				
				UWAP.messenger.receiver = function(msg) {

					if (msg.type === "passiveAuth" && msg.status === "success") {

						UWAP.auth.check(callbackSuccess, callbackNo)

					} else {
						callbackNo();
					}

					console.log("Received response. Juhu ", msg);
					delete UWAP.messenger.receiver;
					// $("body iframe.uwap_messenger_iframe").remove();

				};
				$("body").prepend('<iframe class="uwap_messenger_iframe" style="display: none" src="/_/login?passive=true"></iframe>');

			}

		});

	},

	check: function (callbackSuccess, callbackNo) {
		
		$.getJSON('/_/api/auth.php', function(data, textStatus, jqXHR) {
			console.log('Response auth check()');
			console.log(data);
			if (data.status === 'ok') {
				callbackSuccess(data.user);
			} else {
				callbackNo();
			}
		});
		
	}
	
};

UWAP.store = {
	save: function(object, callback, errorcallback) {
		var parameters = {};
		parameters.op = "save";
		parameters.object = JSON.stringify(object);

		$.getJSON('/_/api/storage.php', parameters, function(result, textStatus, jqXHR) {
			console.log('Response data save()');
			console.log(result);
			if (result.status === 'ok') {
				callback(result.data);
			} else {
				if  (typeof errorcallback === 'function') {
					errorcallback(result.message);
				}
				console.log('Data request error (server side): ' + result.message);
			}

		}, function(err) {
			if  (typeof errorcallback === 'function') {
				errorcallback(err);
			}
			console.log('Data request error (client side): ' + err);
		});
	},
	remove: function(object, callback, errorcallback) {
		var parameters = {};
		parameters.op = "remove";
		parameters.object = JSON.stringify(object);

		$.getJSON('/_/api/storage.php', parameters, function(result, textStatus, jqXHR) {
			console.log('Response data remove()');
			console.log(result);
			if (result.status === 'ok') {
				callback();
			} else {
				if  (typeof errorcallback === 'function') {
					errorcallback(result.message);
				}
				console.log('Data request error (server side): ' + result.message);
			}

		}, function(err) {
			if  (typeof errorcallback === 'function') {
				errorcallback(err);
			}
			console.log('Data request error (client side): ' + err);
		});
	},
	queryOne: function(query, callback, errorcallback) {
		var parameters = {};
		parameters.op = "queryOne";
		parameters.query = JSON.stringify(query);

		$.getJSON('/_/api/storage.php', parameters, function(result, textStatus, jqXHR) {
			console.log('Response data queryOne()');
			console.log(result);
			if (result.status === 'ok') {
				callback(result.data);
			} else {
				if  (typeof errorcallback === 'function') {
					errorcallback(result.message);
				}
				console.log('Data request error (server side): ' + result.message);
			}

		}, function(err) {
			if  (typeof errorcallback === 'function') {
				errorcallback(err);
			}
			console.log('Data request error (client side): ' + err);
		});
	},
	queryList: function(query, callback, errorcallback) {
		var parameters = {};
		parameters.op = "queryList";
		parameters.query = JSON.stringify(query);

		$.getJSON('/_/api/storage.php', parameters, function(result, textStatus, jqXHR) {
			console.log('Response data queryList()');
			console.log(result);
			if (result.status === 'ok') {
				callback(result.data);
			} else {
				if  (typeof errorcallback === 'function') {
					errorcallback(result.message);
				}
				console.log('Data request error (server side): ' + result.message);
			}

		}, function(err) {
			if  (typeof errorcallback === 'function') {
				errorcallback(err);
			}
			console.log('Data request error (client side): ' + err);
		});
	}
};

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


UWAP.appconfig = {
	list: function(callback, errorcallback) {
		
		$.ajax({
			type: 'GET',
			url: '/_/api/appconfig.php/apps',
			dataType: 'json',
			// data: JSON.stringify({ "command": "on" }),
			// processData: false,
			success: function(result, textStatus, jqXHR) {
				console.log('Response appconfig get()');
				console.log(result);
				if (result.status === 'ok') {
					callback(result.data);
				} else {
					if  (typeof errorcallback === 'function') {
						errorcallback(result.message);
					}
					console.log('Data request error (server side): ' + result.message);
				}

			},
			error: function(err) {
				if  (typeof errorcallback === 'function') {
					errorcallback(err);
				}
				console.log('Data request error (client side): ' + err);
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
				console.log('Response data save()');
				console.log(result);
				if (result.status === 'ok') {
					callback(result.data);
				} else {
					if  (typeof errorcallback === 'function') {
						errorcallback(result.message);
					}
					console.log('Data request error (server side): ' + result.message);
				}

			},
			error: function(err) {
				if  (typeof errorcallback === 'function') {
					errorcallback(err);
				}
				console.log('Data request error (client side): ' + err);
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
				console.log('Response data save()');
				console.log(result);
				if (result.status === 'ok') {
					callback(result.data);
				} else {
					if  (typeof errorcallback === 'function') {
						errorcallback(result.message);
					}
					console.log('Data request error (server side): ' + result.message);
				}

			},
			error: function(err) {
				if  (typeof errorcallback === 'function') {
					errorcallback(err);
				}
				console.log('Data request error (client side): ' + err);
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
				console.log('Response data save()');
				console.log(result);
				if (result.status === 'ok') {
					callback(result.data);
				} else {
					if  (typeof errorcallback === 'function') {
						errorcallback(result.message);
					}
					console.log('Data request error (server side): ' + result.message);
				}

			},
			error: function(err) {
				if  (typeof errorcallback === 'function') {
					errorcallback(err);
				}
				console.log('Data request error (client side): ' + err);
			}
		});

	},
	check: function(id, callback, errorcallback) {

		$.getJSON('/_/api/appconfig.php/check/' + id, null, function(result, textStatus, jqXHR) {
			console.log('Response apiconfig check');
			console.log(result);
			if (result.status === 'ok') {
				callback(result.data);
			} else {
				if  (typeof errorcallback === 'function') {
					errorcallback(result.message);
				}
				console.log('Data request error (server side): ' + result.message);
			}

		}, function(err) {
			if  (typeof errorcallback === 'function') {
				errorcallback(err);
			}
			console.log('Data request error (client side): ' + err);
		});
	},
	get: function(id, callback, errorcallback) {

		$.getJSON('/_/api/appconfig.php/app/' + id, null, function(result, textStatus, jqXHR) {
			console.log('Response data queryOne()');
			console.log(result);
			if (result.status === 'ok') {
				callback(result.data);
			} else {
				if  (typeof errorcallback === 'function') {
					errorcallback(result.message);
				}
				console.log('Data request error (server side): ' + result.message);
			}

		}, function(err) {
			if  (typeof errorcallback === 'function') {
				errorcallback(err);
			}
			console.log('Data request error (client side): ' + err);
		});
	}
};

UWAP.applisting = {
	list: function(callback, errorcallback) {

		$.getJSON('/_/api/applisting.php', {}, function(result, textStatus, jqXHR) {
			console.log('Response applisting get()');
			console.log(result);
			if (result.status === 'ok') {
				callback(result.data);
			} else {
				if  (typeof errorcallback === 'function') {
					errorcallback(result.message);
				}
				console.log('Data request error (server side): ' + result.message);
			}

		}, function(err) {
			if  (typeof errorcallback === 'function') {
				errorcallback(err);
			}
			console.log('Data request error (client side): ' + err);
		});
	}
};

UWAP.data = {

	get: function (url, options, callback, errorcallback) {

		options = options || {};
		options.url = url;
		options.returnTo = window.location.href;

		// var parameters = {};
		// parameters.url = url;
		// parameters.returnTo = window.location.href;

		// options = options || {};

		// if (options.handler) {
		// 	parameters.handler = options.handler;
		// }
		// if (options.xml) {
		// 	parameters.xml = (options.xml ? '1' : '0');
		// }

		console.log("Performing GET request to " + url);
		console.log("Parameters");
		console.log(options);

		$.getJSON('/_/api/data.php', {args: JSON.stringify(options)}, function(result, textStatus, jqXHR) {
			console.log('Response data get()');
			console.log(result);
			if (result.status === 'ok') {
				callback(result.data);
			} else if (result.status === 'redirect') {
				window.location.href = result.url;
			} else {
				if  (typeof errorcallback === 'function') {
					errorcallback(result.message);
				}
				console.log('Data request error (server side): ' + result.message);
			}

		}, function(err) {
			if  (typeof errorcallback === 'function') {
				errorcallback(err);
			}
			console.log('Data request error (client side): ' + err);
		});
	},




	hget: function (url, options, callback) {
		$.getJSON('/_/api/hdata.php?url=' + encodeURIComponent(url) + '&xml=1', function(result, textStatus, jqXHR) {
			console.log('Response data get()');
			console.log(result);
			if (result.status === 'ok') {
				callback(result.data);
			} else {
				console.log('Error');
			}

		}, function() {
			console.log('Error 2');
		});
	},
	oget: function (url, options, callback) {
		console.log('oget to ' + url);
		$.getJSON('/_/api/dataoauth.php?url=' + encodeURIComponent(url) + '&return=' + encodeURIComponent(window.location) + '&xml=1', 
				function(result, textStatus, jqXHR) {
				
			console.log('Response data get()');
			console.log(result);
			if (result.status === 'ok') {
				callback(result.data);
			} else if (result.status === 'redirect') {
				window.location = result.url;
			} else {
				console.log('Error getting data from ' + url);
			}

		}, function() {
			console.log('Error 2');
		});
	}
	
};


