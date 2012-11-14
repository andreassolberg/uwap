define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),

		AddCommentController = require('AddCommentController'),
		MediaPlayerController = require('MediaPlayerController'),
		ViewController = require('ViewController')
		;


	var ItemController = function(pane, app) {
		this.pane = pane;

		this.groups = {};
		this.app = app;
		this.id = null;

		this.loadeditems = {};

		this.mediaplayer = new MediaPlayerController(this.pane.el);

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
		item.timestamp = moment(item.ts*1000).format();

		console.log("Adding item", item);

		this.groups = this.app.getGroups();

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

		if (item.user) {
			item.user.profileimg = UWAP.utils.getEngineURL('/api/media/user/' + item.user.a);
		}
		if (item.client) {
			item.client.profileimg = UWAP.utils.getEngineURL('/api/media/logo/client/' + item.client['client_id']);
		}


		// console.log("Testing article class", item.class)
		if ($.isArray(item.class) && $.inArray('article', item.class) !== -1) {
			// console.log("MATCH:", item.class, ' ' + $.inArray('article', item.class));
			console.log("ARTICLE", item);
			item.message = item.message.replace(/([\n\r]{2,})/gi, '</p><p class="articleParagraph">');
		}

		if (item.inresponseto) {
			this.addComment(item);
		} else {
			this.addPost(item);
		}

	}


	ItemController.prototype.addPost = function(item) {
		var 
			h,
			feedcontainer = this.pane.el.find('.feedcontainer');

	
		h = $("#itemTmpl").tmpl(item);	
		feedcontainer.prepend(h);
	

		console.log("Add post", item);
		
		this.loadeditems[item.id] = h;
	}

	ItemController.prototype.addComment = function(item) {
		// console.log("Add comment");
		if (this.loadeditems[item.inresponseto]) {
			// console.log("found item", item);
			var h = $("#commentTmpl").tmpl(item);
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
		if (this.view.view === 'members' && s.group) {
			

			console.log("Load members", s);

			var gr = s.group;

			UWAP.groups.get(gr, function(data) {
				console.log("Group data received.", data);

				if (data.userlist) {
					feedcontainer.empty();

					for(var uid in data.userlist) {
						feedcontainer.append('<div>' + data.userlist[uid]['name'] + '</div>');
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
				
				feedcontainer.empty();

				if (!data.range) return;
				that.currentRange = data.range;

				if (that.view.view === 'media') {
					feedcontainer.append('<ul></ul>');	
				}
				

				$.each(data.items, function(i, item) {
					that.addItem(item);
				});

				$("span.ts").prettyDate(); 
			});

		}

		
	}
	ItemController.prototype.post = function(msg) {
		var that = this;
		// console.log("POSTING", msg);
		UWAP.feed.post(msg, function() {
			that.load();
		});
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