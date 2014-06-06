define(function(require, exports, module) {

	requirejs.config( {
	    "shim": {
	        "libs/jquery.flot"  : {deps: ['jquery'], exports: 'jQuery'}
	    }
	} );

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),

		moment = require('uwap-core/js/moment'),
    	prettydate = require('uwap-core/js/pretty')

    	;
    require('uwap-core/js/jquery.tmpl');

	require('uwap-core/bootstrap/js/bootstrap');	
	
	require('uwap-core/bootstrap/js/bootstrap-modal');
	require('uwap-core/bootstrap/js/bootstrap-collapse');
	require('uwap-core/bootstrap/js/bootstrap-button');
	require('uwap-core/bootstrap/js/bootstrap-dropdown');

	require('libs/jquery.flot');


	var Temp = function(container) {
		this.container = container;


		this.colors = {
			// 'Ute': '#66c',
			'Kjeller': '#aaa',
			'Linneas rom': '#5c1',
			'Stue': '#f78'
		};

		// this.containerNow = $('#tempnow');
		this.containerTS = $('<div class="ts"></div>').appendTo(this.container);

		// this.loadNow();
		this.loadTS();
		// setInterval($.proxy(this.loadNow, this), 3*60*1000); // 3 minutes
		setInterval($.proxy(this.loadTS, this), 3*60*1000+3); // 3 minutes
	};


	Temp.prototype.loadTS = function() {
		var url = "http://solweb.no/data/twine/vib.php";
		UWAP.data.get(url, {}, $.proxy(this.responseTS, this));
	}
	
	Temp.prototype.responseTS = function(data) {
		var 
			i, el,
			now = moment();

		console.log("temp timeseries response");
		console.log(data);

		var now = moment();
		var dayfill = parseInt(now.format("H"), 10) + (now.format("m") / 60);

		var fdata = [];
		var start = 6;
		var markings = [
	        { color: '#aaa', lineWidth: 1, xaxis: { from: start, to: dayfill } }
	    ];

	    var range = [moment('2013-02-06T08:00'), moment('2013-02-06T18:00')];
	    console.log("Range", range);

	    var chd = [];
	    for(var key in data) {
	    	// if (data[key]['data'][0][0] < range[0]+0.0) continue;
	    	// if (data[key]['data'][0][0] > range[1]+0.0) continue;
	    	// console.log('Compare ', data[key]['data'][0][0] , range[1]+0.0)
	    	// console.log("Compare", data[key]['data'][0], range[1]+0.0); return;
	    	chd.push(
	    		{
	    			// label: data[key]['name'],
	    			data: data[key]['data'],
	    			color: this.colors[data[key]['name']],
	    			lines: { show: true },
	    			points: { show: true }
	    		});
	    }


	    console.log("From ", { from: moment() - 3600*1000, to: moment() - 0 });

		var markings = [
	        // { color: '#f6f6f6', yaxis: { from: 1 } },
	        // { color: '#f6f6f6', yaxis: { to: -1 } },
	        { color: '#777', lineWidth: 1, xaxis: { from: moment().sod() - 2*3600*1000, to: moment().sod() + 7*3600*1000 } },
	        // { color: '#aaf', lineWidth: 1, yaxis: { from: -10, to: 0 } },
	    ];

		$(this.containerTS).empty().append('<div id="floph" style="width:800px;height:400px;"></div>');
		$.plot($("#floph"), 
			chd, 
			{
				grid: { 
					markings: markings 
				},
				xaxis: { 
					mode: "time"
					// ,min: range[0]+0.0, max: range[1]+0.0
				}
				// yaxis: {},
				,legend: {}
			}
		);
	}




	$(document).ready(function() {
		console.log("1");
		UWAP.auth.require(function() {
			console.log("2");
			var temp = new Temp($("#plot"))	
		});
		
	});

});