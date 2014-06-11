
define(function(require) {

	var 
		jso = require('uwap-core/js/oauth'),
		moment = require('uwap-core/js/moment'),
		prettydate = require('uwap-core/js/pretty'),
		models = {},

		Class = require('uwap-core/js/class');


	/**
	 * [description]
	 * @return {[type]}            [description]
	 */
	var Model = Class.extend({
		"init": function(props) {
			if (!this._) this._ = {};
			for (var key in props) {
				this[key] = props[key];
			}
			return this;
		},
		"get": function(key) {
			return this[key];
		},
		"getView": function() {
			var obj = {};
			for(var key in this) {
				if (key === '_') continue;
				if (typeof this[key] !== 'function') {
					obj[key] = this[key];
				}
			}
			return obj;
		}
	});


	models.GroupType = Model.extend({

	});

	models.Role = Model.extend({
		"init": function(props, user, group) {
			if (!this._) this._ = {};
			if (user instanceof models.User) {
				this._.user = user;
			}
			if (group instanceof models.Group) {
				this._.group = group;
			}

			return this._super(props);
		}
	});


	models.ListResponse = Model.extend({
		"init": function(props) {
			var resources = null;
			var grouptypes = null;
			if (props.Resources) {
				resources = props.Resources;
				delete props.Resources;
			}
			if (props.GroupTypes) {
				grouptypes = props.GroupTypes;
				delete props.GroupTypes;
			}

			this._super(props);
			this._.GroupTypes = {};
			this._.Resources = [];

			if (resources == null) return this;
			var i, n;


			if (grouptypes) {
				for(i = 0; i < grouptypes.length; i++) {
					n = new models.GroupType(grouptypes[i]);
					this._.GroupTypes[n.id] = n;
				}				
			}
			if (resources) {
				for(i = 0; i < resources.length; i++) {
					n = new models.Group(resources[i]);
					if (n.groupType && this._.GroupTypes.hasOwnProperty(n.groupType)) {
						n.setGroupType(this._.GroupTypes[n.groupType]);
					}
					this._.Resources.push(n);
				}
			}
			return this;
		},

		"getGroupTypes": function() {
			return this._.GroupTypes;
		},

		"getByID": function(id) {

			if (this._ && this._.Resources) {

				for(var i = 0; this._.Resources.length; i++) {
					if (this._.Resources[i].id === id) return this._.Resources[i];
				}

			}

			return null;

		},

		"getView": function() {
			// console.error("Prepping getview from list");
			var obj = this._super();
			obj.Resources = [];
			if (this._.Resources.length > 0) {
				for(var i = 0; i < this._.Resources.length; i++) {
					obj.Resources.push(this._.Resources[i].getView());
				}
			}
			return obj;
		}
	})

	models.Group = Model.extend({
		"init": function(props) {

			if (!this._) this._ = {};
			this._.role = null;

			if (props.vootRole) {
				this._.role = new models.Role(props.vootRole);
				delete props.vootRole;
			}

			return this._super(props);
		},
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
		"setGroupType": function(grouptype) {
			this._.groupType = grouptype;
		},
		"getView": function() {
			var obj = this._super();
			if (this._.role) {
				obj.vootRole = this._.role.getView();
			}
			if (this._.groupType) {
				obj.groupType = this._.groupType.getView();
			} else {
				obj.groupType = {
					"id": obj.groupType,
					"displayName": obj.groupType
				}
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

		}
	});

	models.Role = Model.extend({
		"photourl": function() {
			return UWAP.utils.getEngineURL('/media/user/' + this.a );
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
			// tmp['photourl'] = this.photourl();

			// tmp.roleType = {};
			// tmp.roleType[this.role] = true;
			return tmp;
		}
	});


	models.User = Model.extend({
		"photourl": function() {
			return UWAP.utils.getEngineURL('/media/user/' + this.a );
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
		return UWAP.utils.getEngineURL('/media/logo/app/' + this.id);
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