define(['../libs/moment'], function(moment) {

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
			havn = 19,
			mnd = 8,
			dag = 19,
			that = this,
			now = moment();

		console.log("Day", (now.format("M")-1), "month", now.format("D"));

		this.url = 'http://retro.met.no/cgi-bin/vannstand-tabell.cgi?' +
			'havn=' + havn + '&dag=' + now.format("D") + '&mnd=' + (now.format("M")-1) + '&dogn=1&referanse=0&side=1';

		UWAP.data.get(this.url,  null, function(data) {
			// console.log('data', data);
			console.log("  - - - - - - - - - - -  -- - - - -  -- - - - -  -- - - - -  - DATATATA");
			var obj = $(data);

			var fdata = [];
			var start = 6;
			
			obj.find("table.table tr[align='right']").each(function(i, item) {
				var hour = $(item).children().eq(0).text();
				var height = $(item).children().eq(1).text();
				console.log("foo", hour, height);

				if (hour >= start)
					fdata.push([hour, height]);

				// console.log("hour", $(item).find("td[0]"), parseInt($(item).find("td[0]"), 10));
				// console.log("height", parseInt($(item).find("td[1]"), 10));
			});

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
		        { color: '#aaa', lineWidth: 1, xaxis: { from: start, to: dayfill } }
		    ];

			$(that.container).empty().append('<div id="floph" style="width:100%;height:100px;"></div>');
			$.plot($("#floph"), [ fdata ], 
				{
					grid: { 
						markings: markings 
					},
					xaxis: { ticks: [8,10,12,14,16,18,20,22]}
				}
			);



			// obj.each("")

			// console.log('content', content);
			// $(that.container).empty().append(content);

		});




	}

	return Flo;

});