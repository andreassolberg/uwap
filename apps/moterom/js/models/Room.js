define(function(require, exports, module) {
	var moment = require('uwap-core/js/moment');
	var Event = require('models/Event');
	
	
	var Room = function(o) {
		var 
			i, 
			fields = ['id', 'class', 'comment', 'created', 'description', 'name', 'short', 'specification'],
			that = this;

		this.events = [];
		// console.log("Contructing room with ", o);
		for(i in fields) {
			// console.log("Checking property ", fields[i]);
			if (o.hasOwnProperty(fields[i])) {
				this[fields[i]] = o[fields[i]];
			}
		}
		if (o.events) {
			
			$.each(o.events, function(i, ev) {
				that.addEvent(ev);
			})
		}
		// console.log("Availability for room " + that.short);
		// console.log(this.events);
		this.availability = [[moment().sod(), moment().eod()]];
		$.each(this.events, function(i, event) {
			var 
				na = [],
				processed = true;

			$.each(that.availability, function(i, avail) {
				if (event.start <= avail[0] && event.end >= avail[1]) {
					// This entire available slot is not available any more.
					processed = true;
					// console.log('Availability :: This entire available slot is not available any more');
				} else if (event.start >= avail[0] && event.end <= avail[1] ) {
					// this entire event is within the available slot. Split available slot into two.
					// console.log('Availability :: entire event is within the available slot', event.start, event.end);

					if (event.start > avail[0]) na.push([avail[0], event.start]);
					if (avail[1] > event.end) na.push([event.end, avail[1]]);
					processed = true;
				} else if (event.start < avail[0] && event.end > avail[0] && event.end < avail[1]) {
					// only the end of the vent overlaps with the avail period.
					// console.log('Availability :: only the end of the vent overlaps with the avail period');
					na.push([event.end, avail[1]]);
					processed = true;
				} else if (event.start > avail[0] && avail.start < avail[1] && event.end > avail[1]) {
					// only the start of the event overlaps with the avail period.
					// console.log('Availability :: only the start of the vent overlaps with the avail period');
					na.push([avail[0], event.start]);
					processed = true;
				} else {
					// The event does not match the avail
					// console.log("DID NOT MATCH");
					na.push(avail);
				}
			});
			
			// console.log("After processing event " + event.getHdr() + ' (' + event.start.format('HH:mm') + '-' + event.end.format('HH:mm') + ')');
			// that.showAvail(na);
			that.availability = na;
		});
		// that.showAvail(this.availability);
		// console.log(' -------- ');
	};
	Room.prototype.showAvail = function(avail) {
		$.each(avail, function(i, item) {
			console.log('From     -> ' + item[0].format('HH:mm') + ' - ' + item[1].format('HH:mm'));
		});
	};
	Room.prototype.nextAvailable = function() {
		var current = moment(),
			i,
			a;
		if (this.availability.length === 0) return false;
		for(i = 0; i < this.availability.length; i++) {
			a = this.availability[i];
			if (a[0] > current) {
				// console.log("NextAvailable is ", a[0]);
				return a[0];
			} else {
				// console.log("NextAvailable is NOT ", a[0]);
			}
		}
		// console.log("Not available any more today", this.availability);
		return false;
	};
	Room.prototype.isAvailableToday = function() {
		var na = this.nextAvailable();
		if (na === false) return 'resten av dagen';
		return na.hdur();
	};
	Room.prototype.isAvailable = function() {
		var i;
		for(i = 0; i < this.events.length; i++) {
			if (this.events[i].isNow()) return false;
		} 
		return true;
	};
	Room.prototype.isAvailableForHowLong = function() {
		var n;
		n = this.getNextEvents();

		if (n.length === 0) return 'resten av dagen';
		return n[0].start.hdur();
	};
	Room.prototype.isAvailableFor = function(dur) {
		var n;
		dur = dur || 15;
		n = this.getNextEvents();

		if (n.length === 0) return true;
		if (n[0].start > moment().add('minutes', dur)) return true;
		return false;
	};
	Room.prototype.getMore = function() {
		return this.description + ' ' + this.specification;
	};

	Room.prototype.addEvent = function(ev) {
		this.events.push(new Event(ev));
	};
	Room.prototype.getCurrentEvents = function() {
		var cur = [];
		$.each(this.events, function(i, event) {
			if (event.isNow()) {
				cur.push(event);
			}
		});
		return cur;
	};
	Room.prototype.getNextEvents = function() {
		var cur = [];
		$.each(this.events, function(i, event) {
			if (event.isNext()) {
				cur.push(event);
			}
		});
		return cur;
	};

	return Room;
});