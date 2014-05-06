define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core')		;

	require('uwap-core/bootstrap3/js/bootstrap');	
	
	$(window).on("message", function(e) {
		var data = e.originalEvent.data;  // Should work.
		console.log("UWAP Feed Received postMessage message from one of the iframes", data);

		if(data.action === 'setSize') {
			
			var menupadding = data.extra + 50;

			$("#connect-widget").height(data.size + 34 + menupadding);
			$("#connect-widget").css('margin-bottom', -menupadding);
			console.error("RESIZE", data, "set to ", $("#connect-widget").height(), "menupadding: " + menupadding);
		}

	});

});

