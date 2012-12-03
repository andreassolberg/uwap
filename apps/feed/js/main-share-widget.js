/*
 * This is the main js app for the iframe content of the "share this" widget.
 * 
 */


define(function(require, exports, module) {

	var 

		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		jso = require('uwap-core/js/oauth'),

		moment = require('uwap-core/js/moment'),
		prettydate = require('uwap-core/js/pretty')
		;

	require('uwap-core/js/jquery.tmpl');

	require('uwap-core/bootstrap/js/bootstrap');
	require('uwap-core/bootstrap/js/bootstrap-collapse');
	require('uwap-core/bootstrap/js/bootstrap-button');
	require('uwap-core/bootstrap/js/bootstrap-dropdown');


	console.log("Share iframe is running code.....")


	var App = function(el, user) {
		var that = this;

		this.el = el;
		this.user = user;

		this.currentData = null;

		this.el.on("click", ".actPost", $.proxy(this.postBox, this));
		this.setgroups();

		window.addEventListener("message", $.proxy(this.receiveMessage, this), false);
		// window.addEventListener("message", $.proxy(this.receiveMessage, this), false);
		 
	}
	App.prototype.receiveMessage = function(event) {
		// console.log("Receives message", event);
		this.currentData = event.data;
		this.currentData.user = this.user;
		this.currentData.user.profileurl = UWAP.utils.getEngineURL('/api/media/user/' + this.currentData.user.a);

		console.log("POOT", event.data);
		console.log("sharetmpl", this.currentData);
		var sharetmpl = $("#shareTmpl").tmpl(this.currentData);
		$("div#post").empty().append(sharetmpl);
		console.log("sharetmpl", sharetmpl);

		// if (event.origin !== "http://example.org:8080")
		//   return;
	};
	App.prototype.setgroups = function() {
		var groups = this.user.groups;
		console.log("groups", groups);
		var groupscontainer = $("div.groups").empty();
		var select = $('<select id="groups"></select>').appendTo(groupscontainer);

		$.each(groups, function(i, item) {

			select.append('<option value="' + i + '">' + item + '</option>');
			// $("div.groups").append('<label class="checkbox inline"><input type="checkbox" id="grp_' + i + '" value="' + i + '">' + item + '</label>');
			// $("ul#navfilter").append('<li><a id="entr_' + i + '" href="#"><span class="icon icon-folder-open"></span> ' + item + '</a></li>');
		});

	}
	App.prototype.postBox = function() {

		console.log("PostBox()")

		var str = $("#fieldMessage").val();
		var msg = this.currentData;
		delete msg.user;
		var groups;
		
		// $("div.groups input:checked").each(function(i, item) {
		// 	groups.push($(item).attr('value'));
		// });

		groups = [$("select#groups").val()];

		msg['class'] = ['activity'];
		msg['groups'] = groups;
		msg.activity.object.content = str;

		console.log("Pushing obj", msg); 
		// $("#fieldMessage").val("").focus();
		this.post(msg);
		
	};
	App.prototype.post = function(msg) {
		var that = this;
		UWAP.feed.post(msg, function() {
			$("#share").empty().append('<p>Thanks for sharing...</p><p><a target="_blank" href="https://feed.uwap.org">View your post on uwap.org</a></p>');
		});
	}







	$("document").ready(function() {

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



		UWAP.auth.checkPassive(function(user) {

			$("#share-widget-main").show();
			console.log("LOADING APP WITH USER", user);
			var app = new App($("body"), user);
			// app.setauth(user);

		}, function() {
			$('#notauthorized').show();
			$('#notauthorized').on('click', 'button', function(e) {
				e.preventDefault();
				authpopup(function() {

					UWAP.auth.checkPassive(function(user) {

						$('#notauthorized').hide();
						$("#share-widget-main").show();

						var app = new App($("body"), user)
						console.log("LOADING APP WITH USER", user);
						// app.setauth(user);

					});

				});

			});
		});



	});


});

