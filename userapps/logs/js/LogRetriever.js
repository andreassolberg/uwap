define(['libs/moment'], function(moment) {
	

	var LogRetriever;

	LogRetriever = function(callback) {
		this.timer = null;

		this.filters = [];
		this.callback = callback;
		this.cursor = ((new Date()).getTime() / 1000.0) - 1.0;
		
		this.running = false;
	};

	LogRetriever.prototype.play = function() {
		this.running = true;
		this.getLogs();
	}
	LogRetriever.prototype.pause = function() {
		this.running = false;
		if(this.timer) clearTimeout(this.timer);
	}

	LogRetriever.prototype.updateCursor = function(time) {
		// console.log("Updating cursor from " + moment.unix(this.cursor).format('HH:mm:ss.SSS') + ' to ' + moment.unix(time).format('HH:mm:ss.SSS'));
		// console.log("Updating cursor from " + this.cursor + ' to ' + time);
		this.cursor = time;
	};

	LogRetriever.prototype.getLogs = function() {

		console.log("About to request logs from " + moment.unix(this.cursor).format('HH:mm:ss.SSS') + '  cursor in ms ' + this.cursor);

		var that = this;
		UWAP.logs.get(this.cursor, this.filters, function(logs) {
			if (logs.data !== null) {
				that.callback(logs);
				that.updateCursor(logs.to);
			} else {
				// console.log("Empty log result");
			}
			if (that.running) {
				that.timer = setTimeout($.proxy(that.getLogs, that), 1000);	
			} else {
				console.log("Not scheduling new log retrieval, because paued.");
			}
			

		}, function(err) {
			console.error("Error occured fetching logs. Stopping.");
		});
	}

	LogRetriever.prototype.setFilter = function(filters) {
		this.filters = filters;
	};

	LogRetriever.prototype.resetFrom = function(from) {
		if(this.timer) clearTimeout(this.timer);
		if (!from) throw new Error("Missing [from] parameter to LogRetriever.resetForm()");
		this.cursor = from;
		this.getLogs();
	};

	return LogRetriever;
});