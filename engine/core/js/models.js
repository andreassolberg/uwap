
define(function(require) {

	var 
		jso = require('uwap-core/js/oauth'),
		moment = require('uwap-core/js/moment'),
		prettydate = require('uwap-core/js/pretty'),
		models = {},

		Class = require('uwap-core/js/class');


	var Model = Class.extend({
		"init": function(props) {
			for (var key in props) {
				this[key] = props[key];
			}
		},
		"get": function(key) {
			return this[key];
		},
		"getView": function() {
			var obj = {};
			for(var key in this) {
				if (typeof this[key] !== 'function') {
					obj[key] = this[key];
				}
			}
			return obj;
		}
	});


	models.Group = Model.extend({
		"getTypeText": function() {
			var types = {
				"uwap:group:type:ad-hoc": {"title": "Ad-hoc group", "icon": "xxx"},
				"uwap:group:type:org": {"title": "Organization group", "icon": "xxx"},
				"uwap:group:type:orgUnit": {"title": "Organization unit group", "icon": "xxx"}
			};
			if (types[this.type]) {
				return types[this.type];
			}
			return this.type;
		},
		"getRoleValue": function() {
			var roles = {
				"owner": 3,
				"admin": 2,
				"member": 1
			};
			if (roles.hasOwnProperty(this.role)) {
				return roles[this.role];
			}
			return 0;
		},
		"getView": function() {
			var obj = this._super();
			obj.typeText = this.getTypeText();
			obj.roleType = {};
			if (this.role) {
				obj.roleType[this.role] = true;	
			}
			return obj;
		}
	}),

	models.Set = Model.extend({
		"init": function(props) {
			this.items = [];
			if (props && props.items) {
				var items = props.items;
				
				for(var i = 0; i < props.items.length; i++) {
					this.addItem(props.items[i]);
				}
				delete props.items;
			}

			this._super(props);
		},
		"addProps": function(props) {
			for(var key in props) {
				this.addItem(props[key]);
			}
		},
		"addArray": function(arr) {
			console.log("adding", arr);
			for(var i = 0; i < arr.length; i++) {
				this.addItem(arr[i]);
			}
			console.log(this.items);
		},
		"addItem": function(item) {
			this.items.push(item);
		},
		"getView": function() {
			var x = [];
			if (this.items) {
				for(var i = 0; i < this.items.length; i++) {
					if (this.items[i].getView) {
						x.push(this.items[i].getView());	
					} else {
						x.push(this.items[i]);
					}
				}
			}

			return x;
		}
	});

	models.Groups = models.Set.extend({
		"addItem": function(item) {
			this._super(new models.Group(item));
		},
		"getByID": function(id) {
			for(var i = 0; i < this.items.length; i++) {
				if (this.items[i].id === id) return this.items[i];
			}
			return null;
		},
		"sort": function(a,b) {
			// console.log("Comparing", a, b);
			
			if (a.type === b.type) {

				if (a.getRoleValue() === b.getRoleValue()) {

					if (a.name == b.name) {
						return 0;
					} else if(a.name > b.name) {
						return 1;
					} else {
						return -1;
					}

				} else return (b.getRoleValue() - a.getRoleValue());

			} else if(a.type < b.type) {
				return 1;
			} else {
				return -1;
			}


		},
		"getView": function() {
			this.items = this.items.sort(this.sort);
			return this._super();
		}


	});



	models.GroupMembers = models.Set.extend({
		"addItem": function(item) {
			this._super(new models.Role(item));
		},
		"sort": function(a,b) {
			// console.log("Comparing", a, b);
			if (a.getRoleValue() === b.getRoleValue()) {

				if (a.name == b.name) {
					return 0;
				} else if(a.name > b.name) {
					return 1;
				} else {
					return -1;
				}

			} else return (b.getRoleValue() - a.getRoleValue());
		},
		"getByUserID": function(userid) {
			for(var i = 0; i < this.items.length; i++) {
				if (userid === this.items[i].userid) return this.items[i];
			}
			return null;
		},
		"getView": function() {

			this.items = this.items.sort(this.sort);

			var obj = [];
			for(var i = 0; i < this.items.length; i++) {
				obj.push(this.items[i].getView());
			}
			return obj;
			// return obj;
		}
	});

	models.Role = Model.extend({
		"photourl": function() {
			return UWAP.utils.getEngineURL('/api/media/user/' + this.a );
		},
		"getRoleValue": function() {
			var roles = {
				"owner": 3,
				"admin": 2,
				"member": 1
			};
			if (roles.hasOwnProperty(this.role)) {
				return roles[this.role];
			}
			return 0;
		},
		"getView": function() {
			var tmp = this._super();
			tmp['photourl'] = this.photourl();

			tmp.roleType = {};
			tmp.roleType[this.role] = true;
			return tmp;
		}
	});


	models.User = Model.extend({
		"photourl": function() {
			return UWAP.utils.getEngineURL('/api/media/user/' + this.a );
		},
		"getView": function() {
			var tmp = this._super();
			tmp['photourl'] = this.photourl();
			return tmp;
		}
	});



	models.Feed = function(data) {
		this.items = [];

		this.clients = {};
		this.users = {};

		this.range = data.range;

		// console.log("------------------------------ Producing a feed model");
		// console.log(data);


		if (data.clients) {
			// console.log("-- YES CLIENT");
			for(var clientid in data.clients) {
			// for(var i = 0; i < data.clients.length; i++) {
				// console.log("-- This client", data.clients[clientid]);
				this.clients[clientid] = new models.Client(data.clients[clientid]);

			}
		}

		if (data.users) {
			for(var userid in data.users) {
			// for(var i = 0; i < data.users.length; i++) {
				this.users[userid] = new models.User(data.users[userid]);
			}
		}

		if (data.items) {
			for(var i = 0; i < data.items.length; i++) {
				var feeditem = new models.FeedItem(data.items[i]);
				if (feeditem['uwap-clientid'] && this.clients[feeditem['uwap-clientid']]) {
					feeditem.client = this.clients[feeditem['uwap-clientid']];
				}
				if (feeditem['uwap-userid'] && this.users[feeditem['uwap-userid']]) {
					feeditem.user = this.users[feeditem['uwap-userid']];
				}
				this.items.push(feeditem);
			}
		}
		
	}



	models.Client = function (props) {
		for (var key in props) {
			this[key] = props[key];
		}
	};

	models.Client.prototype.logo = function() {
		// console.log("properties of client is", this.id);
		return UWAP.utils.getEngineURL('/api/media/logo/app/' + this.id);
	}



	models.FeedItem = function (props) {
		for (var key in props) {
			this[key] = props[key];
		}
	};

	// models.FeedItem.prototype.getObject = function(str) {
	// 	console.log("getObject: ", this);
	// 	return this.activity;
	// }

	models.FeedItem.prototype.hasClass = function(cls) {
		if (!this['class']) return false;
		for(var i = 0; i < this['class'].length; i++) {
			if (this['class'][i] === cls) return true;
		}
		return false;
	}

	models.FeedItem.prototype.allowSignup = function() {
		var s;
		if (!this.signup) return false;
		s = this.signup;
		s.hasDeadline = !!s.deadline;

		if (s.hasDeadline) {
			var m = moment(s.deadline);
			s.deadlineH = this.getDeadlineDT();
			s.deadlineUntil = prettydate.prettyUntil(m);
		}
		
		s.deadlineH = this.getDeadlineDT();
		return s;
	}

	models.FeedItem.prototype.isEvent = function() {
		// console.log("this, a, b", this, a, b);
		return this.hasClass('event');
	}

	models.FeedItem.prototype.getDT = function() {
		var m = moment(this.dtstart*1000);
		// var m = moment(this.datetime);
		return m.format('DD. MMMM YYYY, HH:mm');
	}

	models.FeedItem.prototype.getDeadlineDT = function(a) {
		// console.log("this, a, b", this, a, b);
		// console.log("getDeadlineDT", this, a);
		var m = moment(this.signup.deadline*1000);
		return m.format('DD. MMMM YYYY, HH:mm');
	}
	

	models.FeedItem.prototype.getUntil = function() {
		var m = moment(this.dtstart*1000);
		return prettydate.prettyUntil(m);
	}


	return models;


});