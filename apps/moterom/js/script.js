/*
 * JavaScript Pretty Date
 * Copyright (c) 2011 John Resig (ejohn.org)
 * Licensed under the MIT and GPL licenses.
 */

// Takes an ISO time and returns a string representing how
// long ago the date represents.
function prettyDate(time){
	var date = new Date(time),
		diff = (((new Date()).getTime() - date.getTime()) / 1000),
		day_diff = Math.floor(diff / 86400);

	if ( isNaN(day_diff) || day_diff < 0 ) return;

	return day_diff == 0 && (
			diff < 60 && "just now" ||
			diff < 120 && "1 minute ago" ||
			diff < 3600 && Math.floor( diff / 60 ) + " minutes ago" ||
			diff < 7200 && "1 hour ago" ||
			diff < 86400 && Math.floor( diff / 3600 ) + " hours ago") ||
		day_diff == 1 && "Yesterday" ||
		day_diff < 7 && day_diff + " days ago" ||
		day_diff < 31 && Math.ceil( day_diff / 7 ) + " weeks ago";
}


function prettyInterval(diff){

	var 
		day_diff = Math.floor(diff / 86400);

	if ( isNaN(day_diff) || day_diff < 0 ) return;

	return day_diff == 0 && (
			diff < 60 && "under ett minutt" ||
			diff < 120 && "ett minutt" ||
			diff < 3600 && Math.floor( diff / 60 ) + " minutter" ||
			diff < 7200 && "en time " + Math.floor( (diff - 3600*Math.floor( diff / 3600 ))/60) + ' minutter ' ||
			diff < 86400 && Math.floor( diff / 3600 ) + " timer " + Math.floor( (diff - 3600*Math.floor( diff / 3600 ))/60) + ' minutter ') ||
		day_diff == 1 && "en dag" ||
		day_diff < 7 && day_diff + " dager" ||
		day_diff < 31 && Math.ceil( day_diff / 7 ) + " uker";
}







moment.fn.hdur = function() {
	var udur = Math.abs(moment().unix() - this.unix());
	return prettyInterval(udur);
}
moment.lang('no');

var Room = Spine.Class.sub({
	showAvail: function(avail) {
		$.each(avail, function(i, item) {
			console.log('From     -> ' + item[0].format('HH:mm') + ' - ' + item[1].format('HH:mm'));
		});
	},
	init: function(o) {
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
	},
	nextAvailable: function() {
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
	},
	isAvailableToday: function() {
		var na = this.nextAvailable();
		if (na === false) return 'resten av dagen';
		return na.hdur();
	},
	isAvailable: function() {
		var i;
		for(i = 0; i < this.events.length; i++) {
			if (this.events[i].isNow()) return false;
		} 
		return true;
	},
	isAvailableForHowLong: function() {
		var n;
		n = this.getNextEvents();

		if (n.length === 0) return 'resten av dagen';
		return n[0].start.hdur();
	},
	isAvailableFor: function(dur) {
		var n;
		dur = dur || 15;
		n = this.getNextEvents();

		if (n.length === 0) return true;
		if (n[0].start > moment().add('minutes', dur)) return true;
		return false;
	},
	getMore: function() {
		return this.description + ' ' + this.specification;
	},

	addEvent: function(ev) {
		this.events.push(new Event(ev));
	},
	getCurrentEvents: function() {
		var cur = [];
		$.each(this.events, function(i, event) {
			if (event.isNow()) {
				cur.push(event);
			}
		});
		return cur;
	},
	getNextEvents: function() {
		var cur = [];
		$.each(this.events, function(i, event) {
			if (event.isNext()) {
				cur.push(event);
			}
		});
		return cur;
	}
});


function dparse(str) {
	var nstr = '';
	nstr += str.substring(0, 10) + 'T';
	nstr += str.substring(11);
	// console.log('string became: ' + nstr);
	return moment(nstr);
}


