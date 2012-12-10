define(function(require, exports, module) {
	
	var
		moment = require('uwap-core/js/moment'),
		geo = require('libs/geo');

	/**
	 * Holds a single position
	 * @param {[type]} obj [description]
	 */
	var Position = function(obj) {
		if (obj.latitude) this.latitude = parseFloat(obj.latitude);
		if (obj.longitude) this.longitude = parseFloat(obj.longitude);
		if (obj.timestampMs) this.time = moment(parseInt(obj.timestampMs, 10));
		if (obj.accuracy) this.accuracy = parseFloat(obj.accuracy);


		var getDistance = function(l1, l2) {
			// console.log("Comparing", l1, l2);
			var p1 = new LatLon(l1.latitude, l1.longitude);
			var p2 = new LatLon(l2.latitude, l2.longitude);
			return p1.distanceTo(p2);
		};

		var getLocation = function(obj) {
			var i, loc, d;


			for(i = 0; i < Position.locations.length; i++) {
				Position.locations[i];

				d = getDistance(Position.locations[i], obj);
				// console.log("Checking locations of ", obj, "against. Distance was: ", d);
				if (Position.locations[i].title === 'Leistad Barnehage') {
					// console.log("Checking locations of ", Position.locations[i].title, "against. Distance was: ", d);
				}
				console.log()

				if (d < Position.locations[i].radius) {
					loc = jQuery.extend(true, {}, Position.locations[i]);
					loc.distance = d;
					return loc;
				}
			}
			return null;
		}

		this.location = getLocation(this);
	};
	Position.prototype.since = function() {
		return this.time.fromNow();
	}
	Position.prototype.when = function() {
		return this.time.format('HH:mm');
	}
	Position.locations = [];
	Position.init = function(l) {
		Position.locations = l;
	} 




	var compactTrackpoints = function(trackpoints) {
		var 
			i,
			current = null,
			candidate;

		locations = [];

		console.log("Trackpoints", trackpoints);
		
		for(i = 0; i < trackpoints.length; i++) {
			candidate = trackpoints[i];

			console.log("Processing " + candidate.location.title + ' ' + candidate.time.format('HH:mm'));
			// continue;

			if (current === null) {
				current = candidate;
				current.from = candidate.time;
				current.to = candidate.time;
				continue;
			}

			if (current.location.title !== candidate.location.title) {
				locations.push(current);

				current = candidate;
				current.from = candidate.time;
				current.to = candidate.time;
				continue;
			} else {
				// console.log("Candidate is the same ", current, candidate);
				if (candidate.time < current.from) {
					current.from = candidate.time;
				}
				if (candidate.time > current.to) {
					current.to = candidate.time;
				}
			}
		}
		if (current !== null) {
			locations.push(current);

		}
		return locations;

	};

	// PTracker.prototype.list = function() {
	// 	var i, l;
	// 	for(i = 0; i < this.locations.length; i++) {
	// 		l = this.locations[i];
	// 		console.log(l.location.title + ' ' + l.from.format('HH:mm') + '-' + l.to.format('HH:mm'));
	// 	}
	// };

	var Tracker = function(locations, container) {
		Position.init(locations);
		this.container = container;
		this.locations = [];
		this.load();
		setInterval($.proxy(this.load, this), 30000);
	};
	Tracker.locationPeriod = function(from, to) {
		if (from === to) {
			return from.format('HH:mm');
		} else {
			return from.format('HH:mm') + '-' + to.format('HH:mm');
		}
	};
	Tracker.locationDuration = function(from, to) {
		var dur;
		if (from === to) {
			return '';
		} else {
			dur = to - from;
			return ' (<span class="dur">' + moment.duration(dur).humanize() + '</span>)';
		}
	};
	Tracker.prototype.showData = function() {
		console.log("---- Date updated ----");
		$(this.container).empty();
		var i, el;
		for (i = 0; i < this.locations.length; i++) {
			el = $('<div class="location"></div>');
			var dur = this.locations[i].to- this.locations[i].from;

			if (i === 0) {
				el.addClass('firstLocation');
			}

			var cls = ['generic'];
			if (dur < 50*60*1000 && this.locations[i].location.radius > 10) {
				cls.push('transit');
			}
			el.addClass(cls.join(' '));
			var icon = '';
			if (this.locations[i].location.icon) {
				icon = 'icon-' + this.locations[i].location.icon;
			}

			el.append('<p class="title"><i style="position: relative; bottom: -3px" class="' + icon + '"></i> ' + this.locations[i].location.title + '</p>');
			el.append('<p class="time">' + Tracker.locationPeriod(this.locations[i].from, this.locations[i].to) +
				Tracker.locationDuration(this.locations[i].from, this.locations[i].to) + '</p>');

			$(this.container).append(el);;
		}
	};
	Tracker.prototype.load = function() {
		var that = this;
		var httpconfig = {
			handler: "latitude", 
			requestedScopes: [
				"https://www.googleapis.com/auth/latitude.all.best",
				"https://www.googleapis.com/auth/latitude.current.best"
			],
			requiredScopes: [
				"https://www.googleapis.com/auth/latitude.all.best"
			]
		};
		
		// UWAP.data.get('https://www.googleapis.com/latitude/v1/currentLocation?granularity=best', httpconfig, 
		// 	function(data) {
		// 		console.log("Latitude Data response Current");
		// 		console.log(data);
		// 		var l = new Latitude(data.data);
		// 		var loc = l.getLocation();
		// 		console.log("Location " + loc.title +  " at " + l.when() + " : " + loc.distance );
		// 	}
		// );

		var now = new Date().getTime();
		var begin = now - (20*3600*1000); // 20 hours back

		var url = 'https://www.googleapis.com/latitude/v1/location?';
		url += 'granularity=best';
		url += '&max-time=' + now;
		url += '&min-time=' + begin;
		url += '&max-results=1000';
		console.log('Accessing url ' + url);

		UWAP.data.get(url, httpconfig, 
			function(data) {
				console.log("Latitude Data response History");
				console.log(data);
				if (!data.data.items) return;

				var trackpoints = [];

				$.each(data.data.items, function(i, item) {
					var l = new Position(item);

					if (l.accuracy >= 200) {
						// console.log("Skipping " + loc.title + " because of bad accuracy.");
						return;
					}
					if (l.location) {
						trackpoints.push(l);
						console.log("Location " + l.location.title +  " at " + l.when() +  " ",
							l, " : " + l.location.distance + "   (" + l.accuracy + ")");
					} else {
						console.log("Location NA at " + l.when() + "  " );
					}
					
				});

				that.locations = compactTrackpoints(trackpoints);
				that.showData();

				// var tracker = new PTracker(trackpoints);
				// tracker.list();
			}
		);
	};

	return Tracker;

});





			



