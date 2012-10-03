define([
	'./moment', './PostController', './AddCommentController', './GroupSelectorController'
], function(moment, PostController, AddCommentController, GroupSelectorController) {

	$("document").ready(function() {


		var App = function(el) {
			var that = this;
			this.el = el;
			this.groups = {};
			this.loadeditems = {};

			this.selector = {};

			this.groupcontroller = new GroupSelectorController(this.el.find('ul#navfilter'));
			this.groupcontroller.onSelect($.proxy(this.setSelector, this));

			this.postcontroller = new PostController(this.el.find("div#post"));
			this.postcontroller.onPost($.proxy(this.post, this));

			this.el.on('click', '.actEnableComment', $.proxy(this.enableComment, this));
			this.el.on('click', '.actDelete', $.proxy(this.deleteItem, this));

			this.load();
		}

		App.prototype.setSelector = function(selector) {
			this.selector = selector;
			this.load();
		}

		App.prototype.setauth = function(user) {
			this.user = user;
			this.groups = user.groups;
			this.postcontroller.setgroups(user.groups);
			this.groupcontroller.setgroups(user.groups);
		}

		App.prototype.enableComment = function(e) {
			e.preventDefault();

			var targetItem = $(e.currentTarget).closest('div.item');
			var item = targetItem.tmplItem().data;
			console.log("Found item ", targetItem, item);
			var cc = new AddCommentController(this.user, item, targetItem.find('div.postcomment'));
			cc.onPost($.proxy(this.post, this));

		}

		App.prototype.deleteItem = function(e) {
			var that = this;
			e.preventDefault();
			var currentItem = $(e.currentTarget).closest('.item');
			var item = currentItem.tmplItem().data;
			console.log('About to delete ', item.id);

			UWAP.feed.delete(item.id, function(data) {
				console.log("Delete response Received", data);
				that.load();
			});

		}

		App.prototype.post = function(msg) {
			var that = this;
			// console.log("POSTING", msg);
			UWAP.feed.post(msg, function() {
				that.load();
			});
		}

		App.prototype.addItem = function(item) {
			var that = this;
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

			if (item.inresponseto) {
				this.addComment(item);
			} else {
				this.addPost(item);
			}

		}


		App.prototype.addPost = function(item) {
			var h = $("#itemTmpl").tmpl(item);
			$("div#feed").prepend(h);
			this.loadeditems[item.id] = h;
		}
		App.prototype.addComment = function(item) {
			console.log("Add comment");
			if (this.loadeditems[item.inresponseto]) {
				console.log("found item", item);
				var h = $("#commentTmpl").tmpl(item);
				this.loadeditems[item.inresponseto].find('div.comments').prepend(h);
			}
		}


		App.prototype.load = function() {
			var that = this;

			UWAP.feed.read(this.selector, function(data) {
				console.log("FEED Received", data);
				$("div#feed").empty();
				$.each(data, function(i, item) {
					that.addItem(item);
				});

				$("span.ts").prettyDate(); 
			});
		}


		setInterval(function(){ 
			$("span.ts").prettyDate(); 
		}, 8000);


		UWAP.auth.require(function(user) {

			
			var app = new App($("body"))
			app.setauth(user);

		});



	});

});

