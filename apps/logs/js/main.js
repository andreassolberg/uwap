$(document).ready(function() {
	

	function format (m) {
		var mom = moment(m);
		return mom.format('HH:mm:ss') + ' ' + mom.milliseconds();
	} 


	function filterBtn(o) {


		var html = '<!-- <div class="btn-toolbar"> -->' +
        '<div class="filterbtn btn-group"><button class="btn btn-mini">Filter</button>' +
        '<button class="btn btn-mini dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>' + 
        	'<ul class="dropdown-menu">' +
            '<li><a href="#">by module ' + o.module + '</a></li>' +
            '<li><a href="#">by ip ' + o.ip + '</a></li>' +
            '<li><a href="#">by host ' + o.host + '</a></li>' +
            '<li><a href="#">by subid ' + o.subid + '</a></li>' +
            '<li class="divider"></li>' +
            '<li><a href="#">Separated link</a></li>' +
          '</ul></div><!-- /btn-group --><!-- </div> -->';
      return html;

	}


	UWAP.auth.require(function(user) {

		require(['./libs/moment', './LogRetriever'], function(moment, LogRetriever) {

			var container = $("div#logout");

			container.on("click", "div.logMain", function(event) {
				$(event.currentTarget).closest(".logentry").toggleClass("active");
				return false;
			});

			container.on("click", "div.filterbtn a", function(event) {
				console.log("clicked fliter button ", event.currentTarget);
				// $(event.currentTarget).toggleClass("active");
				// return false;
			});

			var logr = new LogRetriever(function(logs) {
				console.log("Log files from " + moment(logs.from).format('HH:mm:ss SSS') + ' to ' + moment(logs.to).format('HH:mm:ss SSS') );
				console.log(logs.data);

				$.each(logs.data, function(i, logentry) {
					var o =  '';
					if (logentry.object) {
						o = '<pre class="logobject">' + JSON.stringify(logentry.object, undefined, 4) + '</pre>';
					}

					$(container).prepend('<div class="logentry level' + logentry.level + '">' + 
						'<div class="logMain"><span class="time">' + moment(logentry.time).format('HH:mm:ss.SSS') + '</span> ' +
						'<span class="module">' + logentry.module + '</span> ' +
						'<span class="subid">' + logentry.subid + '</span> ' +
						'<span class="host">' + logentry.host + '</span> ' +
						'<span class="ip">' + logentry.ip + '</span> ' +
						logentry.message + '</div>' +
						'<div class="logExtra">' + filterBtn(logentry) + o +'</div>' +
						'</div>');
				});

			});



			// UWAP.logs.get(after, function(logs) {
				
			// 	if (logs.data === null) {

			// 		console.log("Empty output");
			// 		return;
			// 	}

			// 	console.log("Log files from " + moment(logs.from).format('HH:mm:ss SSS') + ' to ' + moment(logs.to).format('HH:mm:ss SSS') );
			// 	console.log(logs.data);


			// });

		});
	});





});