


var Group = function(attr) {

	this.id = null;

	if (attr && typeof attr === 'object') {
		for(var key in attr) {
			this[key] = attr[key];
		}
	}

};


exports.Group = Group;

