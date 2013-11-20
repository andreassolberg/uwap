
var Static = function(config) {
	this.config = config;
}

Static.prototype.getByUser = function(input, callback) {
	callback(this.config.config.groups);
}

exports.Static = Static;


