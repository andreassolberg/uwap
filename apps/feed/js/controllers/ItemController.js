define(function(require, exports, module) {


	// TODO Remove duplication between this and feedcontroller

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),

		AddCommentController = require('./AddCommentController'),
		// MediaPlayerController = require('MediaPlayerController'),
		// ViewController = require('ViewController'),
		hogan = require('uwap-core/js/hogan'),
		moment = require('uwap-core/js/moment')
		;


	var tmpl = {
		"feedItem": require('uwap-core/js/text!templates/feedItem.html'),
		"feedItemComment": require('uwap-core/js/text!templates/feedItemComment.html'),
		"participant":  require('uwap-core/js/text!templates/participant.html')
	};

	var ItemController = function(pane, app) {
		this.pane = pane;

		this.groups = {};
		this.subscriptions = {};

		this.app = app;
		this.id = null;

		this.loadeditems = {};

		// this.mediaplayer = new MediaPlayerController(this.pane.el);


		this.templates = {
			"itemTmpl": hogan.compile(tmpl.feedItem),
			"commentTmpl": hogan.compile(tmpl.feedItemComment),
			"participant": hogan.compile(tmpl.participant)
		};

		var vbcel = $('<div class="feedcontainer"></div>')
			.appendTo(this.pane.el);

		this.pane.el.on('click', '.actEnableComment', $.proxy(this.enableComment, this));
		this.pane.el.on('click', '.actDelete', $.proxy(this.deleteItem, this));

		this.pane.el.on('click', '#postEnableBtn', $.proxy(this.postEnable, this));
		this.pane.el.on('click', '#postDisableBtn', $.proxy(this.postDisable, this));


	}
	ItemController.prototype.clean = function(u) {
		var 
			feedcontainer = this.pane.el.find('.feedcontainer');

		feedcontainer.empty();
	}


	ItemController.prototype.enableComment = function(e) {
		if (e) e.preventDefault();

		var targetItem = $(e.currentTarget).closest('div.item');
		$(e.currentTarget).hide();
		var item = targetItem.tmplItem().data;
		console.log("Found item ", targetItem, item);
		var cc = new AddCommentController(this.user, item, targetItem.find('div.postcomment'));
		cc.onPost($.proxy(this.post, this));

	}

	ItemController.prototype.deleteItem = function(e) {
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


	ItemController.prototype.addItem = function(item) {
		var that = this;


		// console.log ("  ›››› ADD ITEM »›››››");
		
		// console.log("Working with ", item.ts, item.timestamp);




		item.groupnames = [];
		if (item.groups) {
			$.each(item.groups, function(i, g) {
				if (that.groups[i]) {
					item.groupnames.push(that.groups[g].title);
				} else {
					item.groupnames.push(g);
				}
			});
		}


		item.viewconfig = this.viewconfig;


		// console.log("Testing article class", item.class)
		if ($.isArray(item.class) && $.inArray('article', item.class) !== -1) {
			// console.log("MATCH:", item.class, ' ' + $.inArray('article', item.class));
			// console.log("ARTICLE", item);
			item.message = item.message.replace(/([\n\r]{2,})/gi, '</p><p class="articleParagraph">');
		}

		if (item.hasClass('comment')) {
			console.log("Has class comment", item);
			this.addComment(item);
		} else if (item.hasClass('response')) {
			this.addResponse(item);
		} else {
			this.addPost(item);
		}

	}


	ItemController.prototype.addPost = function(item) {
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
		
		this.loadeditems[item.id] = h;
	}

	ItemController.prototype.getItemView = function(item) {

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
				item.groupnames.push(item.groups[key].title);
			}
		}




		// console.log(" - o - o - o getItemView()", item);
		// console.log("item group", item.audience.groups);

		return item;
	}

	ItemController.prototype.addResponse = function(item) {
		console.log("Adding a response", item['uwap-userid'], item['inresponseto']);
		console.log(this.loadeditems);

		if (this.loadeditems[item.id]) {
			console.error("Adding a response that is already loaded.")
			// console.error("Post is already loaded. No need to load it again.");
			this.loadeditems[item.id].detach();
		}


		// 
		if (this.loadeditems[item.inresponseto]) {

			console.log(this.loadeditems[item.inresponseto]);

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

	ItemController.prototype.addComment = function(item) {
		console.log("Add comment", item, this.loadeditems);

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





	ItemController.prototype.getSettings = function() {
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


	ItemController.prototype.setSelector = function(selector) {
		this.selector = selector;
		this.load();
	}


	ItemController.prototype.postEnable = function(e) {
		e.preventDefault();
		this.pane.el.find('#enablePost').hide();
		this.pane.el.find('#post').show();
	}

	ItemController.prototype.postDisable = function(e) {
		e.preventDefault();
		this.pane.el.find('#enablePost').show();
		this.pane.el.find('#post').hide();
	}

	ItemController.prototype.viewchange = function(opt) {
		console.log('View change', opt);



		this.pane.el.find('.feedcontainer').removeClass('view-' + this.view.view);
		this.pane.el.find('.feedcontainer').addClass('view-' + opt.view);

		this.view = opt;
		this.load();
	}




	ItemController.prototype.update = function() {
		var that = this;
		// console.log("About to update");
		if (!this.currentRange) return;
		// console.log("Updating...", this.currentRange);

		var s = this.getSettings();
		s.from = this.currentRange.to;


		UWAP.feed.read(s, function(data) {
			console.log("FEED Update Received", data);
			// $(".feedtype").empty();
			if (!data.range) return;
			that.currentRange.to = data.range.to;

			$.each(data.items, function(i, item) {
				if (!item.hasOwnProperty('promoted')) {
					item.promoted = false;
				}
				that.addItem(item);
			});

			$("span.ts").prettyDate(); 
		});

	};

	ItemController.prototype.load = function() {
		var that = this;
		var 
			feedcontainer = this.pane.el.find('.feedcontainer');

		var s = this.getSettings();

		console.log("Load ", this.view.view);

		feedcontainer.empty();



		console.log("About to uwap.feed.read()");
		UWAP.feed.read(s, $.proxy(this.processFeedResponse, this));

		
	}

	ItemController.prototype.processFeedResponse = function(data) {
		var that = this;
		if (!data.range) return;
		that.currentRange = data.range;

		$.each(data.items, function(i, item) {
			if (item.inresponseto) return;
			that.addItem(item);
		});

		$.each(data.items, function(i, item) {
			if (!item.inresponseto) return;
			console.log("=== ABOUT to load a comment inresponse to", item);
			that.addItem(item);
		});

		that.pane.activate();

		$("span.ts").prettyDate(); 
		$('.dropdown-toggle').dropdown();
	}



	ItemController.prototype.setMyResponse = function(target, status) {
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



	ItemController.prototype.post = function(msg) {
		var that = this;
		// console.log("POSTING", msg);
		UWAP.feed.post(msg, function() {
			that.load();
		});
	}

	ItemController.prototype.setuser = function(u) {
		this.user = u;
	}
	ItemController.prototype.setgroups = function(groups) {
		this.groups = groups;
	}
	ItemController.prototype.setsubscriptions = function(subscriptions) {
		this.subscriptions = subscriptions;
	}

	ItemController.prototype.load = function(id) {

		var that = this, ids = [];

		if (id) this.id = id;

		UWAP.feed.readItem(this.id, function(data) {
			console.log("FEED Single Item Received", data);
			// $(".feedtype").empty();

			that.clean();

			$.each(data.items, function(i, item) {
				if (item.inresponseto) return;
				that.addItem(item);
				ids.push(item.id);
			});

			$.each(data.items, function(i, item) {
				if (!item.inresponseto) return;
				that.addItem(item);
				ids.push(item.id);
			});

			$("span.ts").prettyDate(); 

			// that.enableComment();
			that.pane.activate();
			console.log("Activating pane for single item", data);


			that.app.notificationsController.markRead(ids);

			// that.currentRange.to = data.range.to;
			// $.each(data.items, function(i, item) {
			// 	if (!item.hasOwnProperty('promoted')) {
			// 		item.promoted = false;
			// 	}
				
			// });
			// $("span.ts").prettyDate(); 
		});
	}


	return ItemController;


});