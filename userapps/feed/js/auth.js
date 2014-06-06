define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core')
		;

	$("document").ready(function() {
		

		UWAP.auth.require(function(user) {
			self.close();
		});


	});

});

