define(['../libs/moment', '../libs/humdur'], function(moment, humdur) {

	var Countdown = function(container, text, ts) {

		this.text = text;
		this.ts = Date.parse(ts);

		this.container = $('<div class="countdown"></div>');
		container.append(this.container);
		
		setInterval($.proxy(this.refresh, this), 3*60*1000); // 3 minutes
		this.refresh();
	};

	Countdown.prototype.diff = function() {
		var sec = Math.abs(this.ts - (new Date()).getTime())/1000.0;
		var str = humdur(sec);
		// console.log("Now", (new Date()).getTime(), ' then ', this.ts);
		// console.log("RESULT", this.text, sec, str);
		return str;
	}

	Countdown.prototype.refresh = function() {
		var t = this.diff();
		this.container.empty().append('<p><span class="text">' + this.text+ '</span>: ' + t  + '</p>');

	}

	return Countdown;

});