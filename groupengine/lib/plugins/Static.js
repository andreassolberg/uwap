var 

	Group = require('../models/Group').Group,
	Role = require('../models/Role').Role
	;


var Static = function(config) {
	this.config = config;
}

Static.prototype.createGroupObj = function(props) {


	var may = {
		'listMembers': false,
		'manageGroup': false,
		'manageMembers': false
	};

	if (props.vootRole) {
		props.vootRole.may = may;	
	}

	var group = new Group(props);
	return group;


}

Static.prototype.getByUser = function(input, callback) {

	var obj = [], newobj;

	for(var i = 0; i < this.config.config.groups.length; i++) {
		newobj = this.createGroupObj(this.config.config.groups[i]);
		if (newobj === null) continue;
		obj.push(newobj);
	}
	return callback(obj);
}

exports.Static = Static;


