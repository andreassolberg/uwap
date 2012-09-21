define([
	'./moment'
], function(moment) {

	$("document").ready(function() {


		var App = function(el) {
			var that = this;
			this.el = el;
			this.groups = {};
			$("div#post").on("click", ".actPost", $.proxy(this.postBox, this));
			this.load();
		}
		App.prototype.setgroups = function(groups) {
			console.log("groups", groups);
			this.groups = groups;
			$("div#post div.groups").empty();
			$.each(groups, function(i, item) {
				$("div#post div.groups").append('<label class="checkbox inline"><input type="checkbox" id="grp_' + i + '" value="' + i + '">' + item + '</label>');
				$("ul#navfilter").append('<li><a id="entr_' + i + '" href="#"><span class="icon icon-folder-open"></span> ' + item + '</a></li>');
			});

		}
		App.prototype.postBox = function() {
			var str = $("div#post textarea").val();
			var msg = {
				message: str
			}
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
				that.load();
			});
		}
		App.prototype.load = function() {
			var that = this;
			UWAP.feed.read({}, function(data) {
				console.log("FEED Received", data);
				$("div#feed").empty();
				$.each(data, function(i, item) {
					item.user = 'User ' + item['uwap-userid'];
					item.timestamp = moment(item.ts).format();

					item.groupnames = [];
					if (item.groups) {
						$.each(item.groups, function(i, g) {
							if (that.groups[g]) {
								item.groupnames.push(that.groups[g]);
							} else {
								item.groupnames.push(g);
							}
						});
					}


					var h = $("#itemTmpl").tmpl(item);
					$("div#feed").prepend(h);
					console.log("Object,", item);
				});
			});
		}

		setInterval(function(){ 
			$("span.ts").prettyDate(); 
		}, 5000);


		UWAP.auth.require(function(user) {

			
			var app = new App($("body"))
			app.setgroups(user.groups);

		});



	});

});

