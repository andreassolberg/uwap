define(function(require, exports, module) {
	
	var
		moment = require('uwap-core/js/moment'),
		humdur = require('../libs/humdur');

	var Countdown = function(container, text, ts) {

		this.text = text;
		this.ts = moment(ts);

		this.container = $('<div class="countdown"></div>');
		container.append(this.container);
		
		setInterval($.proxy(this.refresh, this), 3*60*1000); // 3 minutes
		this.refresh();
	};

	Countdown.prototype.diff = function(now, ts) {
		var dur = now.diff(ts, 'days');

		if (dur > 300) {

			var years = moment.duration(dur, 'days').asYears();
			var yearsround = Math.floor(years+0.2);
			var diffdays = now.diff( ts.clone().add('years', yearsround) , 'days');
			var diff = moment.duration( diffdays, 'days').humanize();


			console.log('-----> Years [' + this.text + ']');
			console.log("years", years, "rounded to ", yearsround, "diff days ", diffdays, "human readable is", diff);

			if (diffdays === 0) {
				return '<span class="countdown1">' + yearsround + '</span> <span class="countdowntoday">today</span>';
			} else if (diffdays < 0) {// Runder opp
				return '<span class="countdown1">' + yearsround + '</span> <span class="countdown2">in ' + diff + '</span>';
			} else { // Runder ned, mest vanlig.
				return '<span class="countdown1">' + yearsround + '</span> <span class="countdown3">and ' + diff + '</span>';
			}


		} else {

			var months = moment.duration(dur, 'days').asMonths();
			var monthsround = Math.floor(months+0.2);
			var diffdays = now.diff( ts.clone().add('months', monthsround) , 'days');
			var diff = moment.duration( diffdays, 'days').humanize();

			console.log('-----> Months [' + this.text + ']');
			console.log("months", months, "rounded to ", monthsround, "diff days ", diffdays, "human readable is", diff);

			if (diffdays === 0) {
				console.log("TODAY")
				return '<span class="countdown1">' + monthsround + 'mnd</span> <span class="countdowntoday">today</span>';
			} else if (diffdays < 0) {// Runder opp
				console.log("round UP")
				return '<span class="countdown1">' + monthsround + 'mnd</span> <span class="countdown2">in ' + diff + '</span>';
			} else { // Runder ned, mest vanlig.
				console.log("round DOWN")
				return '<span class="countdown1">' + monthsround + 'mnd</span> <span class="countdown3">and ' + diff + '</span>';
			}

		}
		return 'na';
	}

	Countdown.prototype.refresh = function() {
		var now = moment();
		var t = this.diff(now.clone().add('days', 0), this.ts);
		this.container.empty().append('<p><span class="text">' + this.text+ '</span> ' + t  + '</p>');

	}

	return Countdown;

});