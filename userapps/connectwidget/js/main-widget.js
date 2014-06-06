define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),

		hb = require('uwap-core/js/handlebars')
		;

	
	require('uwap-core/js/jquery.tmpl');

	require('uwap-core/bootstrap3/js/bootstrap');	
	


	var groupsTmpl = hb.compile(require('uwap-core/js/text!templates/groups.html'));
	var highlightTempl = hb.compile(require('uwap-core/js/text!templates/highlight.html'));
	var participantsTmpl = hb.compile(require('uwap-core/js/text!templates/participants.html'));


	// UWAP.data.soa('http://connectapi.app.bridge.uninett.no/connect/version', {}, function(data) {
	// 	console.log("DATA version", data);
	// });

	// UWAP.data.soa('http://connectapi.app.bridge.uninett.no/debug', {}, function(data) {
	// 	console.log("DATA debug", data);
	// });



	var App = function(el) {
		var that = this;
		this.el = el;

		this.user = null;
		this.groups = {};

		this.participantlistReady = false;

		this.highlighted = null;

		this.el.on('click', '.selectRoom', $.proxy(this.selectRoom, this));

		var that = this;


		$(window).on("message", function(e) {
			var data = e.originalEvent.data;  // Should work.
			console.log("Received message", data);

			if(data.action === 'setContext') {
				if (data.context.group) {
					console.log("About to sethighlightroom ", data.context);
					that.setHighlightRoom(data.context.group);	
				} else {
					that.setHighlightRoom(null);
				}
				
			}

		});

		// setTimeout(function() {
		// 	console.log("Setting highlighted");
		// 	that.setHighlightRoom('5e0daec3-5b71-4e8a-8d5d-3a9894ff00d9');
		// }, 1);

	}

	App.prototype.setHighlightRoom = function(groupid) {


		var changed = (this.highlighted !== groupid);
		this.highlighted = groupid;

		if (groupid === null) {
			$("#content").show();
		} else {
			$("#content").hide();
		}


		if (this.participantlistReady && changed) {
			console.log("setHighlightRoom: CHANGE")
			this.updateOnline();
		} else if (changed) {
			console.log("setHighlightRoom: CHANGE but list not ready");
		}


	}


	App.prototype.selectRoom = function(e) {

		var room = $(e.currentTarget).data('groupid');
		var target = $(e.currentTarget);

		if (target.hasClass("disabled")) {
			console.log("Not reacting to selection.");
			return;
		}

		// target.prop('disabled', true);
		target.addClass("disabled");
		setTimeout(function() {
			target.removeClass("disabled");
		}, 5000);

		console.log("Selecting room " + room);
		console.log("Group data ", this.groups[room]);

		// return;

		// UWAP.data.soa('http://connectapi.app.bridge.uninett.no/connect/' + room + '/participants', {}, function(data) {
		// 	console.log("participants", data);

		// 	$("div#result").empty().append(participantsTmpl(data));
		// 	// $("div#result").find('#joinform').submit();

		// });
		
		
		/*
		 * To prevent popup blocker warning, we need to open a window right away as a response
		 * to the action of the user clicking on a button.
		 * The URL will be changed later on...
		 */
		var wo = window.open('join-meeting-idle.html');


		UWAP.data.soa('http://connectapi.app.bridge.uninett.no/connect/connect', 
			{
				"_method": "post",
				"_data": {
					"groupid": room,
					"name": this.groups[room].displayName
				}
			}, 
			function(data) {

				console.log("Join meeting response", data);


				var msg = {
					"loginurl": data.meeting.loginurl,
					"target": data.meeting.target
				};

				wo.location.href = 'join-meeting.html?msg=' + encodeURIComponent(JSON.stringify(msg));
			}

		);


	}

	App.prototype.updateOnline = function () {
		var that = this;
		console.log("About to updateOnline()");
		UWAP.data.soa('http://connectapi.app.bridge.uninett.no/connect/online', {}, function(data) {
			console.log(" =====> Online", data);

			if (data instanceof UWAP.Error) {
				// alert("Error");
				console.error("Data error", data);
				return;
			}

			that.participantlistReady = true;

			$("#onlinemeetings").empty();

			if ((that.highlighted !== null) && (data[that.highlighted] === null)) {
			
				var ginfo = {
						'groupid': that.highlighted,
						'groupname': that.groups[that.highlighted].displayName
					};
				console.log("group info", ginfo);
				$("#onlinemeetings").append(
					highlightTempl(ginfo)
				);
			}

			for(var groupid in data) {
				if (data[groupid] !== null) {
					var obj = {
						"groupid": groupid,
						"groupname": that.groups[groupid].displayName,
						"participants": data[groupid],
						"nop": data[groupid].length,
						"multiple": data[groupid].length > 1
					}
					console.log("Online meeting", obj);
					$("#onlinemeetings").append(participantsTmpl(obj));
				}
			}
			that.updateHeight();

		});

		
	}


	App.prototype.setauth = function(user, groups) {
		this.user = user;
		this.groups = {};

		for(var i = 0; i < groups.Resources.length; i++) {
			this.groups[groups.Resources[i].id] = groups.Resources[i];
		}

		console.log(user);

		console.log("Apply group templates with ", this.user)

		$("#content").append(groupsTmpl(this.groups));
		this.updateHeight();

		// console.log("Authenticated", user);

		this.updateOnline();
		setInterval($.proxy(this.updateOnline, this), 25000);

		

		// console.error('setauth');

		// this.postcontroller.setgroups(user.groups);
		// this.groupcontrollerbar.setgroups(user.groups);
		// this.mainnewsfeed.setgroups(user.groups);
		// this.mainnewsfeed.setuser(user);
	}

	App.prototype.updateHeight = function() {
		console.log("Child iframe is updating height. sending to parent.")
		parent.postMessage({
			"action": "setSize",
			"size": $('body').height(),
			"extra": $('.dropdown-menu').height()
		}, "*");
	}


	App.prototype.getUser = function () {
		return this.user;
	}
	App.prototype.getGroups = function () {
		return this.groups;
	}


	function authpopup(callback) {
		var url = UWAP.utils.getAppURL('/auth.html');
		newwindow=window.open(url,'uwap-auth','height=600,width=800');
		if (window.focus) {newwindow.focus()};

		var timer = setInterval(function() {   
		    if(newwindow.closed) {  
		        clearInterval(timer);  
		        callback();
		    }  
		}, 1000);

		return false;
	}


	$("document").ready(function() {
		
		console.log("About to checkpassive");
		UWAP.auth.checkPassive(function(user) {

			console.log("Check passive success", user);

			UWAP.groups.listMyGroups(function(groups) {

				console.log("Got groups", groups);
				var app = new App($("body"))
				app.setauth(user, groups);
			});

		}, function() {

			console.log("Check passive Failed");

			$('#notauthorized').show();
			$('#notauthorized').on('click', 'button', function(e) {
				e.preventDefault();
				authpopup(function() {

					UWAP.auth.checkPassive(function(user) {

						$('#notauthorized').hide();

						UWAP.groups.listMyGroups(function(groups) {
							var app = new App($("body"))
							app.setauth(user, groups);
						});

					});

				});

				// UWAP.auth.require(function(user) {
				// 	var app = new App($("body"))
				// 	app.setauth(user);
				// });

			});
		});



	});

});

