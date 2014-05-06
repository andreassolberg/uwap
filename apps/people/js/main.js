define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core')
    	;
	
	require("uwap-core/js/uwap-people");

    // require('uwap-core/js/jquery.tmpl');

	require('uwap-core/bootstrap3/js/bootstrap');	
	require('uwap-core/bootstrap3/js/collapse');
	require('uwap-core/bootstrap3/js/button');
	require('uwap-core/bootstrap3/js/dropdown');	



	UWAP.auth.require(function(user) {


		$(".loader-hideOnLoad").hide();
		$(".loader-showOnLoad").show();
		$("span#username").html(user.name);
		$('.dropdown-toggle').dropdown();


		var ps = $("#peoplesearchContainer").focus().peopleSearch({
			callback: function(item) {
				$("#pl").append('<li><strong>' + item.name + '</strong> from ' + item.o + '</li>');
			}
		});



	});

});