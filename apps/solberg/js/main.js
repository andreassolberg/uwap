$(document).ready(function() {
	
	function cdata(s) {
		var matches=[];
		s.replace(/\<\!\[CDATA\[(.+?\]{0}\>{0})\]\]\>/g, function(a,b){matches.push(b)});
		return matches.join(' ');
	}


	var dataurl = 'http://location.app.bridge.uninett.no/js/locations.js';

	// console.log("Loaded data", locationconfig);
	requirejs(['Tracker', 'locations', 'plugins/SCalendar', 'plugins/Adressa', 'plugins/Yr', 'plugins/SMsg', 'plugins/Buss', 'plugins/Gaver', 'plugins/Todo', 'plugins/Countdown', 'plugins/VGCover'], function(Tracker, locationconfig, SCalendar, Adressa, Yr, SMsg, Buss, Gaver, Todo, Countdown, VGCover) {

		console.log("Loaded all set go.")
		console.log(locationconfig); 

		var adressa = new Adressa($("div#adressa"));
		var tracker = new Tracker(locationconfig, $("div#locations"));
		var scalendar = new SCalendar($("div#calendar"));
		var yr = new Yr($("div#yr"));
		var msg = new SMsg($("div#messages"));
		var buss = new Buss($("div#buss"));
		var gaver = new Gaver($("div#gaver"));
		

		var vgc = new VGCover($("div#vg"));

		// var andreas = new Countdown($("div#countdowns"), 'Andreas', '1980-10-10 04:00');
		// var vigdis = new Countdown($("div#countdowns"), 'Vigdis', '1980-03-30 12:00');
		var linnea = new Countdown($("div#countdowns"), 'Linnéa', 'March 27, 2009 01:00');
		var linus = new Countdown($("div#countdowns"), 'Linus Termin', 'July 29, 2012 21:00');
		var lukas = new Countdown($("div#countdowns"), 'Lukas', 'March 20, 2012 03:00');
		var frida = new Countdown($("div#countdowns"), 'Frida', 'July 8, 2012 05:00');

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