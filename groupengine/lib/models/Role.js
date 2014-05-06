


var Role = function() {
	this.id = null;

	this.basic = null;
	this.notBefore = null;
	this.notAfter = null;
	


	if (attr && typeof attr === 'object') {
		for(var key in attr) {
			this[key] = attr[key];
		}
	}
	
};




exports.Role = Role;

