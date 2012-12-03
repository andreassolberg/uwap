
define(function(require) {

	var 
		jso = require('uwap-core/js/oauth'),
		moment = require('uwap-core/js/moment'),
		prettydate = require('uwap-core/js/pretty'),
		models = {};



	models.FeedItem = function (props) {
		for (var key in props) {
			this[key] = props[key];
		}
	};

	models.FeedItem.prototype.getObject = function(str) {
		console.log("getObject: ", this);
		return this.activity;
	}

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
		var m = moment(this.dtstart);
		// var m = moment(this.datetime);
		return m.format('DD. MMMM YYYY, HH:mm');
	}

	models.FeedItem.prototype.getDeadlineDT = function(a) {
		// console.log("this, a, b", this, a, b);
		// console.log("getDeadlineDT", this, a);
		var m = moment(this.signup.deadline);
		return m.format('DD. MMMM YYYY, HH:mm');
	}
	

	models.FeedItem.prototype.getUntil = function() {
		var m = moment(this.dtstart);
		return prettydate.prettyUntil(m);
	}


	return models;


});