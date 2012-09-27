define([
	'./moment', './PostController'
], function(moment, PostController) {

	$("document").ready(function() {


		var App = function(el) {
			var that = this;
			this.el = el;
			this.groups = {};

			this.postcontroller = new PostController(this.el.find("div#post"));
			this.postcontroller.onPost($.proxy(this.post, this));

			
			this.load();
		}
		App.prototype.setgroups = function(groups) {
			this.groups = groups;
			this.postcontroller.setgroups(groups);
		}

		App.prototype.post = function(msg) {
			var that = this;
			// console.log("POSTING", msg);
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
					// item.user = 'User ' + item['uwap-userid'];
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

				$("span.ts").prettyDate(); 
			});
		}


		setInterval(function(){ 
			$("span.ts").prettyDate(); 
		}, 8000);


		UWAP.auth.require(function(user) {

			
			var app = new App($("body"))
			app.setgroups(user.groups);

		});



	});

});

