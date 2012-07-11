define(['libs/moment'], function(moment) {
	

	var LogRetriever;

	LogRetriever = function(callback) {
		this.callback = callback;
		this.cursor = ((new Date()).getTime() / 1000.0) - 1.0;
		this.getLogs();
	};

	LogRetriever.prototype.updateCursor = function(time) {
		console.log("Updating cursor from " + moment.unix(this.cursor).format('HH:mm:ss.SSS') + ' to ' + moment.unix(time).format('HH:mm:ss.SSS'));
		console.log("Updating cursor from " + this.cursor + ' to ' + time);
		this.cursor = time;
	};

	LogRetriever.prototype.getLogs = function() {

		console.log("About to request logs from " + moment.unix(this.cursor).format('HH:mm:ss.SSS') + '  cursor in ms ' + this.cursor);

		var that = this;
		UWAP.logs.get(this.cursor, function(logs) {
			if (logs.data !== null) {
				that.callback(logs);
				that.updateCursor(logs.to);
			} else {
				console.log("Empty log result");
			}

			setTimeout($.proxy(that.getLogs, that), 1000);

		}, function(err) {
			console.error("Error occured fetching logs. Stopping.");
		});
	}



	return LogRetriever;
});