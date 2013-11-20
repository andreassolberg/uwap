

var Agresso = function(config) {
	this.config = config;
}

Agresso.prototype.getByUser = function(input, callback) {
	setTimeout(function() {
		callback({
			"agresso1": {
				"title": "Agresso Static Example",
				"role": "member"
			}
		});
	}, 2);

}

exports.Agresso = Agresso;

