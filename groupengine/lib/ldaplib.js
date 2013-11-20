var ldap = require('ldapjs');
var util = require('util');
var assert = require('assert');



var ResultSet = function() {
	this.entries = [];
}

ResultSet.prototype.addEntry = function(entry) {
	assert.ok(entry instanceof ResultEntry);

	this.entries.push(entry);
}

ResultSet.prototype.isEmpty = function() {
	return this.entries.length === 0;
}

ResultSet.prototype.getMapped = function(map) {
	var res = [];
	for(var i = 0; i < this.entries.length; i++) {
		res.push(this.entries[i].getMapped(map));
	}
	return res;
}



var ResultEntry = function(obj) {
	this.obj = obj;

	this.processedObject = {};
	
	for(var key in this.obj.object) {
		// console.log("Key " + key);
		if (key === 'jpegPhoto') {
			var buffer = this.getAttributeBuffer('jpegPhoto');
			// console.log("Buffer ", buffer);
			if (buffer) {
				this.processedObject[key] = buffer[0].toString('base64');	
				// console.log(buffer);
			}
			
		} else {
			this.processedObject[key] = this.obj.object[key];
		}


	}


	// if (this.obj.object['jpegPhoto']) {
	// 	console.log(this.getAttributeBuffer('jpegPhoto'));
	// 	this.obj.object['jpegphoto'] = new Buffer(this.getAttributeBuffer('jpegphoto'), 'binary').toString('base64');

	// }
	// console.log(this.processedObject);

	// exit;
}

ResultEntry.prototype.getAttributeBuffer = function(key) {
	for(var i = 0; i < this.obj.attributes.length; i++) {
		if (this.obj.attributes[i]['type'] === key) {
			return this.obj.attributes[i].buffers;
		}
	}
	return null;
}

ResultEntry.prototype.hasProperty = function(prop) {

	if (this.processedObject[prop]) return true;
	return false;
}

ResultEntry.prototype.getMapped = function(map) {
	return map.map(this.processedObject);
}
ResultEntry.prototype.getObject = function(map) {
	return this.processedObject;
}

ResultEntry.prototype.getDN = function(map) {
	return this.processedObject.dn;
}

ResultEntry.prototype.getAliasedObjectName = function() {
	if (this.obj.aliasedObjectName) {
		return aliasedObjectName;
	}
	return null;
}

ResultEntry.prototype.getValueArray = function(key) {
	if (!this.processedObject[key]) return [];
	return LDAP.arrayize(this.obj.object[key]);
}



var ObjectMap = function(config) {
	assert.ok(typeof config === 'object');
	this.config = config;
}

ObjectMap.prototype.getAttrs = function() {
	var attrs = [];
	for(var key in this.config.map) {
		attrs.push(key);
	}
	return attrs;
}

ObjectMap.prototype.map = function(data) {
	assert.ok(typeof data === 'object');
	var result = {}, key;

	for(key in this.config.map) {
		if (data[key]) {
			result[this.config.map[key]] = LDAP.singlify(data[key]);
			// if (key == 'jpegPhoto') {
			// 	result[this.config.map[key]] = new Buffer(result[this.config.map[key]], 'binary').toString('base64');
			// }
		}
	}
	if (this.config.prefix) {
		for(key in this.config.prefix) {
			if (result[key]) {
				result[key] = this.config.prefix[key] + ':' + result[key];	
			}
		}
			
	}
	if (this.config.attr) {
		for(key in this.config.attr) {
			result[key] = this.config.attr[key];
		}
	}	

	return result;
}




var LDAP = function(config) {
	assert.ok(typeof config === 'object');
	this.config = config;

	this.client = ldap.createClient({
		url: this.config.config['host']
	});

}


LDAP.prototype._ldapCoreSearch = function(dn, options, multiple, callback) {

	this.client.search(dn, options, function(err, res) {

		var 
			results = new ResultSet(), 
			result = null;

		if (err instanceof ldap.LDAPError) {
			console.error("Trouble! Error with LDAP connection.");
			callback(null);
			return;
		}

		res.on('searchEntry', function(entry) {

			result = new ResultEntry(entry);

			if (multiple) {
				results.addEntry(result);
			}

		});

		res.on('error', function(err) {
			console.error('error: ' + err.message);
		});

		res.on('end', function(ldapresult) {
			if (multiple) {
				callback(results);
			} else {
				callback(result);
			}
			
		});

	});

}


