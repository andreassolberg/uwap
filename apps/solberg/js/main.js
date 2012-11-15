define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),

		moment = require('uwap-core/js/moment'),
    	prettydate = require('uwap-core/js/pretty')

		Tracker = require('Tracker'),
		locations = require('locations'),

		SCalendar = require('plugins/SCalendar'),
		Adressa = require('plugins/Adressa'),
		Yr = require('plugins/Yr'),
		SMsg = require('plugins/SMsg'),
		Buss = require('plugins/Buss'),
		Gaver = require('plugins/Gaver'),
		Todo = require('plugins/Todo'),
		Countdown = require('plugins/Countdown'),
		VGCover = require('plugins/VGCover'),
		Flo = require('plugins/Flo')
    	;


    require('uwap-core/js/jquery.tmpl');

	require('uwap-core/bootstrap/js/bootstrap');	
	
	require('uwap-core/bootstrap/js/bootstrap-modal');
	require('uwap-core/bootstrap/js/bootstrap-collapse');
	require('uwap-core/bootstrap/js/bootstrap-button');
	require('uwap-core/bootstrap/js/bootstrap-dropdown');

	require('libs/jquery.flot');


	$(document).ready(function() {
		
		function cdata(s) {
			var matches=[];
			s.replace(/\<\!\[CDATA\[(.+?\]{0}\>{0})\]\]\>/g, function(a,b){matches.push(b)});
			return matches.join(' ');
		}


		var dataurl = 'http://location.app.bridge.uninett.no/js/locations.js';

		locationconfig = locations;

		console.log("Loaded all set go.")
		console.log(locationconfig); 

		var adressa = new Adressa($("div#adressa"));
		// var tracker = new Tracker(locationconfig, $("div#locations"));
		var scalendar = new SCalendar($("div#calendar"));
		var yr = new Yr($("div#yr"));
		// var msg = new SMsg($("div#messages"));
		var buss = new Buss($("div#buss"));
		var gaver = new Gaver($("div#gaver"));
		var vgc = new VGCover($("div#vg"));
		var flo = new Flo($("#flo"))

		// var andreas = new Countdown($("div#countdowns"), 'Andreas', '1980-10-10 04:00');
		// var vigdis = new Countdown($("div#countdowns"), 'Vigdis', '1980-03-30 12:00');

		var linus = new Countdown($("div#countdowns"), 'Linus 3aug', 'August 3, 2012 05:00');
		var linnea = new Countdown($("div#countdowns"), 'Linnéa 27mar', 'March 27, 2009 01:00');		
		var lukas = new Countdown($("div#countdowns"), 'Lukas 20mar', 'March 20, 2012 03:00');
		var frida = new Countdown($("div#countdowns"), 'Frida 8jul', 'July 8, 2012 05:00');

		// UWAP.data.get('http://www.adressa.no/nyheter/',  null, function(data) {
		// 	console.log("Got addreasa");
		// 	// console.log(data);

		// 	var obj = $(data);
		// 	var content = obj.find("div#content div.polarisStories div.article");
		// 	console.log(content);
		// 	$("div#adressa").append(content);

		// });
		
		// var todo = new Todo($("div#todo"));
			

		
	});

});