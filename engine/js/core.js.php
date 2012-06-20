/**
 * @package UWAP
 * @description The core UWAP javascript library communicates with the UWAP server using the REST API.
 * @author Andreas Åkre Solberg
 * @copyright UNINETT AS
 * @version 1.0
 */


UWAP = {};

UWAP.utils = {};

UWAP.utils.addQueryParam = function (url, key, value) {
	var delimiter = ((url.indexOf('?') != -1) ? '&' : '?');
	if (url.charAt(url.length-1) === '?') {
		delimiter = '';
	}
	return url + delimiter + encodeURIComponent(key) + '=' + encodeURIComponent(value);
};
UWAP.utils.goAndReturn = function(url) {
	console.log("About to redirect to: " + UWAP.utils.addQueryParam(url, 'return', document.URL));
	var base = 'http://app.bridge.uninett.no';
	window.location = UWAP.utils.addQueryParam(url, 'return', document.URL);	
}



UWAP.messenger = {};
UWAP.messenger.send = function(msg) {
	if (UWAP.messenger.receiver) {
		UWAP.messenger.receiver(msg);
	} else {
		console.error("Could not deliver message from iframe, because listener was not setup.");
	}

}

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
		var parameters = {};
		// parameters.store = "save";
		parameters.store = JSON.stringify(object);


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
	} ,
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

		var parameters = {};
		parameters.url = url;
		parameters.returnTo = window.location.href;

		options = options || {};

		if (options.handler) {
			parameters.handler = options.handler;
		}
		if (options.xml) {
			parameters.xml = (options.xml ? '1' : '0');
		}

		console.log("Performing GET request to " + url);
		console.log("Parameters");
		console.log(parameters);

		$.getJSON('/_/api/data.php', parameters, function(result, textStatus, jqXHR) {
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


