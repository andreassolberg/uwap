define(function(require, exports, module) {
	
	var
		moment = require('uwap-core/js/moment');

	var Buss = function(container) {
		this.container = container;
		this.load();
		setInterval($.proxy(this.load, this), 3*60*1000); // 3 minutes
		setInterval($.proxy(this.updateCount, this), 1000); // 3 minutes
	};



	Buss.secondsT = function(sec) {
		var minutes = 0;

		if (sec <= 60) {
			return Math.floor(sec) + 's';
		} else {
			minutes = Math.floor(sec/60);
			sec = sec - (minutes*60);
			return '<span class="minutes">' + minutes + '</span>m' + Math.floor(sec) + '';
		}
	} 

	Buss.prototype.updateCount = function() {
		$(this.container).find("span.count").each(function(i, item) {
			var rdt = $(this).data("eta");
			var now = new Date(); 
			var until = (rdt - now) / 1000;
			// console.log("Until is " + until);
			if (until < (60*3)) {
				$(this).closest(".bussentry").addClass("toolate");
				$(this).closest(".bussentry").removeClass("soon");
			} else if (until < (60*20)) {
				$(this).closest(".bussentry").removeClass("toolate");
				$(this).closest(".bussentry").addClass("soon");
			}
			$(this).html(Buss.secondsT(until));
		})
	}


	Buss.prototype.load = function() {
		var bussurl = "http://api.busbuddy.no:8080/api/1.3/departures/16011125";
		UWAP.data.get(bussurl, {handler: "buss", followRedirects: false, curl: true}, $.proxy(this.response, this));
	}

	Buss.prototype.response = function(r) {
		var that = this;
		$(this.container).empty();
		if (!r) {
			console.error('Did not get any proper response', r); return;
		}
		$.each(r.departures, function(i, item) {
			var now = new Date(); 
			var rdt = new Date(); 
			rdt.setHours(item.registeredDepartureTime.substr(11,2));
			rdt.setMinutes(item.registeredDepartureTime.substr(14,2));
			rdt.setSeconds(0);

			var sdt = new Date();
			sdt.setHours(item.scheduledDepartureTime.substr(11,2));
			sdt.setMinutes(item.scheduledDepartureTime.substr(14,2));
			sdt.setSeconds(0);

			var diff = Math.floor((rdt - sdt) / (60*1000));
			var until = (rdt - now) / 1000;


			console.log(rdt);
			console.log(sdt);
			console.log("-----");
			console.log(item.registeredDepartureTime.substr(11,5));
			console.log(item.scheduledDepartureTime.substr(11,5));

			var tid = item.registeredDepartureTime.substr(11,5);

			
			var real = (item.isRealtimeData ? 'sanntid' : 'rute');
			var toolate = ''; //(until < (60*4) ? 'toolate' : '');


			$("div#out").append('<p><span class="line">' + item.line + " " + item.destination + "</span> " + tid + " " + real +  " " + diff  + " forsinket " + Buss.secondsT(until) + " sekunder til avgang</p>");
			var nel = $('<div></div>');
			nel.attr('class', 'bussentry ' + real + ' ' + toolate);

			var countel = $('<span class="count">' + Buss.secondsT(until) + '</span>')
			countel.data("eta", rdt);
			nel.append(countel);
			nel.append('<span class="line">' + item.line + '</span>');
			nel.append('<span class="destination">' + item.destination + '</span>');
			nel.append('<span class="tid">' + tid + '</span>');
			// nel.append('<span class="diff">' + diff + '</span>');
			
			// nel.append('<span class="clear" style="clear: both" >&nbsp;</span')

			$(that.container).append(nel);
		});
	}


	return Buss;

});