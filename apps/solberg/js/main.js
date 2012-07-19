$(document).ready(function() {
	
	function cdata(s) {
		var matches=[];
		s.replace(/\<\!\[CDATA\[(.+?\]{0}\>{0})\]\]\>/g, function(a,b){matches.push(b)});
		return matches.join(' ');
	}


	var dataurl = 'http://location.app.bridge.uninett.no/js/locations.js';

	// console.log("Loaded data", locationconfig);
	requirejs(['Tracker', 'locations', 'plugins/SCalendar', 'plugins/Adressa', 'plugins/Yr', 'plugins/SMsg', 'plugins/Buss', 'plugins/Gaver', 'plugins/Todo'], function(Tracker, locationconfig, SCalendar, Adressa, Yr, SMsg, Buss, Gaver, Todo) {

		console.log("Loaded all set go.")
		console.log(locationconfig); 


		var adressa = new Adressa($("div#adressa"));
		var tracker = new Tracker(locationconfig, $("div#locations"));
		var scalendar = new SCalendar($("div#calendar"));
		var yr = new Yr($("div#yr"));
		var msg = new SMsg($("div#messages"));
		var buss = new Buss($("div#buss"));
		var gaver = new Gaver($("div#gaver"));

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