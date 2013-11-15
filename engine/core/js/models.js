
define(function(require) {

	var 
		jso = require('uwap-core/js/oauth'),
		moment = require('uwap-core/js/moment'),
		prettydate = require('uwap-core/js/pretty'),
		models = {};



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


	models.User = function (props) {
		for (var key in props) {
			this[key] = props[key];
		}
	};

	models.User.prototype.photourl = function() {
		// console.log("properties of client is", this.id);
		// media/user
		return UWAP.utils.getEngineURL('/api/media/user/' + this.a );
	}

	models.User.prototype.getView = function() {
		var tmp = {};
		for (var key in this) {
			if (!this.hasOwnProperty(key)) continue;
			if (typeof this[key] === 'function') continue;
			tmp[key] = this[key];
		}
		tmp['photourl'] = this.photourl();
		return tmp;
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