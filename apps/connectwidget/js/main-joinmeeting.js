define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),

		hb = require('uwap-core/js/handlebars')
		;

	
	require('uwap-core/js/jquery.tmpl');

	require('uwap-core/bootstrap3/js/bootstrap');	
	
	var joinTmpl = hb.compile(require('uwap-core/js/text!templates/joinform.html'));


	$("document").ready(function() {
		

		var query = {};
		(function () {
			var e,
				a = /\+/g,  // Regex for replacing addition symbol with a space
				r = /([^&;=]+)=?([^&;]*)/g,
				d = function (s) { return decodeURIComponent(s.replace(a, " ")); },
				q = window.location.search.substring(1);

			while (e = r.exec(q))
			   query[d(e[1])] = d(e[2]);
		})();

		console.log("About to parse message", query.msg);
		var msg = JSON.parse(query.msg);

		// alert('Join meeting '  + JSON.stringify(msg));

		$("div#content").empty().append(joinTmpl(msg));
		$("div#content").find('#joinform').submit();




	});

});

