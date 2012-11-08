
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

	models.FeedItem.prototype.hasClass = function(cls) {
		if (!this['class']) return false;
		for(var i = 0; i < this['class'].length; i++) {
			if (this['class'][i] === cls) return true;
		}
		return false;

	}

	models.FeedItem.prototype.getDT = function() {
		var m = moment(this.dtstart);
		// var m = moment(this.datetime);
		return m.format('DD. MMMM YYYY, HH:mm');
	}

	models.FeedItem.prototype.getDeadlineDT = function() {
		
		var m = moment(this.signup.deadline);
		return m.format('DD. MMMM YYYY, HH:mm');
	}
	

	models.FeedItem.prototype.getUntil = function() {
		var m = moment(this.dtstart);
		return prettydate.prettyUntil(m);
	}




	return models;


});