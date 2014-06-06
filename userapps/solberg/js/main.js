define(function(require, exports, module) {

	requirejs.config( {
	    "shim": {
	        "libs/jquery.flot"  : {deps: ['jquery'], exports: 'jQuery'},
	        "libs/jquery.flot.time"  : {deps: ['jquery'], exports: 'jQuery'}
	    }
	} );

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),

		moment = require('uwap-core/js/moment'),
		prettydate = require('uwap-core/js/pretty'),

		Tracker = require('Tracker'),
		locations = require('locations'),

		SCalendar = require('plugins/SCalendar'),
		Adressa = require('plugins/Adressa'),
		Yr = require('plugins/Yr'),
		SMsg = require('plugins/SMsg'),
		Buss = require('plugins/Buss'),
		Gaver = require('plugins/Gaver'),
		Todo = require('plugins/Todo'),
		Temp = require('plugins/Temp'),
		Countdown = require('plugins/Countdown'),
		Flo = require('plugins/Flo')
    	;

    require('uwap-core/js/jquery.tmpl');

	require('uwap-core/bootstrap3/js/bootstrap');	
	
	require('uwap-core/bootstrap3/js/modal');
	require('uwap-core/bootstrap3/js/collapse');
	require('uwap-core/bootstrap3/js/button');
	require('uwap-core/bootstrap3/js/dropdown');

	require('libs/jquery.flot');
	require('libs/jquery.flot.time');


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

		var scalendar = new SCalendar($("div#calendar"));

		var adressa = new Adressa($("div#adressa"));
		var yr = new Yr($("div#yr"));
		var gaver = new Gaver($("div#gaver"));
		var flo = new Flo($("#flo"))
		var temp = new Temp($("#temp"));


		// var vgc = new VGCover($("div#vg"));
		var msg = new SMsg($("div#messages"));
		// var buss = new Buss($("div#buss"));
		// var tracker = new Tracker(locationconfig, $("div#locations"));



		// var andreas = new Countdown($("div#countdowns"), 'Andreas', '1980-10-10 04:00');
		// var vigdis = new Countdown($("div#countdowns"), 'Vigdis', '1980-03-30 12:00');



		
		var linnea = new Countdown($("div#countdowns"), 'Linnea', 'March 27, 2009 12:00');		
		var lukas = new Countdown($("div#countdowns"), 'Lukas', 'March 20, 2012 12:00');
		var frida = new Countdown($("div#countdowns"), 'Frida', 'July 8, 2012 12:00');
		var linus = new Countdown($("div#countdowns"), 'Linus', 'August 3, 2012 12:00');

		var sofie = new Countdown($("div#countdowns2"), 'Sofie', 'April 14, 1999 12:00');
		var cornelia = new Countdown($("div#countdowns2"), 'Cornelia', 'December 19, 2001 12:00');
		var tobias = new Countdown($("div#countdowns2"), 'Tobias', 'October 18, 2002 12:00');
		var oliver = new Countdown($("div#countdowns2"), 'Oliver', 'September 29, 2005 12:00');






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