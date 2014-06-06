define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),


		AddCommentController = require('./AddCommentController'),
		// MediaPlayerController = require('MediaPlayerController'),
		// ViewController = require('ViewController'),
		moment = require('uwap-core/js/moment'),
		hogan = require('uwap-core/js/hogan')
		;

	var tmpl = {
		"feedItem": require('uwap-core/js/text!templates/feedItem.html'),
		"feedItemFile": require('uwap-core/js/text!templates/feedItemFile.html'),
		"feedItemComment": require('uwap-core/js/text!templates/feedItemComment.html'),
		"participant":  require('uwap-core/js/text!templates/participant.html')
	};


	var FeedController = function(pane, app) {
		this.pane = pane;
		this.app = app;

		this.groups = {};
		this.subscriptions = {};

		this.currentRange = null;
		this.loadeditems = {};
		this.selector = {};
		this.view = {
			view: 'feed'
		};

		var vbcel = $('<div class="feedcontainer"></div>')
			.appendTo(this.pane.el);

		this.pane.el.find('.feedcontainer').addClass('view-' + this.view.view);

		// this.viewcontroller = new ViewController($("#viewbarcontroller"));
		// this.viewcontroller.onChange($.proxy(this.viewchange, this));

		// this.mediaplayer = new MediaPlayerController(this.pane.el);

		this.pane.el.on('click', '.actEnableComment', $.proxy(this.enableComment, this));
		this.pane.el.on('click', '.actDelete', $.proxy(this.deleteItem, this));

		this.pane.el.on('click', '#postEnableBtn', $.proxy(this.postEnable, this));
		this.pane.el.on('click', '#postDisableBtn', $.proxy(this.postDisable, this));

		this.pane.el.on('click', '.responseOption', $.proxy(this.respond, this));


		this.templates = {
			"itemTmpl": hogan.compile(tmpl.feedItem),
			"itemTmplFile": hogan.compile(tmpl.feedItemFile),
			"commentTmpl": hogan.compile(tmpl.feedItemComment),
			"participant": hogan.compile(tmpl.participant)
		};

		// console.log("Feed controller loaded.")


		// this.load();
		
		setInterval($.proxy(this.update, this), 10000);

	}

	FeedController.prototype.setMyResponse = function(target, status) {
		var text = [
			{
				'yes': 'Attend',
				'maybe': 'Maybe',
				'no': 'Appologize'
			},
			{
				'yes': 'I&apos;m attending',
				'maybe': 'I&apos;m maybe attending',
				'no': 'I&apos;m appologized'
			}
		];
		var icon = '<i class="icon-ok icon-white"></i> ';

		// console.log("    › Set my response", target);

		target.find('.responseOption').each(function(i, opt) {
			// console.log("Response options is ", opt);

			cur = $(opt).data('status');
			if (cur === status) {
				// console.log("SETTIN STATUS TO BE ", status);
				$(this).removeClass('btn-small');
				$(this).removeClass('btn-mini');
				$(this).html(icon + text[1][cur]);
			} else {
				$(this).removeClass('btn-small');
				$(this).addClass('btn-mini');
				$(this).html(text[0][cur]);
			}	
		});


	}


	FeedController.prototype.respond = function(e) {
		var that = this;
		if (e) e.preventDefault();
		var targetItem = $(e.currentTarget).closest('div.item');
		var item = targetItem.data('object');
		var status = $(e.currentTarget).data('status');
		

		var response = {};
		response['audience'] = item['audience'];
		response.inresponseto = item.id;
		response.status = status;
		response.class = ['response'];

		// console.log("Response with ", status, item, response);

		// return;


		UWAP.feed.respond(response, function(feed) {
			// console.log("RESPOND COMPLETE", feed);
			that.setMyResponse(targetItem, status);

			that.processFeedResponse(feed);
		});

	}


	FeedController.prototype.setuser = function(u) {
		this.user = u;
	}
	FeedController.prototype.setgroups = function(groups) {
		this.groups = groups;
	}
	FeedController.prototype.setsubscriptions = function(subscriptions) {
		this.subscriptions = subscriptions;
	}

	FeedController.prototype.enableComment = function(e) {
		e.preventDefault();

		var targetItem = $(e.currentTarget).closest('div.item');
		$(e.currentTarget).hide();
		var item = targetItem.data('object');
		// console.log(" ===== About to enable comment", this.app.user, targetItem, item);
		var cc = new AddCommentController(this.app.user, item, targetItem.find('div.postcomment'));
		cc.onPost($.proxy(this.post, this));

	}

	FeedController.prototype.deleteItem = function(e) {
		var that = this;
		e.preventDefault();
		var currentItem = $(e.currentTarget).closest('.item');
		var item = currentItem.data('object');
		// console.log('About to delete ', currentItem.data());

		UWAP.feed.delete(item.id, function(data) {
			// console.log("Delete response Received", data);
			// that.load();
			if (data) {
				that.loadeditems[item.id].detach();	
			} else {
				alert("error deleting this entry");
			}
			

		});

	}



	FeedController.prototype.addItem = function(item) {
		var that = this;

		console.log("Add item", item)

		item.groupnames = [];
		if (item.groups) {
			$.each(item.groups, function(i, g) {
				if (that.groups[i]) {
					item.groupnames.push(that.groups[g].displayName);
				} else {
					item.groupnames.push(g);
				}
			});
		}

		// console.log("Testing article class", item.class)
		if ($.isArray(item.class) && $.inArray('article', item.class) !== -1) {
			// console.log("MATCH:", item.class, ' ' + $.inArray('article', item.class));
			// console.log("ARTICLE", item);
			item.message = item.message.replace(/([\n\r]{2,})/gi, '</p><p class="articleParagraph">');
		}

		if (item.hasClass('comment')) {
			// console.log("Has class comment", item);
			this.addComment(item);
		} else if (item.hasClass('response')) {
			this.addResponse(item);
		} else {
			this.addPost(item);
		}

	}


	FeedController.prototype.addPost = function(item) {
		var 
			h,
			feedcontainer = this.pane.el.find('.feedcontainer');

		if (this.loadeditems[item.id]) {
			// console.error("Post is already loaded. No need to load it again.");
			this.loadeditems[item.id].detach();
		}

		var itemview = this.getItemView(item);
		h = $(this.templates['itemTmpl'].render(itemview));
		h.data('object', item).prependTo(feedcontainer);
		
		// console.log("Adding post", itemview);

		this.loadeditems[item.id] = h;
	}

	FeedController.prototype.getItemView = function(item) {

		var ts;
		if (item.ts) {
			ts = item.ts;
		} else if (item.created) {
			ts = item.created;
		}

		if (item.user) {
			item.icon = item.user.photourl();
		} else {
			item.icon = item.client.logo();
		}


		item.timestamp = moment(ts).format();
		item.ts = ts;
		
		if (item.audience.groups) {
			item.groups = {};
			for(var i = 0; i < item.audience.groups.length; i++) {
				var group = item.audience.groups[i];
				if (this.groups[group]) {
					item.groups[group] = this.groups[group];
				} else if (this.subscriptions[group]) {
					item.groups[group] = this.subscriptions[group];
				} else {
					item.groups[group] = {name: group};
				}
				
			}

			// item.groupnames = {};
			item.groupnames = [];
			for(var key in item.groups) {
				// item.groupnames[key] = item.groups[key].title;
				item.groupnames.push(item.groups[key].displayName);
			}
		}




		// console.log(" - o - o - o getItemView()", item);
		// console.log("item group", item.audience.groups);

		return item;
	}

	FeedController.prototype.addResponse = function(item) {
		// console.log("Adding a response", item['uwap-userid'], item['inresponseto']);
		// console.log(this.loadeditems);

		if (this.loadeditems[item.id]) {
			console.error("Adding a response that is already loaded.")
			// console.error("Post is already loaded. No need to load it again.");
			this.loadeditems[item.id].detach();
		}


		// 
		if (this.loadeditems[item.inresponseto]) {

			// console.log(this.loadeditems[item.inresponseto]);

			// console.log("Adding YES", this.loadeditems[item.inresponseto]);
			if (item['uwap-userid'] === this.app.user.userid) {
				// console.log("MY RESPONSE", item);
				this.setMyResponse(this.loadeditems[item.inresponseto], item.status);
			}
			
			var itemview = this.getItemView(item);
			// var h = $(this.templates['commentTmpl'].render(itemview));

			item.statusItem = {};
			item.statusItem[item.status] = true;

			var h = $(this.templates.participant.render(itemview)).data('object', item);
			// var h = $("#participantTmpl").tmpl(item);
			this.loadeditems[item.inresponseto].find('table.participants').append(h);
		}
	}

	FeedController.prototype.addComment = function(item) {
		// console.log("Add comment", item, this.loadeditems);

		if (this.loadeditems[item.id]) {
			// console.log("Adding a response that is already loaded.")
			console.error("Comment is already loaded. No need to load it again.");
			this.loadeditems[item.id].detach();
		}

		if (this.loadeditems[item.inresponseto]) {
			// console.log("found item", item);
			// var h = $("#commentTmpl").tmpl(item);
			
			
			var itemview = this.getItemView(item);
			var h = $(this.templates['commentTmpl'].render(itemview));

			// console.log("ITEM VIEW IS ", itemview);

			// h = $(this.templates['itemTmpl'].render(itemview));
			h.data('object', item);
			this.loadeditems[item.inresponseto].find('div.comments').append(h);

		}
	}


	FeedController.prototype.getSettings = function() {
		var s = {};
		for (var k in this.selector) {
			if (this.selector.hasOwnProperty(k)) {
				s[k] = this.selector[k]
			}
		}


		return s;
	}


	FeedController.prototype.setSelector = function(selector) {


		var prevGroup = (this.selector.group ? this.selector.group : null);
		var newGroup = (selector.group ? selector.group : null);

		if (prevGroup !== newGroup) {
			
			var message = {
				"action": "setContext", 
				"context": {
					"group": newGroup
				}
			}
			// console.log("------> Postmessage", message, $("iframe#connect-widget"));
			var ix = document.getElementById("connect-widget");
			if (ix) {
				ix.contentWindow.postMessage(message, '*');
			}
		}

		// console.log("Set selector", selector);


		if (selector.group) {

			// this.app.navbar.set([
			// 	{'title': 'Newsfeed', 'href': '/'},
			// 	{'title': 'Group'}
			// ]);		
			// this.app.setHash('/group/' + selector.group);
		}


		this.selector = selector;
		this.load();
	}


	FeedController.prototype.postEnable = function(e) {
		e.preventDefault();
		this.pane.el.find('#enablePost').hide();
		this.pane.el.find('#post').show();
	}

	FeedController.prototype.postDisable = function(e) {
		e.preventDefault();
		this.pane.el.find('#enablePost').show();
		this.pane.el.find('#post').hide();
	}

	FeedController.prototype.viewchange = function(opt) {
		// console.log(' =============> View change', opt);

		this.pane.el.find('.feedcontainer').removeClass('view-' + this.view.view);
		this.pane.el.find('.feedcontainer').addClass('view-' + opt.view);

		this.view = opt;
		this.load();
	}




	FeedController.prototype.update = function() {
		var that = this;
		// console.log("About to update");
		if (!this.currentRange) return;
		// console.log("Updating...", this.currentRange);

		var s = this.getSettings();
		s.from = this.currentRange.to;

		// console.log(" ======> About to read feed");
		// console.log(s);
		// return;

		UWAP.feed.read(s, $.proxy(this.processFeedResponse, this));

	};

	FeedController.prototype.processFeedResponse = function(data) {
		var that = this;
		if (!data.range) return;
		that.currentRange = data.range;

		$.each(data.items, function(i, item) {
			if (item.inresponseto) return;
			that.addItem(item);
		});

		$.each(data.items, function(i, item) {
			if (!item.inresponseto) return;
			// console.log("=== ABOUT to load a comment inresponse to", item);
			that.addItem(item);
		});

		that.pane.activate();


		var groupcounter = {};
		$.each(data.items, function(i, item) {
			if (item.audience && item.audience.groups) {
				$.each(item.audience.groups, function(j, group) {
					if (!groupcounter[group]) groupcounter[group] = 0;
					groupcounter[group]++;
				});
			}
		});

		

		this.app.feedselector.setFeedActivity(groupcounter);


		$("span.ts").prettyDate(); 
		$('.dropdown-toggle').dropdown();
	}

	FeedController.prototype.load = function() {
		var that = this;
		var feedcontainer = this.pane.el.find('.feedcontainer');
		var s = this.getSettings();

		// console.log(" =====> Load ", s);

		feedcontainer.empty();

		// console.log("About to uwap.feed.read()");
		UWAP.feed.read(s, $.proxy(this.processFeedResponse, this));
		
	}


	
	FeedController.prototype.post = function(msg) {
		var that = this;
		// console.log("POSTING", msg);
		UWAP.feed.post(msg, function(data) {
			that.update();
		});
	}


	return FeedController;


});