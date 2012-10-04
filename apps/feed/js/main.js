define([
	'./moment', './PostController', './AddCommentController', './GroupSelectorController', './MediaPlayerController', './ViewController'
], function(moment, PostController, AddCommentController, GroupSelectorController, MediaPlayerController, ViewController) {

	$("document").ready(function() {


		var App = function(el) {
			var that = this;
			this.el = el;
			this.groups = {};
			this.loadeditems = {};

			this.selector = {};
			this.view = {
				view: 'feed'
			};

			this.groupcontroller = new GroupSelectorController(this.el.find('ul#navfilter'));
			this.groupcontroller.onSelect($.proxy(this.setSelector, this));

			this.postcontroller = new PostController(this.el.find("div#post"));
			this.postcontroller.onPost($.proxy(this.post, this));

			this.mediaplayer = new MediaPlayerController(this.el);

			this.viewcontroller = new ViewController(this.el.find('#viewbarcontroller'));
			this.viewcontroller.onChange($.proxy(this.viewchange, this));

			this.el.on('click', '.actEnableComment', $.proxy(this.enableComment, this));
			this.el.on('click', '.actDelete', $.proxy(this.deleteItem, this));

			this.load();
		}

		App.prototype.viewchange = function(opt) {
			console.log('View change', opt);
			this.view = opt;
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
			$(e.currentTarget).hide();
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
			// console.log("Testing article class", item.class)
			if ($.isArray(item.class) && $.inArray('article', item.class) !== -1) {
				// console.log("MATCH:", item.class, ' ' + $.inArray('article', item.class));
				item.message = item.message.replace(/([\n\r]{2,})/gi, '</p><p class="articleParagraph">');
			}

			if (item.inresponseto) {
				this.addComment(item);
			} else {
				this.addPost(item);
			}

		}


		App.prototype.addPost = function(item) {
			var h;
			if (this.view.view === 'media') {
				h = $("#itemMediaTmpl").tmpl(item);
				$("#feedMedia").prepend(h);

				console.log("About to add media to ", $("#feedMedia"));
			} else if (this.view.view === 'file') {

				h = $("#itemFileTmpl").tmpl(item);
				$("#feedFiles").prepend(h);

			} else {
				h = $("#itemTmpl").tmpl(item);	
				$("#feedBasic").prepend(h);
			}
			
			
			this.loadeditems[item.id] = h;
		}
		App.prototype.addComment = function(item) {
			console.log("Add comment");
			if (this.loadeditems[item.inresponseto]) {
				console.log("found item", item);
				var h = $("#commentTmpl").tmpl(item);
				this.loadeditems[item.inresponseto].find('div.comments').append(h);
			}
		}

		App.prototype.getSettings = function() {
			var s = {};
			for (var k in this.selector) {
				if (this.selector.hasOwnProperty(k)) {
					s[k] = this.selector[k]
				}
			}
			
			if (this.view.view === 'media') {
				s['class'] = ['media'];
			}
			if (this.view.view === 'file') {
				s['class'] = ['file'];
			}
			if (this.view.view === 'calendar') {
				s['class'] = ['calendar'];
			}


			return s;
		}


		App.prototype.load = function() {
			var that = this;

			var s = this.getSettings();

			console.log("Load ", this.view.view);
			if (this.view.view === 'members' && s.group) {
				

				console.log("Load members", s);

				var gr = s.group;

				UWAP.groups2.get(gr, function(data) {
					console.log("Group data received.", data);

					if (data.userlist) {
						$(".feedtype").empty();

						for(var uid in data.userlist) {
							$("#feedBasic").append('<div>' + data.userlist[uid]['name'] + '</div>');
						}


					}


					// $.each(data, function(i, item) {
					// 	that.addItem(item);
					// });
					// $("span.ts").prettyDate(); 
				}, function() {
					console.error("Could not get list");
				});

			} else {
				UWAP.feed.read(s, function(data) {
				console.log("FEED Received", data);
				$(".feedtype").empty();
				$.each(data, function(i, item) {
					that.addItem(item);
				});

				$("span.ts").prettyDate(); 
			});
			}

			
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

