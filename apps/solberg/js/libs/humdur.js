define(function() {


	var split = function(seconds) {
		var res = {
			'seconds': seconds
		};

		console.log('1', JSON.parse(JSON.stringify(res)));
		if (res.seconds > 60) {
			res.minutes = Math.floor(res.seconds/60);
			res.seconds = res.seconds % 60
		}
		console.log('2', JSON.parse(JSON.stringify(res)));
		if (res.minutes > 60) {
			res.hours = Math.floor(res.minutes/60);
			res.minutes = res.minutes % 60;
		}
		console.log('3', JSON.parse(JSON.stringify(res)));
		if (res.hours > 24) {
			res.days = Math.floor(res.hours/24);
			res.hours = res.hours % 24;
		}
		console.log('4', JSON.parse(JSON.stringify(res)));	
		if (res.days > 365) {
			// deal with weeks
			res.years = Math.floor(res.days / 365);
			res.days = res.days % 365;
		}
		console.log('5', JSON.parse(JSON.stringify(res)));
		if (res.days > 7*12) {
			res.months = Math.floor(res.days / 30);
			res.days = res.days % 30; 
		} else if (res.days > 7) {
			res.weeks = Math.floor(res.days / 7);
			res.days = res.days % 7;
		}
		console.log('6', JSON.parse(JSON.stringify(res)));
		return res;
	};
	
	var humdur = function(seconds) {

		var 
			res = split(seconds),
			str;

		console.log("Seconds", seconds);
		console.log("splite", res);

		if (res.years) {
			str = res.years + ' år ';
			if (res.months) str += ' og ' + res.months + ' måneder';
			if (res.weeks) str += ' og ' + res.weeks + ' uker';
			return str;
		}
		if (res.months) {
			return res.months + ' måneder og ' + res.days + ' dager';
		}
		if (res.weeks) {
			return res.weeks + ' uker og ' + res.days + ' dager';
		}
		if (res.days) {
			return res.days + ' dager og ' + res.hours + ' timer';
		}
		if  (res.hours) {
			return res.hours + ' timer og ' + res.minutes + ' minutes';
		}
		if (res.minutes) {
			return res.minutes + ' minutter og ' + res.seconds + ' sekunder';
		}
		return res.seconds + ' sekunder';


	};

	return humdur;

});