var Event = Spine.Class.sub({
	init: function(o) {
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
	},
	isNow: function() {
		// console.log("Compare", this.start, this.end,  moment());
		return (this.start <= moment() && this.end >= moment());
	}, 
	isNext: function() {
		return (this.start > moment() && this.end > moment());
	},
	getClass: function() {
		var cls = [];
		if (this.isNow()) cls.push('now');
		if (this.isNext()) cls.push('next');
		return cls.join(' ');
	},
	getHdr: function() {
		if (!this.summary && !this.description) {
			return 'NA';
		}
		return (this.summary || '') + ' ' + (this.description || '');
	},
	getTime: function() {
 		return this.start.format('HH:mm') + '-' + this.end.format('HH:mm') 
		return 'time';
	},
	remaining: function() {
		if (this.isNow()) {
			return this.end.fromNow(true);;
		} else if(this.isNext()) {
			return this.start.fromNow();
		} else {
			return 'har vært tidligere';
		}
	},
	availability: function(type) {
		if (type === 'busy') {
			return true;
		} else if(type === 'available') {
			return false;
		} else {
			return false;	
		}
		
	},
	getPerson: function() {
		if (!this.firstname || !this.lastname) return 'NA';
		return this.firstname + ' ' + this.lastname;
	}
});


var MRController = Spine.Class.sub({
	init: function(el) {
		var that = this;
		this.el = el;

		this.user = null;
		this.device = null;

		this.mainRoom = null;
		this.loaded = false;

		if (location.hash) {
			
			this.mainRoom = location.hash.substring(1);
			console.log("Location", this.mainRoom);

		};

		UWAP.auth.checkPassive(
			this.proxy(this.processLoggedIn), 
			this.proxy(this.processNotLoggedIn)
		);

		// this.load();

		// setInterval(this.proxy(this.updateHeight), 3000);

		$(this.el).on("click", "div.roomcontainer", function(event) {
			// var that = this;
			var rc = $(event.currentTarget);
			var room = rc.tmplItem().data;
			console.log("Click on ", room);

			that.setActiveRoom(room.name);
			

		});
		$(this.el).on("click", "button.register", function(event) {
			// console.log(event.currentTarget); return false;
			var thatbutton = this;
			var rc = $(event.currentTarget).closest("div.roomcontainer");
			var room = rc.tmplItem().data;
			console.log("REGISTER MEETING ON ", room);

			var element = $("#regTmpl").tmpl();
			var times = [15, 30, 45, 60];


			// console.log("times", times, 'slots', timeslots, 'ranges', ranges);

			element.on("click", "div.selecthours button", function(event) {
				var minutes = parseInt(moment().format('m'), 10);
				var timeslots = times.map(function(i) { return Math.ceil((minutes - 2 + i)/15)*15-minutes; });
				var ranges = timeslots.map(function(i) { return [moment().format('YYYY-MM-DD HH:mm'), moment().add('minutes', i).format('YYYY-MM-DD HH:mm')]; } );
				var rangesH = timeslots.map(function(i) { return [moment().format('HH:mm'), moment().add('minutes', i).format('HH:mm')]; } );

				var minutes = $(event.currentTarget).data('minutes');
				console.log("selected", minutes);

				console.log("Regster meeting from ", rangesH[minutes]);
				var reservation = {
					title: "Ad-hoc meeting reservation",
					room: room.id,
					from: ranges[minutes][0],
					to: ranges[minutes][1]
				};
				console.log("RESERVATION: ", reservation);
				var url = 'https://foodle.feide.no/reserve';
				var opts = {method: "POST", data: reservation};

				console.log(that);
				if (that.user) {
					opts.handler = 'cal';
				} else if (that.device) {
					url += '?key=' + that.device.key;
				} else {
					alert("Unexpected error: not locally authorized to register.");
				}

				UWAP.data.get(url, opts, function(response) {
					console.log("Successfully registered meeting");
					that.load();
				});

				element.modal('hide');
				element.remove();
			});
			
			$("div#modalContainer").append(element);
			element.modal('show');
			return false;
		});

		// setTimeout(function() {
		// 	$(this.setActiveRoom(that.mainRoom));
		// }, 30000);
	},
	uuid: function() {
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
		    var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
		    return v.toString(16);
		});
	},
	processLoggedIn: function(user) {
		this.user = user;
		console.log("Logged in");
		var el = $("#authTmpl").tmpl(user);
		$("body").prepend(el);

		this.load();
	},
	processNotLoggedIn: function() {
		console.log("Not logged in");
		var device = localStorage.getItem('device');

		if (device) {
			this.device = JSON.parse(device);
			if (this.device.room) {
				this.mainRoom = this.device.room;
			}
			this.showDeviceInfo(this.device);
			this.load();
		} else {
			this.showAuthDialog();
		}
	},
	showDeviceInfo: function(device) {
		var el = $("#deviceInfoTmpl").tmpl(device);
		var that = this;
		el.on("click", "button.unregister", function() {
			el.remove();
			that.device = null;
			that.showAuthDialog();
		});
		$("body").append(el);
	},
	showAuthDialog: function() {
		var that = this;
		var el = $("#authDialogTmpl").tmpl();

		el.on("click", "button.login", function() {
			UWAP.auth.require(function() {});
		});
		el.on("click", "button.devicereg", function(event) {
			var devicename = $(event.currentTarget).closest("form").find("input#devicename").val();
			var deviceid = that.uuid();
			var devicekey  = $(event.currentTarget).closest("form").find("input#devicekey").val();
			var deviceroom = $(event.currentTarget).closest("form").find("select#deviceroom").val();
			UWAP.data.get("https://foodle.feide.no/register?key=" + devicekey, {}, function(data) {
				if (data && data.ok === true) {
					el.modal('hide').remove();
					that.device = {
						name: devicename, key: devicekey, id: deviceid, room: deviceroom
					}
					that.showDeviceInfo(that.device);
					that.mainRoom = that.device.room;
					console.log("device", that.device);
					that.load();
					localStorage.setItem('device', JSON.stringify(that.device));
				} else {
					alert("Invalid device key, please try again");
				}
			}, function(err) {
				alert("Invalid device key, please try again");
			});
			return false;
		});
		$("div#modalContainer").append(el.modal("show"));
	},
	setActiveRoom: function(id) {
		console.log("Set active room " + id);
		$(this.el).find("div.roomcontainer").removeClass("activeRoom");
		$(this.el).find("div.roomcontainer.roomtype-" + id).addClass("activeRoom");
		this.updateHeight();
	},
	load: function() {
		console.log("Loading data");
		var that = this;
		var date = moment()
			// .add("days", 1)
			.format('YYYY-MM-DD');
		var url = 'https://foodle.feide.no/meetingroom/' + date;
		var opts = {};

		if (this.user) {
			opts.handler = 'cal';
		} else if(this.device) {
			url += '?key=' + this.device.key;
		}
		UWAP.data.get(url, opts, this.proxy(this.updateData));

		if (!this.loaded) {
			that.loaded = true;
			setInterval(that.proxy(that.load), 30000);
		}
	},
	updateHeight: function () {
		return;
		// $("div.room").equalHeights();
		$(this).find("div.room").css("height", "inherit");
		$(this.el).find("div.etg").each(function(i, item) {
			console.log("On each ", this);
			$(this).find("div.room").equalHeights();
		});
	},
	updateData: function(data) {
		var roomdef = {
			'gra': {

			},
			'orange': {

			},
			'lilla': {

			},
			'sort': {

			},
			'rod': {

			},
			'gul': {

			},
			'gronn': {

			}
		};

		var etg = {
			'6': ['gul', 'sort'],
			'5': ['rod', null],
			'4': ['lilla', 'orange'],
			'3': ['gronn', 'gra']
		};

		$.each(data, function(i, roomobj) {
			var el = $('<div></div>');
			
			if (!roomdef[roomobj.name]) {
				// console.log("Skipping " + roomobj.name);
				return;
			}
			roomdef[roomobj.name].room = new Room(roomobj, roomobj.events);
		});

		var e, i, el, r;
		for(e in etg) {
			$("div.etg" + e).empty().append('<div class="span2 etghdr">' + e + '. etg</div>');
			for(i = 0; i < etg[e].length; i++) {
				if (etg[e][i] === null) {
					el = $('<div class="room empty span5">&nbsp;</div>');
				} else {
					r = roomdef[etg[e][i]].room;
					el = $("#roomTmpl").tmpl(r);
					el.find(".roomEvents").append($("#nowTmpl").tmpl(r));

					var now = r.getCurrentEvents();
					var nexts = r.getNextEvents();
			
					el.find(".roomEvents").append($("#eventTmpl").tmpl(now));
					el.find(".roomEvents").append($("#eventTmpl").tmpl(nexts));

					if (r.isAvailable()) {
						el.addClass("available");
					}
				}
				$("div.etg" + e).append(el);
			}
			
		}
		this.updateHeight();

		$("div.room.empty").first().empty().append('<p class="dateclock"><span class="clock">' + moment().format('HH:mm') + '</span> - <span class="date">' + moment().format('dddd, D. MMMM') + '</span></p>');

		if (this.mainRoom) {
			this.setActiveRoom(this.mainRoom);
		}
	}
});


$(document).ready(function() {
	var m = new MRController($("div#main"));
});


