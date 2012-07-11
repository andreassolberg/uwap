$(document).ready(function() {
	






	UWAP.auth.require(function(user) {

		require(['./LogApp'], function(LogApp) {

			var logapp = new LogApp($("div#logout"), $("div#filters"));
			
			// $("div#filters").append('<div class="btn btn-info">138.28.3.4 <a class="close" href="#">&times;</a></div>');

		});
	});





});