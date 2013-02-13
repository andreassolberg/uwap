define(function(require, exports, module) {
	
	var
		moment = require('uwap-core/js/moment');
		
	var Temp = function(container) {
		this.container = container;


	    this.colors = {
	    	'Ute': '#66c',
    		'Kjeller': '#aaa',
			'Linneas rom': '#5c1',
			'Stue': '#f78'
	    };

		this.containerNow = $('#tempnow');
		this.containerTS = $('<div class="ts"></div>').appendTo(this.container);

		this.loadNow();
		this.loadTS();
		setInterval($.proxy(this.loadNow, this), 3*60*1000); // 3 minutes
		setInterval($.proxy(this.loadTS, this), 3*60*1000+3); // 3 minutes
	};

	Temp.prototype.loadNow = function() {
		var url = "http://solweb.no/data/twine/temp.php";
		UWAP.data.get(url, {handler: "solberg"}, $.proxy(this.responseNow, this));
	}


	Temp.prototype.responseNow = function(data) {
		var 
			i, el;

		console.log("temp response");
		console.log(data);
		$(this.containerNow).empty();

		var text = '';

		for(var key in data) {
			console.log("ke is " , data)
			text += '<tr><td class="tempLoc">' + 
				key + ' ' +
				'<div style="width: 10px; display: inline; height: 10px; margin: 6px 5px; background: ' + this.colors[key] + '; border: 1px solid #888; padding: 1px">&nbsp;</div>' +
				'</td><td class="tempValue">' + data[key].v.temperature + '</td></tr>';
		}
		text = '<table class="temp">' + text + '</table>';
		$(this.containerNow).empty().append(text);
		
	}

	Temp.prototype.loadTS = function() {
		var url = "http://solweb.no/data/twine/timeseries.php";
		UWAP.data.get(url, {handler: "solberg"}, $.proxy(this.responseTS, this));
	}
	
	Temp.prototype.responseTS = function(data) {
		var 
			i, el,
			now = moment();

		console.log("temp timeseries response");
		console.log(data);

		var now = moment();
		var dayfill = parseInt(now.format("H"), 10) + (now.format("m") / 60);

		// console.log("Day fill", dayfill, now.format("H"), now.format("m"));

		// var d1 = [];
		// for (var i = 0; i < 14; i += 0.5)
		// d1.push([i, Math.sin(i)]);
		// var d2 = [[0, 3], [4, 8], [8, 5], [9, 13]];

		// // a null signifies separate line segments
		// var d3 = [[0, 12], [7, 12], null, [7, 2.5], [12, 2.5]];
		var fdata = [];
		var start = 6;
		var markings = [
	        // { color: '#f6f6f6', yaxis: { from: 1 } },
	        // { color: '#f6f6f6', yaxis: { to: -1 } },
	        { color: '#aaa', lineWidth: 1, xaxis: { from: start, to: dayfill } }
	    ];

	    /*
	    $twines = array(
			'0000ed20ba33dc40' => 'Ute',
			'000016bfe1630291' => 'Kjeller',
			'0000f7087250b84b' => 'Linneas rom',
			'0000c2b595f71000' => 'Stue',
		);
	     */


	    var chd = [];
	    for(var key in data) {
	    	chd.push(
	    		{
	    			// label: data[key]['name'],
	    			data: data[key]['data'],
	    			color: this.colors[data[key]['name']]
	    		});
	    }


	    console.log("From ", { from: moment() - 3600*1000, to: moment() - 0 });

		var markings = [
	        // { color: '#f6f6f6', yaxis: { from: 1 } },
	        // { color: '#f6f6f6', yaxis: { to: -1 } },
	        { color: '#777', lineWidth: 1, xaxis: { from: moment().sod() - 2*3600*1000, to: moment().sod() + 7*3600*1000 } },
	        { color: '#aaf', lineWidth: 1, yaxis: { from: -10, to: 0 } },
	    ];

		$(this.containerTS).empty().append('<div id="floph" style="width:100%;height:200px;"></div>');
		$.plot($("#floph"), 
			chd, 
			{
				grid: { 
					markings: markings 
				},
				xaxis: { 
					mode: "time"
				},
				// yaxis: {min: -20, max: 30},
				// legend: {container: $("#tcontainer")}
			}
		);
	}

	return Temp;

});