define([
	'./moment'
], function(moment) {

	$("document").ready(function() {


		var App = function(el, user) {
			var that = this;

			this.el = el;
			this.user = user;

			this.currentData = null;

			$("div#post").on("click", ".actPost", $.proxy(this.postBox, this));
			this.setgroups();


			window.addEventListener("message", $.proxy(this.receiveMessage, this), false);
			 
		}
		App.prototype.receiveMessage = function(event) {
			console.log("Receives message", event);
			this.currentData = event.data;
			this.currentData.user = this.user;

			console.log("sharetmpl", this.currentData);
			var sharetmpl = $("#shareTmpl").tmpl(this.currentData);
			$("div#post div#share").empty().append(sharetmpl);
			console.log("sharetmpl", sharetmpl);

			// if (event.origin !== "http://example.org:8080")
			//   return;
		};
		App.prototype.setgroups = function() {
			var groups = this.user.groups;
			console.log("groups", groups);
			$("div#post div.groups").empty();
			$.each(groups, function(i, item) {
				$("div#post div.groups").append('<label class="checkbox inline"><input type="checkbox" id="grp_' + i + '" value="' + i + '">' + item + '</label>');
				$("ul#navfilter").append('<li><a id="entr_' + i + '" href="#"><span class="icon icon-folder-open"></span> ' + item + '</a></li>');
			});

		}
		App.prototype.postBox = function() {
			var str = $("div#post textarea").val();
			var msg = this.currentData;
			delete msg.user;
			var groups = [];
			
			$("div#post div.groups input:checked").each(function(i, item) {
				groups.push($(item).attr('value'));
			});
			msg['groups'] = groups;
			console.log("Pushing obj", msg); // return;
			this.post(msg);
			$("div#post textarea").val("").focus();
		};
		App.prototype.post = function(msg) {
			var that = this;
			UWAP.feed.post(msg, function() {
				$("#feed").empty().append('<p>Thanks for sharing...</p>');
			});
		}

		// App.prototype.load = function() {
		// 	var that = this;
		// 	UWAP.feed.read({}, function(data) {
		// 		console.log("FEED Received", data);
		// 		$("div#feed").empty();
		// 		$.each(data, function(i, item) {
		// 			// item.user = 'User ' + item['uwap-userid'];
		// 			item.timestamp = moment(item.ts).format();

		// 			item.groupnames = [];
		// 			if (item.groups) {
		// 				$.each(item.groups, function(i, g) {
		// 					if (that.groups[g]) {
		// 						item.groupnames.push(that.groups[g]);
		// 					} else {
		// 						item.groupnames.push(g);
		// 					}
		// 				});
		// 			}

		// 			var h = $("#itemTmpl").tmpl(item);
		// 			$("div#feed").prepend(h);
		// 			console.log("Object,", item);
		// 		});

		// 		// $("span.ts").prettyDate(); 
		// 	});
		// }


		// setInterval(function(){ 
		// 	$("span.ts").prettyDate(); 
		// }, 8000);


		UWAP.auth.checkPassive(function(user) {

			var app = new App($("body"), user);
			// app.setgroups(user.groups);

		}, function() {
			$("#noauth").show();
			$("#feed").hide();
			$("#noauth").on("click", function() {
				var w = window.open("http://feed.app.bridge.uninett.no/");
			});
		});


	});


});

