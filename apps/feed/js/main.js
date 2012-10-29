define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		moment = require('uwap-core/js/moment'),
    	PostController = require('PostController')
    	AddCommentController = require('AddCommentController'),
    	GroupSelectorController = require('GroupSelectorController'),
    	GroupSelectorControllerBar = require('GroupSelectorControllerBar'),
    	MediaPlayerController = require('MediaPlayerController'),
    	ViewController = require('ViewController'),

    	prettydate = require('uwap-core/js/pretty')
    	;

    require('uwap-core/js/jquery.tmpl');

	require('uwap-core/bootstrap/js/bootstrap');	
	
	require('uwap-core/bootstrap/js/bootstrap-modal');
	require('uwap-core/bootstrap/js/bootstrap-collapse');
	require('uwap-core/bootstrap/js/bootstrap-button');
	require('uwap-core/bootstrap/js/bootstrap-dropdown');

    // require('uwap-core/bootstrap/js/bootstrap-tooltip');
	// require('uwap-core/bootstrap/js/bootstrap-transition');
	// require('uwap-core/bootstrap/js/bootstrap-alert');
	// require('uwap-core/bootstrap/js/bootstrap-scrollspy');
	// require('uwap-core/bootstrap/js/bootstrap-tab');
	// require('uwap-core/bootstrap/js/bootstrap-popover');
	// require('uwap-core/bootstrap/js/bootstrap-carousel');
	// require('uwap-core/bootstrap/js/bootstrap-typeahead');

	$("document").ready(function() {



		var App = function(el) {
			var that = this;
			this.el = el;
			this.groups = {};
			this.loadeditems = {};

			this.currentRange = null;

			this.selector = {};
			this.view = {
				view: 'feed'
			};

			this.groupcontroller = new GroupSelectorController(this.el.find('ul#navfilter'));
			this.groupcontroller.onSelect($.proxy(this.setSelector, this));

			this.groupcontrollerbar = new GroupSelectorControllerBar(this.el.find('#feedmenu'));
			this.groupcontrollerbar.onSelect($.proxy(this.setSelector, this));


			this.postcontroller = new PostController(this.el.find("div#post"));
			this.postcontroller.onPost($.proxy(this.post, this));

			this.mediaplayer = new MediaPlayerController(this.el);

			this.viewcontroller = new ViewController(this.el.find('#viewbarcontroller'));
			this.viewcontroller.onChange($.proxy(this.viewchange, this));

			this.el.on('click', '.actEnableComment', $.proxy(this.enableComment, this));
			this.el.on('click', '.actDelete', $.proxy(this.deleteItem, this));

			this.load();
			setInterval($.proxy(this.update, this), 5000);
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
			this.groupcontrollerbar.setgroups(user.groups);
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

		App.prototype.updateNotifications = function(n) {

			
			
			if (n.length > 0)  {
				$(".notificationcount").show();
			} else {
				$(".notificationcount").hide();
			}
			$(".notificationlist").find('.notificationentry').empty();

			if (n.length > 10) {
				$(".notificationlist").prepend('<li class="notificationentry"> - ' + (n.length-10) + ' more notifications...</li>');
			}

			$.each(n, function(i, item) {
				if (i > 10) return;
				$(".notificationlist").prepend('<li class="notificationentry"><a href="#/item/' + item.id + '">' + item.summary + '</a></li>');
			});

			$(".notificationcount").empty().text(n.length);
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

		App.prototype.update = function() {
			var that = this;
			console.log("About to update");
			if (!this.currentRange) return;
			console.log("Updating...", this.currentRange);

			var s = this.getSettings();
			s.from = this.currentRange.to;

			UWAP.feed.notifications({}, function(data) {
				that.updateNotifications(data);
			});

			UWAP.feed.read(s, function(data) {
				console.log("FEED Update Received", data);
				// $(".feedtype").empty();
				if (!data.range) return;
				that.currentRange.to = data.range.to;
				$.each(data.items, function(i, item) {
					that.addItem(item);
				});
				$("span.ts").prettyDate(); 
			});

		};


		App.prototype.load = function() {
			var that = this;

			var s = this.getSettings();

			console.log("Load ", this.view.view);
			if (this.view.view === 'members' && s.group) {
				

				console.log("Load members", s);

				var gr = s.group;

				UWAP.groups.get(gr, function(data) {
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

					if (!data.range) return;
					that.currentRange = data.range;



					$.each(data.items, function(i, item) {
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

