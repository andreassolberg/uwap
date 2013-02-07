define(function(require, exports, module) {
	var 
	$ = require('jquery'),
	UWAP = require('uwap-core/js/core');
	var moment = require('uwap-core/js/moment');
	var Room = require('models/Room');
	var hogan = require('uwap-core/js/hogan');

	require('uwap-core/bootstrap/js/bootstrap');	
	
	require('uwap-core/bootstrap/js/bootstrap-modal');
	require('uwap-core/bootstrap/js/bootstrap-collapse');
	require('uwap-core/bootstrap/js/bootstrap-button');
	require('uwap-core/bootstrap/js/bootstrap-dropdown');
	
	var tmpl = {
		"authDialog": require('uwap-core/js/text!templates/authDialogTmpl.html'),
		"auth": require('uwap-core/js/text!templates/authTmpl.html'),
		"deviceInfo":  require('uwap-core/js/text!templates/deviceInfoTmpl.html'),
		"event": require('uwap-core/js/text!templates/eventTmpl.html'),
		"nowDanger": require('uwap-core/js/text!templates/nowDangerTmpl.html'),
		"nowSuccess": require('uwap-core/js/text!templates/nowSuccessTmpl.html'),
		"nowWarning": require('uwap-core/js/text!templates/nowWarningTmpl.html'),
		"reg": require('uwap-core/js/text!templates/regTmpl.html'),
		"room": require('uwap-core/js/text!templates/roomTmpl.html')
	};




	// ---------- o ---------- o ---------- o ---------- o ---------- o 
	// Fix for locking modal.
	// http://stackoverflow.com/questions/13421750/twitter-bootstrap-jquery-how-to-temporarily-prevent-the-modal-from-being-clo
	// 
	// save the original function object
	var _superModal = $.fn.modal;

	// add locked as a new option
	$.extend( _superModal.defaults, {
	    locked: false
	});

	// create a new constructor
	var Modal = function(element, options) {
	    _superModal.Constructor.apply( this, arguments )
	}

	// extend prototype and add a super function
	Modal.prototype = $.extend({}, _superModal.Constructor.prototype, {
	    constructor: Modal

	    , _super: function() {
	        var args = $.makeArray(arguments)
	        // call bootstrap core
	        _superModal.Constructor.prototype[args.shift()].apply(this, args)
	    }

	    , lock : function() {
	        this.options.locked = true
	    }

	    , unlock : function() {
	        this.options.locked = false
	    }

	    , hide: function() {
	        if (this.options.locked) return
	        this._super('hide')
	    }
	});

	// override the old initialization with the new constructor
	$.fn.modal = $.extend(function(option) {
	    var args = $.makeArray(arguments),
	    option = args.shift()

	    // this is executed everytime element.modal() is called
	    return this.each(function() {
	        var $this = $(this)
	        var data = $this.data('modal'),
	            options = $.extend({}, _superModal.defaults, $this.data(), typeof option == 'object' && option)

	        if (!data) {
	            $this.data('modal', (data = new Modal(this, options)))
	        }
	        if (typeof option == 'string') {
	            data[option].apply( data, args )
	        }
	    });
	}, $.fn.modal);
	// ---------- o ---------- o ---------- o ---------- o ---------- o 




	var MRController =  function(el) {
			var that = this;
			this.el = el;

			this.user = null;
			this.device = null;

			this.mainRoom = null;
			this.loaded = false;
			
			this.roomArray = {};
			this.roomConfig = null;
			this.data = null;

			this.templates = {
					"authDialog": hogan.compile(tmpl.authDialog),
					"auth": hogan.compile(tmpl.auth),
					"deviceInfo": hogan.compile(tmpl.deviceInfo),
					"event": hogan.compile(tmpl.event),
					"nowSuccess": hogan.compile(tmpl.nowSuccess),
					"nowWarning": hogan.compile(tmpl.nowWarning),
					"nowDanger": hogan.compile(tmpl.nowDanger),
					"reg": hogan.compile(tmpl.reg),
					"room": hogan.compile(tmpl.room)
				};
			
			if (location.hash) {
				
				this.mainRoom = location.hash.substring(1);
				console.log("Location", this.mainRoom);

			};

//			UWAP.auth.checkPassive(
////					
////					function(){}, function(){}
//				$.proxy(this.processNotLoggedIn, this), 
//				$.proxy(this.processNotLoggedIn, this)
//				
//			);
			
			UWAP.auth.check(
				$.proxy(this.processLoggedIn, this),
				$.proxy(this.processNotLoggedIn, this)
			);
//			this.processLoggedIn
			// this.load();

			// setInterval(this.proxy(this.updateHeight), 3000);

			$(this.el).on("click", "div.roomcontainer", function(event) {
				// var that = this;
				var rc = $(event.currentTarget);
				console.log(that.roomArray[rc.attr('room')]);
				
//				var room = rc.tmplItem().data;
				var room = that.roomArray[rc.attr('room')];
				console.log("Click on ", room);

				that.setActiveRoom(room.name);
				
			});
			$(this.el).on("click", "button.register", function(event) {
				// console.log(event.currentTarget); return false;
				var thatbutton = this;
				var rc = $(event.currentTarget).closest("div.roomcontainer");
//				var room = rc.tmplItem().data;
				var room = that.roomArray[rc.attr('room')];
				
				console.log("REGISTER MEETING ON ", room);

//				var element = $("#regTmpl").tmpl();
				var element = $( that.templates['reg'].render() );
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
					var url = 'http://moterom-api.uninett.no/reserve';
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
				console.log("About to show modal!")
				return false;
			});
			this.getRoomConfig();
			// setTimeout(function() {
			// 	$(this.setActiveRoom(that.mainRoom));
			// }, 30000);
		};
		
		MRController.prototype.updateStatus = function(){
			var that = this;
			console.log('Updating status');
			console.log(this.user);
			if (!(that.user == null)) {
//				UWAP.store.remove({"type": "meetUpdate"});
				UWAP.store.queryOne({
					"type" : "meetUpdate",
					"uwap-userid" : that.user.userid
				}, function(d) {
					console.log(d);
					if(!(d == null)){
						d.lastUpdate = moment().format('YYYY-MM-DD hh:mm:ss');
						d.device = that.device;
						UWAP.store.save(d, function(){console.log('status updated');}, function(err){ console.log(err);});
					}
					else{
						 UWAP.store.save({
						"type" : "meetUpdate",
						"uwap-acl-read" : "uwap:realm:uninett_no",
						"device" : that.device,
						"lastUpdate" : moment().format('YYYY-MM-DD hh:mm:ss')
					}, function() {
						console.log('Status updated');
					}, function(err) {
						console.log(err);
					});
					}
					
				}, function(err) {
					console.log('Could not retrieve status, because: ' + err);
				});
			}
			
			
			
		
		};
		
		MRController.prototype.updateInterval = function(){
			var that = this;
			setInterval($.proxy(that.updateStatus, that), 15000);
//			setInterval(this.updateStatus, 10000);
		};
		
		MRController.prototype.getRoomConfig = function(){
			var that = this;
			// UWAP.data.get('https://moterom2.uwap.org/config.json', null, function(d){ that.roomConfig = d; if(!(that.data == null)){that.updateData(this.data);}}, function(err){console.log(err);});
			$.getJSON('config.json', function(d) {
				that.roomConfig = d; 
				if(!(that.data == null)){
					that.updateData(this.data);
				}
			});
		};
		
		MRController.prototype.uuid = function() {
			return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			    var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
			    return v.toString(16);
			});
		};
		MRController.prototype.processLoggedIn = function(user) {
			this.user = user;
			console.log("Logged in");
//			var el = $("#authTmpl").tmpl(user);
			var el = $( this.templates['auth'].render(user) );
			$("body").prepend(el);

			this.load();
		};
		MRController.prototype.processNotLoggedIn = function(user) {
			var that = this;
			// this.user = user;
			// console.log(this.user);
			console.log("Not logged in");
			var device = localStorage.getItem('device');

			if (device) {
				this.device = JSON.parse(device);
				if (this.device.room) {
					this.mainRoom = this.device.room;
					this.setActiveRoom(this.device.room);
					this.updateInterval();
					console.log("device", this.device);
				}
				this.showDeviceInfo(this.device);
				this.load();
			} else {
				console.log("show auth diaglog")
				this.showAuthDialog();
			}
		};
		MRController.prototype.showDeviceInfo = function(device) {
//			var el = $("#deviceInfoTmpl").tmpl(device);
			var el = $( this.templates['deviceInfo'].render(device) );
			var that = this;
			el.on("click", "button.unregister", function() {
				el.remove();
				that.device = null;
				that.showAuthDialog();
			});
			$("body").append(el);
		};
		MRController.prototype.showAuthDialog = function() {
			var that = this;
			console.log("showAuthDialog");
			var el = $( this.templates['authDialog'].render() );

			$("#modalContainer").show();

			el.on("click", "button.login", function() {
				UWAP.auth.require(function() {});
			});
			el.on("click", "button.devicereg", function(event) {
				var devicename = $(event.currentTarget).closest("form").find("input#devicename").val();
				var deviceid = that.uuid();
				var devicekey  = $(event.currentTarget).closest("form").find("input#devicekey").val();
				var deviceroom = $(event.currentTarget).closest("form").find("select#deviceroom").val();
				UWAP.data.get("http://moterom-api.uninett.no/register?key=" + devicekey, {}, function(data) {
					console.log(data);
					if (data && data.ok === true) {
						el.modal('hide').remove();
						that.device = {
							name: devicename, key: devicekey, id: deviceid, room: deviceroom
						}
						that.showDeviceInfo(that.device);
						that.mainRoom = that.device.room;
						that.setActiveRoom(that.device.room);
						that.updateInterval();
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
			$("div#modalContainer").append(el.modal({locked: true}).show());
		};
		
		MRController.prototype.setActiveRoom = function(id) {
			console.log("Set active room " + id);
			window.location.hash = '#/room/'+id;
			$(this.el).find("div.roomcontainer").removeClass("activeRoom");
			$(this.el).find("div.roomcontainer.roomtype-" + id).addClass("activeRoom");
			this.updateHeight();
		};
		MRController.prototype.load = function() {
			console.log("Loading data");
			var that = this;
			var date = moment()
				// .add("days", 1)
				.format('YYYY-MM-DD');
			var url = 'http://moterom-api.uninett.no/meetingroom/' + date;
			var opts = {};

			if (this.user) {
				opts.handler = 'cal';
			} else if(this.device) {
				console.log('Device: '+this.device);
				this.updateInterval();
				url += '?key=' + this.device.key;
			}
			UWAP.data.get(url, opts, $.proxy(this.updateData, this));

			if (!this.loaded) {
				that.loaded = true;
				setInterval($.proxy(that.load, that), 30000);
			}
		};
		MRController.prototype.updateHeight = function () {
			return;
			$(this).find("div.room").css("height", "inherit");
			$(this.el).find("div.etg").each(function(i, item) {
				console.log("On each ", this);
				$(this).find("div.room").equalHeights();
			});
		};
		
		MRController.prototype.updateData = function(data) {
			if(data==null){
				return;
			}
			this.data = data;
			var that = this;			
			if(that.roomConfig == null){
				console.log('roomConfig not set');
				setTimeout(this.updateData(data), 400);
				return false;
			}
			console.log(this.roomConfig);
			var config = this.roomConfig[0];
			console.log(config);
			var roomdef = config.rooms;
			var etg = config.etg;
			console.log(roomdef);
			
			console.log(data);
			$.each(data, function(i, roomobj) {
				var el = $('<div></div>');
				
				if (!roomdef[roomobj.name]) {
					// console.log("Skipping " + roomobj.name);
					return;
				}
				roomdef[roomobj.name].room = new Room(roomobj, roomobj.events);
			});

			var e, i, el, r;

			console.log("========= o ========= o ========= o ========= o ========= o ");
			console.log(etg);
			
			for(e in etg) {
				$("div.etg" + e).empty();
				// $("div.etg" + e).empty().append('<div class="span2 etghdr">' + e + '. etg</div>');

				if (etg[e].length == 3) {
					// $("div.etg" + e).append('<div class="room empty span1">&nbsp;</div>');
				}

				for(i = 0; i < etg[e].length; i++) {
					
					// if (etg[e][i] === null) {
					// 	if (etg[e].length === 1) {
					// 		el = $('<div class="room empty span10">&nbsp;</div>');
					// 	} else if(etg[e].length === 2) {
					// 		el = $('<div class="room empty span5">&nbsp;</div>');
					// 	} else if(etg[e].length === 3) {
					// 		el = $('<div class="room empty span5">&nbsp;</div>');
					// 	} else {
					// 		el = $('<div class="room empty span5">&nbsp;</div>');
					// 	}
						
					// } else {
						console.log(etg[e][i]);
						r = roomdef[etg[e][i]].room;
						console.log(r);
						console.log(r.isAvailable());
						this.roomArray[r.name] = r;
						var nowEl;
						if(r.isAvailable()){
							if(r.isAvailableFor(15)){
								//Success
								nowEl = $( this.templates['nowSuccess'].render(r) );
							}
							else{
								//Warning
								nowEl = $( this.templates['nowWarning'].render(r) );
							}
						}
						else{
							//Danger
							nowEl = $( this.templates['nowDanger'].render(r) );
						}

						if (etg[e].length === 3) {
							if (i === 2) {
								r.span = 4;
							} else {
								r.span = 4;
							}
							
						} else if (etg[e].length === 1) {
							r.span = 12;
						} else {
							r.span = 6;
						}
						r.etg = e;

						el = $( this.templates['room'].render(r) );
						el.attr('room', r.name);
						console.log(el);



						el.find(".roomEvents").append(nowEl);

						var now = r.getCurrentEvents();
						var nexts = r.getNextEvents();

						
						var nowIter;
						var nextsIter;
						$.each(now, function(i,v){
							nowIter = that.templates['event'].render(v);
							el.find(".roomEvents").append(nowIter);
						});
						$.each(nexts, function(i,v){
							nextsIter = that.templates['event'].render(v);
							el.find(".roomEvents").append(nextsIter);
							
						});


						if (r.isAvailable()) {
							el.addClass("available");
						}
					// }
					$("div.etg" + e).append(el);
				}
				
			}
			this.updateHeight();
			
			// $('<div class="room empty span5">&nbsp;</div>').appendTo('.etg5');
			$("div.clock").empty().append(
				'<p style="text-align: center" class="dateclock"><span class="clock">' + moment().format('HH:mm') + '</span>' +
				' - ' +
				'<span class="date">' + moment().format('dddd, D. MMMM') + '</span></p>');

			if (this.mainRoom) {
				this.setActiveRoom(this.mainRoom);
			}
			
			return true;
		};
	
	
	return MRController;
});