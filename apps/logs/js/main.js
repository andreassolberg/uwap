$(document).ready(function() {
	

	function format (m) {
		var mom = moment(m);
		return mom.format('HH:mm:ss') + ' ' + mom.milliseconds();
	} 



	UWAP.auth.require(function(user) {

		require(['./libs/moment', './LogRetriever'], function(moment, LogRetriever) {

			var logr = new LogRetriever(function(logs) {
				console.log("Log files from " + moment(logs.from).format('HH:mm:ss SSS') + ' to ' + moment(logs.to).format('HH:mm:ss SSS') );
				console.log(logs.data);
				
				$.each(logs.data, function(i, logentry) {
					$("div#logout").prepend('<div class="logentry level' + logentry.level + '">' + 
						'<span class="time">' + moment(logentry.time).format('HH:mm:ss.SSS') + '</span> ' +
						'<span class="module">' + logentry.module + '</span> ' +
						'<span class="subid">' + logentry.subid + '</span> ' +
						'<span class="host">' + logentry.host + '</span> ' +
						'<span class="ip">' + logentry.ip + '</span> ' +
						logentry.message + '</div>');
				});

// level	3		
// time	1341995030.61607		
// host	"bridge.uninett.no"		
// module	"auth"		
// subid	"chat"


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