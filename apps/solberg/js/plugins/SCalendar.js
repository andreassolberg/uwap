define(function(require, exports, module) {
	
	var
		moment = require('uwap-core/js/moment');
		
	var SCalendar = function(container) {
		this.container = container;
		this.load();
		setInterval($.proxy(this.load, this), 3*60*1000); // 3 minutes
	};

	SCalendar.prototype.load = function() {
		var calurl = "http://app.solweb.no/solberg/index.php";
		UWAP.data.get(calurl, {handler: "solberg"}, $.proxy(this.response, this));
	}
	SCalendar.prototype.response = function(c) {
		var 
			key, i, 
			dayel, entryel, cur;

		console.log("Cal response");
		console.log(c);
		$(this.container).empty();
		// $("#call").append('<div class="calentry">AAA</div>');
		// $("#call").append('<p>sldkjfldskf</p>');

		for(key in c) {
			dayel = $('<div class="day"></div>');
			dayel.append('<div class="dayheader">' + c[key].text + '</div>');


			console.log(c[key].middag);
			if (c[key].middag) {
				for(i = 0; i < c[key].middag.length; i++) {
					entryel = $('<div class="calentry middag">' + c[key].middag[i].name + '</div>');
					entryel.prepend('<img src="/img/dinner2.png" style="" />');
					dayel.append(entryel);
				}

			}
			if (c[key].hasOwnProperty('events')) {
				for(i = 0; i < c[key].events.length; i++) {
					cur = c[key].events[i];
					entryel = $('<div class="calentry"><span class="caltype">' + cur.calendar[0].toUpperCase() + '</span> ' + 
						cur["summary"]["value"] + '</div>');
					entryel.addClass(cur.calendar);

					if (cur.caltype === 'singleday') {
						entryel.append('<span class="timerange">' + cur.timerange + '</div>');
					}

					dayel.append(entryel);
				}
			}

			$(this.container).append(dayel);
		}
	}


	return SCalendar;

});