LDAP.prototype.ldapLookup = function(dn, attrs, callback) {

	var options = {
		"scope": "base",
		"filter": "(objectClass=*)",
		"attributes": attrs,
		"timeLimit": 5
	};
	this._ldapCoreSearch(dn, options, false, callback);
}




LDAP.prototype.ldapSearch = function(base, query, type, attrs, multiple, callback) {

	assert.ok(typeof query === 'object' || typeof query === 'string');

	var options = {
		"scope": type,
		"attributes": attrs,
		"timeLimit": 5
	};
	if (typeof query === 'string') {
		options.filter = query;
	} else {
		options.filter = LDAP.buildFilter(query);	
	}
	
	// console.log("Filter ");
	// console.log(query);
	// console.log('----');
	// console.log(options);

	this._ldapCoreSearch(base, options, multiple, callback);

}







LDAP.prototype.mapResultEntry = function(data, mapping, includeMeta) {


	var result = {}, key;

	for(key in mapping.map) {
		if (data[key]) {
			result[mapping.map[key]] = LDAP.singlify(data[key]);
		}
	}
	if (mapping.prefix) {
		for(key in mapping.prefix) {
			if (result[key]) {
				result[key] = mapping.prefix[key] + ':' + result[key];	
			}
		}
			
	}
	if (mapping.attr) {
		for(key in mapping.attr) {
			result[key] = mapping.attr[key];
		}
	}

	if(includeMeta) {
		result["_"] = data["_"];
	}
	
	

	return result;
}


/**
 * Lookup up a DN that refers to a group definition. Then performs a mapping operation
 * to include the proper properties of a group.
 *
 * The callback is then called with the resulting group defintion.
 * 
 * @param  {[type]}   dn       [description]
 * @param  {[type]}   mapping  [description]
 * @param  {Function} callback [description]
 * @return {[type]}            [description]
 */
LDAP.prototype.lookupGroupMappedX = function(dn, mapping, callback) {

	var groupattrs = [];
	for(var key in mapping.map) {
		groupattrs.push(key);
	}

	this.ldapLookup(dn, groupattrs, function(groupResultEntry) {

		var group = {}, key;

		for(key in mapping.map) {
			if (groupResultEntry[key]) {
				group[mapping.map[key]] = LDAP.singlify(groupResultEntry[key]);
			}
		}
		if (mapping.prefix) {
			for(key in mapping.prefix) {
				if (group[key]) {
					group[key] = mapping.prefix[key] + ':' + group[key];	
				}
			}
				
		}
		if (mapping.attr) {
			for(key in mapping.attr) {
				group[key] = mapping.attr[key];
			}

		}

		group["_"] = groupResultEntry["_"];

		callback(group);

	});

}




LDAP.countProps = function(obj) {
	var c = 0;
	if (obj === null) return 0;
	if (!obj) return 0;

	assert.ok(typeof obj === 'object');
	for(var key in obj) {
		if (obj.hasOwnProperty(key)) c++;
	}
	return c;
}

LDAP.buildFilter = function(query) {
	assert.ok(typeof query === 'object');

	// console.log("build filter ", query);

	var c = LDAP.countProps(query);
	// console.log("Building a query for ", query);
	if (c === 0) {
		return  new ldap.EqualityFilter({
			"attribute": "objectClass",
			"value": "*"
		});
	} else {

		var eFilters = [];
		for(var key in query) {
			eFilters.push(new ldap.EqualityFilter({
				"attribute": key,
				"value": query[key]
			}));
		}
		if (c === 1) {
			return eFilters[0];
		}

		return new AndFilter({
			"filters": eFilters
		});

	}
}

LDAP.sfilter = function(key, value) {
	var o = {};
	o[key] = value;
	return o;
}


LDAP.arrayize = function(a) {
	return util.isArray(a) ? a : [a];
}

LDAP.singlify = function(a) {
	return util.isArray(a) ? a[0] : a;
}


exports.ResultEntry = ResultEntry;
exports.ResultSet = ResultSet;
exports.ObjectMap = ObjectMap;
exports.LDAP = LDAP;

