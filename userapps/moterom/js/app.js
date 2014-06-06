define(function(require, exports, module) {
	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		moment = require('uwap-core/js/moment');
//		moment = require('moment')
//		hogan = require('uwap-core/js/hogan'),
//    	prettydate = require('uwap-core/js/pretty')
//    	;
//
//	require('lib/jquery.equalheights');
//	require("lib/director");
//	
//	var MRController = require('controllers/MRController');
//	var Event = require('models/Event');
//	var Room = require('models/Room');
//	
//    
//	require('uwap-core/bootstrap/js/bootstrap');	
//	
//	require('uwap-core/bootstrap/js/bootstrap-modal');
//	require('uwap-core/bootstrap/js/bootstrap-collapse');
//	require('uwap-core/bootstrap/js/bootstrap-button');
//	require('uwap-core/bootstrap/js/bootstrap-dropdown');
//	
	
	

	$(document).ready(function() {
		
//		var m = new MRController($("div#main"));
//		document.write('test');
//		$('#main').append(UWAP.auth.require);
//		console.log(UWAP.auth.require);
		UWAP.data.get("https://moterom2.uwap.org/config.json", {}, function(d){
			$('#main').append('success');
		}, function(err){
			$('#main').append('<br /> UWAP.data.get-test:<br />'+err.toString());
		});
		UWAP.auth.check(function(d){
			$('#main').append(d.name);
		}, function(err){$('#main').append('<br />UWAP.auth.check-test: <br />checked, but not logged in...');});
		
		UWAP.auth.require(function(user){
			$('#main').append('testRequire');
//			document.write(user.name);
//			window.location.href ="https://moterom2.uwap.org/appFile.html#"+user.name;
		});
	});

});