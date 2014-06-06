define(function(require, exports, module) {

	var 

		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		jso = require('uwap-core/js/oauth'),
		App = require('controllers/App')
		;




	document.addEventListener('deviceready', function() {

		UWAP.utils.enginehostname = 'app.bridge.uninett.no';
		UWAP.utils.hostname = 'localhost';
		UWAP.utils.scheme = 'http';
		UWAP.utils.appid = 'uwapfeedapp';
		// UWAP.utils.appid = 'feed';

		var redirect_uri = window.location.protocol + '//' + window.location.hostname + 
			window.location.pathname;
		var passive_redirect_uri = window.location.protocol + '//' + window.location.hostname + '/_/passiveResponse';
		redirect_uri = 'uwap://';

		// var client_id = 'app_' + UWAP.utils.appid;
		var client_id = UWAP.utils.appid;

		jso.jso_configure({
			"uwap": {
				client_id: client_id,
				authorization: UWAP.utils.getEngineURL('/api/oauth/authorization'),
				redirect_uri: redirect_uri,
				passive_redirect_uri: passive_redirect_uri
			}
		}, {debug: 1});

		App.init();

	}, false);


	window.handleOpenURL = function (url) {
		// TODO: parse the url, and do something 
		console.log("REDIRET BACK JUHU");
		console.log("url:" + url + ":");

		setTimeout(function() {
			console.log("run timeout()");
			jso.jso_checkfortoken('uwap', url, function() {
				console.log("found token()");
				App.init();
			});
		}, 0);
	}






});

