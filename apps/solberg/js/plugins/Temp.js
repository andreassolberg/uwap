define(function(require, exports, module) {


	var StatHatEmbed = new function () {
	    function d() {
	        var a = document.getElementsByTagName("script");
	        // return a[a.length - 1];
	        return document.getElementById("tcontainer2");
	    }

	    function e(a, b) {
	        var d = document.createElement("script"),
	            c = "//www.stathat.com/embed/" + a + "/" + b.s1;
	        b.dev && (c = "//localhost:8081/embed/" + a + "/" + b.s1);
	        b.s2 && (c += "/" + b.s2);
	        b.s3 && (c += "/" + b.s3);
	        c += "?w=" + b.w + "&h=" + b.h + "&tf=" + b.tf  + '&refresh=' + (new Date).getTime();
	        b.style && (c += "&style=" + b.style);
	        b.dev && (c += "&dev=1");
	        b.title && (c += "&title=" + b.title);
	        d.src = c;
	        d.type = "text/javascript";
	        // document.getElementsByTagName("head")[0].appendChild(d)
	        document.getElementById("tcontainer2").appendChild(d);
	    }
	    function f(a) {
	        return [a.s1,
	            a.s2, a.s3, a.w, a.h, a.tf, a.style].join("_")
	    }
	    this.render_graph = function (a) {
	        DIV_ID = "statd_embed_graph_" + f(a);
	        d().insertAdjacentHTML("AfterEnd", "<div id='" + DIV_ID + "' style='display:none'></div>");
	        e("graph", a)
	    };
	    this.render_histogram = function (a) {
	        DIV_ID = "statd_embed_histogram_" + f(a);
	        d().insertAdjacentHTML("AfterEnd", "<div id='" + DIV_ID + "' style='display:none'></div>");
	        e("histogram", a)
	    };
	    this.render_data = function (a) {
	        DIV_ID = "statd_embed_data_" + f(a);
	        d().insertAdjacentHTML("AfterEnd", "<div id='" + DIV_ID + "' style='display:none'></div>");
	        e("data", a)
	    };
	    this.render_table = function (a) {
	        DIV_ID = "statd_embed_table_" + f(a);
	        d().insertAdjacentHTML("AfterEnd", "<div id='" + DIV_ID + "' style='display:none'></div>");
	        e("table", a)
	    };
	    this.render_text = function (a) {
	        DIV_ID = ["statd_embed_text", a.s1, a.u].join("_");
	        d().insertAdjacentHTML("AfterEnd", "<div id='" + DIV_ID + "' style='display:none'></div>");
	        e("text", a)
	    };
	    this.render = function (a) {
	        a.tf || (a.tf = "week_compare");
	        a.kind || (a.kind = "graph");
	        switch (a.kind) {
	        case "graph":
	            this.render_graph(a);
	            break;
	        case "histogram":
	            this.render_histogram(a);
	            break;
	        default:
	            this.render_graph(a)
	        }
	    }
	};
	
	var
		moment = require('uwap-core/js/moment');
		
	var Temp = function(container) {
		this.container = container;
		this.type = 0;

	    this.colors = {
	    	'Ute': '#66c',
    		'Kjeller': '#aaa',
			'Linneas rom': '#5c1',
			'Stue': '#f78'
	    };

		this.containerNow = $('#tempnow');
		this.containerTS = $('<div class="ts"></div>').appendTo(this.container);

		this.loadNow();
		// this.loadTS();
		setInterval($.proxy(this.loadNow, this), 90*1000); // 90 seconds
		// setInterval($.proxy(this.loadTS, this), 3*60*1000+3); // 3 minutes


		this.loadSH()
		setInterval($.proxy(this.loadSH, this), 90*1000); // 90 seconds



		// StatHatEmbed.render({s1: 'VP6Z', s2: 'KN49', s3: 'FCjV', w: 500, h: 200, tf: 'half_compare', style: 'fill'});


	};

	var lastValue = function(arr) {
		for(var i = arr.length-1; i >= 0; i--) {
			console.log("Checking", arr[i]);
			if (arr[i].value !== 0) return arr[i];
		}
		return null;
	}

	Temp.prototype.loadSH = function() {	
		console.log("››››››› LOAD NOW !!!!")
		$("div#tcontainer").
			empty().
			append('<div id="tcontainer2"></div>');
			// append('<img src="https://www.stathat.com//graphs/3e/74/f61b0f10bf70b4b4ae89a933f94d.png?ts=' + (new Date).getTime()+ '" />');
		if (this.type ===0) {
			this.type = 1;
			StatHatEmbed.render({s1: "VP6Z", s2: "Tfofh", s3: "FCjV", w: 500, h: 200, title: 'Temperatur', tf: "day", style: "fill"});
		} else {
			this.type = 0;
			StatHatEmbed.render({s1: "VP6Z", s2: "Tfofh", s3: "FCjV", w: 500, h: 200, title: 'Temperatur', tf: "week", style: "fill"});
		}
		
			// append('<script type="text/javascript" href="https://solberg.uwap.org/js/libs/stathat.js?' + (new Date).getTime()+ '" ></script>');
			// append('<script>StatHatEmbed.render({s1: "VP6Z", s2: "KN49", s3: "FCjV", w: 500, h: 200, tf: "half_compare", style: "fill"});</script>');
	}

	Temp.prototype.loadNow = function() {

		console.log("     ---------------------- Load now")
		// var url = "http://solweb.no/data/twine/temp.php";
		var url = 'https://www.stathat.com/x/MQxvlYzPv6t106JpwRH8/data/VP6Z/KN49/FCjV/Tfofh?t=30m1m';
		// UWAP.data.get(url, {handler: "solberg"}, $.proxy(this.responseNow, this));
		UWAP.data.get(url, {}, $.proxy(this.responseNow, this));
	}


	Temp.prototype.responseNow = function(data) {
		var 
			i, el;

		console.log("---------=================================================================temp response");
		console.log(data);
		$(this.containerNow).empty();

		var text = '';
		var titem = null;
		var tvalue = null;
		var tr, since;

		for(i = 0; i < data.length; i++) {
			titem = data[i];
			tvalue = lastValue(titem.points);

			if (tvalue === null) continue;
				// titem.points[titem.points.length-1];
			tr = Math.round(tvalue.value * 10.0)/10.0;

			console.log("ke is " , titem)
			console.log('Last ', tvalue, tr);

			console.log((new Date).getTime()/1000, tvalue.time);
			since = Math.round((new Date).getTime()/1000 - tvalue.time);

			console.error("Since", since);

			text += '<tr><td class="tempLoc">' + 
				titem['name'] + ' ' +
				'<div style="width: 10px; display: inline; height: 10px; margin: 6px 5px; background: ' + this.colors[data[i]['name']] + '; border: 1px solid #888; padding: 1px">&nbsp;</div>' +
				'</td><td class="tempValue">' + tr + '</td></tr>';
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

	    var chd = [];
	    for(var key in data) {
	    	console.log("TEMP DATA ›››› ", data[key]['data']);
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
	        { color: '#777', lineWidth: 1, xaxis: { from: moment().sod().utc() - 2*3600*1000, to: moment().sod().utc() + 7*3600*1000 } },
	        { color: '#aaf', lineWidth: 1, yaxis: { from: -10, to: 0 } },
	    ];

		$(this.containerTS).empty().append('<div id="flophTemp" style="width:100%;height:200px;"></div>');
		$.plot($("#flophTemp"), 
			chd, 
			{
				grid: { 
					markings: markings 
				},
				xaxis: { 
					timezone: "browser",
					min:  now.clone().subtract('hours', 22).startOf('hour').valueOf(),
					max: now.clone().add('minutes', 59).startOf('hour').valueOf(),
					mode: "time"
				}
				// yaxis: {min: -20, max: 30},
				// legend: {container: $("#tcontainer")}
			}
		);
	}

	return Temp;

});