


/**
 * The WaitGuard is a helper class that allows several operations to run in parallell
 *
 * The completedCallback will be executed when all actions are completed, or the timeout
 * is reached.
 *
 * The callback is called with one parameter which is a sourceStatus object, presenting metadata about
 * the sources that are executed.
 * 
 * @param {[type]} completedCallback [description]
 * @param {[type]} waitMilliSeconds  Number of milliseconds before timeout.
 */
var WaitGuard = function(completedCallback, waitMilliSeconds) {

	this.sourceStatus = {};
	this.actions = {};

	this.completedCallback = completedCallback;
	this.waitMilliSeconds = waitMilliSeconds;

	// Are we done/completed already?
	this.executed = false;

}


WaitGuard.prototype.execute = function() {
	if (this.executed) {
		console.error('Execution cancelled. Already performed.');
		return;
	}

	this.executed = true;
	this.completedCallback(this.sourceStatus);
}

WaitGuard.prototype.allCompleted = function() {
	for(var key in this.sourceStatus) {
		if (!this.sourceStatus[key]) return false;
	}
	return true;
}

WaitGuard.prototype.addAction = function (sourceID, actionCallback) {
	var that = this;
	this.sourceStatus[sourceID] = false;
	this.actions[sourceID] = actionCallback;

	

}

WaitGuard.prototype.startTimer = function () {
	// console.log("-----] STARTTIMER ", parallellActions);
	var that = this;
	// if (this.actions.length === 0) {
	// 	console.log('Executing because no action is scheduled....');
	// 	if (!executed) execute();
	// 	return;
	// }

	var counter = 0;

	for(var sourceID in this.actions) {
		counter++;
		var actionDoneCallback = (function() {

			var currentSourceID = sourceID;
			return function () {
				var i;
				that.sourceStatus[currentSourceID] = true;

				console.error('- - - -] Completed source ' + currentSourceID);
				// console.error(that.sourceStatus);

				if (that.allCompleted()) {
					console.error('- - ] All done, executing.');
					that.execute();
				} else {
					// console.error('- - ] All NOT NOT NOT done, executing.');
					// console.error(that.sourceStatus);
				}

			};

		})();

		this.actions[sourceID](actionDoneCallback);
	}
	if (counter === 0) that.execute();

	setTimeout(function() {
		console.error('Action timeout!');
		if (!that.executed) that.execute();
	}, this.waitMilliSeconds);

}


exports.WaitGuard = WaitGuard;

