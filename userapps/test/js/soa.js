define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		moment = require('uwap-core/js/moment'),
    	prettydate = require('uwap-core/js/pretty')
    	;

   

	require('uwap-core/bootstrap/js/bootstrap');	
	
	// require('uwap-core/bootstrap/js/bootstrap-modal');
	// require('uwap-core/bootstrap/js/bootstrap-collapse');
	// require('uwap-core/bootstrap/js/bootstrap-button');
	// require('uwap-core/bootstrap/js/bootstrap-dropdown');


	UWAP.auth.require(function(user) {
		

		/*
		 * Scopes: soa_testproxy_api
		 */
		var url = 'http://app.solweb.no/misc/reflect.php';
		url = 'http://soademo.app.bridge.uninett.no/api/poot';

		UWAP.data.soa(url, {}, function(data) {
			console.log("Received data...", data);
			$("div#out").empty().append(JSON.stringify(data, '', 4));
		}, function(err) {
			console.error("Error", err);
		});

		// UWAP.data.get('http://testproxy.app.bridge.uninett.no/api/baluba.php', {"soaproxy": true}, function(data) {
		// 	console.log("Received data...", data);
		// }, function(err) {
		// 	console.error("Error", err);
		// });

	});



	console.log("Loaded");

});