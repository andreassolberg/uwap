define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core')
		;


	require('uwap-core/bootstrap/js/bootstrap');
	// require('uwap-core/bootstrap/js/bootstrap-collapse');
	// require('uwap-core/bootstrap/js/bootstrap-button');
	// require('uwap-core/bootstrap/js/bootstrap-dropdown');

	$(document).ready(function() {
		$(".loader-hideOnLoad").hide();
		$("#main").append("Hello world!");

	});



});

