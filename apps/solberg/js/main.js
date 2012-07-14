$(document).ready(function() {
	
	var dataurl = 'http://location.app.bridge.uninett.no/js/locations.js';

	// console.log("Loaded data", locationconfig);
	requirejs(['Tracker', 'locations', 'plugins/SCalendar', 'plugins/Adressa', 'plugins/Yr', 'plugins/SMsg', 'plugins/Buss', 'plugins/Gaver', 'plugins/Todo'], function(Tracker, locationconfig, SCalendar, Adressa, Yr, SMsg, Buss, Gaver, Todo) {

		console.log("Loaded all set go.")
		console.log(locationconfig); 

		var tracker = new Tracker(locationconfig, $("div#locations"));
		var scalendar = new SCalendar($("div#calendar"));
		// var adressa = new Adressa($("div#adressa"));
		var yr = new Yr($("div#yr"));
		var msg = new SMsg($("div#messages"));
		var buss = new Buss($("div#buss"));
		var gaver = new Gaver($("div#gaver"));
		
		// var todo = new Todo($("div#todo"));
		
	});	
	
});