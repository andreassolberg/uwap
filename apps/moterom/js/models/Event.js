define(function(require, exports, module) {
	var moment = require('uwap-core/js/moment');

	var Event = function(o) {
			var 
				i, 
				fields = ['id', 'owner', 'summary', 'description', 'room','firstname', 'lastname'];
			for(i in fields) {
				if (o.hasOwnProperty(fields[i])) {
					this[fields[i]] = o[fields[i]];
				}
			}
			this.start = dparse(o.dtstart);
			this.end = dparse(o.dtend);
		};
		Event.prototype.isNow = function() {
			// console.log("Compare", this.start, this.end,  moment());
			return (this.start <= moment() && this.end >= moment());
		}; 
		Event.prototype.isNext = function() {
			return (this.start > moment() && this.end > moment());
		};
		Event.prototype.getClass = function() {
			var cls = [];
			if (this.isNow()) cls.push('now');
			if (this.isNext()) cls.push('next');
			return cls.join(' ');
		};
		Event.prototype.getHdr = function() {
			if (!this.summary && !this.description) {
				return 'NA';
			}
			return (this.summary || '') + ' ' + (this.description || '');
		};
		Event.prototype.getTime = function() {
	 		return this.start.format('HH:mm') + '-' + this.end.format('HH:mm') 
			return 'time';
		};
		Event.prototype.remaining = function() {
			if (this.isNow()) {
				return this.end.fromNow(true);;
			} else if(this.isNext()) {
				return this.start.fromNow();
			} else {
				return 'har vært tidligere';
			}
		};
		Event.prototype.availability = function(type) {
			if (type === 'busy') {
				return true;
			} else if(type === 'available') {
				return false;
			} else {
				return false;	
			}
			
		};
		Event.prototype.getPerson = function() {
			if (!this.firstname || !this.lastname) return 'NA';
			return this.firstname + ' ' + this.lastname;
		};
		
		function dparse(str) {
			var nstr = '';
			nstr += str.substring(0, 10) + 'T';
			nstr += str.substring(11);
			// console.log('string became: ' + nstr);
			return moment(nstr);
		}
	
	
	return Event;
});