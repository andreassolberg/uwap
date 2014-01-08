define(function(require, exports, module) {
	
	var
		moment = require('uwap-core/js/moment');
		
	var Flo = function(container, text, ts) {

		this.text = text;
		this.ts = Date.parse(ts);

		this.container = $('<div class="countdown"></div>');
		container.append(this.container);
		
		setInterval($.proxy(this.refresh, this), 3*60*1000); // 3 minutes
		this.refresh();
	};


	Flo.prototype.refresh = function() {
		var 
			that = this,
			now = moment(),
			plotdata = [];

		var until = now.clone().add('days', 0);

		var url = 'http://www.sehavniva.no/vannstand.php?locationDataAll=1&reflevelcode=CD&interval=10min&locationType=coordinate&' +
			'lat=63.436484&lon=10.391669&' +
			'fromDay=' + now.format("D") + '&fromMonth=' + (now.format("M")) + '&fromYear=' + now.format("YYYY") + '&' +
			'toDay=' + until.format("D") + '&toMonth=' + (until.format("M")) + '&toYear=' + until.format("YYYY") + '';

		console.log("Fetching vanndata from URL", url);

		UWAP.data.get(url, {}, function(data) {
			console.log("Fikk vannstand data:");
			console.log(data);

			$.each(data.days, function(day, daydata) {
				var dp;
				// console.log("Day data from ", day, daydata.data);
				for(var i = 0; i < daydata.data.length; i++) {
					dp = moment(daydata.data[i].time);
					if  (i < 10)
					console.log("Day", day, daydata.data[i].time, dp.format("dddd, MMMM Do YYYY, h:mm:ss a"), daydata.data[i].values[1]);
					plotdata.push([dp.valueOf(), daydata.data[i].values[1]]);
				}

			});


			console.log("plot PLOT");
			console.log(plotdata);

			var dayfill = parseInt(now.format("H"), 10) + (now.format("m") / 60);

			console.log("Day fill", dayfill, now.format("H"), now.format("m"));

			// var d1 = [];
			// for (var i = 0; i < 14; i += 0.5)
			// d1.push([i, Math.sin(i)]);
			// var d2 = [[0, 3], [4, 8], [8, 5], [9, 13]];

			// // a null signifies separate line segments
			// var d3 = [[0, 12], [7, 12], null, [7, 2.5], [12, 2.5]];


			var markings = [
		        // { color: '#f6f6f6', yaxis: { from: 1 } },
		        // { color: '#f6f6f6', yaxis: { to: -1 } },
		        { color: '#aaa', lineWidth: 1
		        	, xaxis: { 
		        		from: now.clone().startOf('day').valueOf() , to: now.valueOf()
		        		
		        	} 
		        }
		    ];

		    // console.log("TIME", moment('11:00').valueOf());

			$(that.container).empty().append('<div id="flophFlo" style="width:100%;height:100px;"></div>');
			$.plot($("#flophFlo"), [ plotdata ], 
				{
					grid: { 
						markings: markings 
					},
					xaxis: { 
						timezone: "browser",
						// tickSize: 1,
						from:  now.clone().startOf('day').valueOf(),
						to: now.clone().endOf('day').valueOf(),
						mode: "time"}
				}
			);

		});



	}


	return Flo;

});