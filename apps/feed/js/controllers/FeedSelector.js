define([

], function() {

	var FeedSelector = function(app, el) {
		this.el = el;

		this.app = app;
		this.callback = null;

		this.currentSelector = null;


		this.groups = {};
		this.groupels = {};

		this.groupcounter = {};

		this.notifications = {};

		this.showAllLock = false;

		this.el.on("click", '.actGroupSelect', $.proxy(this.actGroupSelect, this));
		this.el.on("click", '#showAllFeedSelector', $.proxy(this.showAll, this));
		
	} 

	FeedSelector.prototype.getListElItem = function(groupid) {
		var x = null;
		this.el.find('li.group').each(function(i, element) {
			var groupid = $(element).data('selector').group;
			if (groupid === groupid) {
				x = element;
			}
		});
			
		return x;
	}

	FeedSelector.prototype.selectUser = function(userid) {


		this.currentSelector = {user: '@me'};

		if (this.callback) {
			this.callback(this.currentSelector);
		}

	}



	FeedSelector.prototype.selectGroup = function(groupid) {

		// console.log("selectgroup()");

		// var e = new Error('dummy');
		// var stack = e.stack.replace(/^[^\(]+?[\n$]/gm, '')
		// 	.replace(/^\s+at\s+/gm, '')
		// 	.replace(/^Object.<anonymous>\s*\(/gm, '{anonymous}()@')
		// 	.split('\n');
		// console.log({"stack": stack});

		if (groupid !== null) {
			this.el.find('li').removeClass('active');

			this.currentSelector = {group: groupid};

			if (this.callback) {
				this.callback(this.currentSelector);
			}
		}


	}

	FeedSelector.prototype.actGroupSelect = function(e) {
		e.preventDefault(); e.stopPropagation();
		
		var currentListItem = $(e.currentTarget).closest('li');

		var selector = currentListItem.data('selector');
		this.currentGroup = selector;
		
		this.el.find('li').removeClass('active');
		currentListItem.addClass('active');

		// console.log("Setting acative actgroupselect", currentListItem);

		if (selector.group) {
			this.app.setHash('/group/' + selector.group);	
		} else if (selector.user) {
			this.app.setHash('/user/' + selector.user);
		} else {
			this.app.setHash('/');
		}
		
		if (this.callback) {
			this.callback(selector);
		}
	}

	FeedSelector.prototype.showAll = function(e) {
		e.preventDefault(); e.stopPropagation();

		var that = this;
		this.showAllLock = true;

		this.el.find('li.group').each(function(i, element) {
			$(element).show();
		});

		$("#showAllFeedSelectorSpan").hide();

		setTimeout($.proxy(function() {

			$("#showAllFeedSelectorSpan").show();
			that.showAllLock = false;
			that.checkActivity();

		}, this), 15000);

	}


	FeedSelector.prototype.setNotifications = function(groups) {

		var that = this;


		// this.el.find('li.group').each(function(i, element) {
		// 	var groupid = $(element).data('selector').group;
			
		// 	if (groups[groupid]) {



		// 	} else {




		// 	}

		// });


		// this.notifications = {};




		this.el.find('li.group').each(function(i, element) {
			var groupid = $(element).data('selector').group;
			// console.log("Checking groups ", groupid, groups[groupid], element);
			if (groups[groupid]) {
				$(element).find('span.notifications').empty().append('<span class="badge pull-right">'+ groups[groupid] + '</span>');
			} else {
				$(element).find('span.notifications').empty();
			}
		});




		$.each(groups, function(groupid, counter) {
			// $(that.groupels[groupid]).find('span.notifications').empty().append('<span class="badge pull-right">'+ counter + '</span>');

			if (!that.groupcounter[groupid]) {
				that.groupcounter[groupid] = 0;
			}
			that.groupcounter[groupid] += counter;
		});
		this.checkActivity();
	}

	FeedSelector.prototype.setgroups = function(groups, subscriptions) {
		var that = this;
		this.groups = groups;
		this.subscriptions = subscriptions;

		this.draw();
		this.checkActivity();
	}

	FeedSelector.prototype.setFeedActivity = function(groupcounter) {
		// console.log("GROUP Counter ", groupcounter);
		var that = this;
		$.each(groupcounter, function(groupid, counter) {
			if (!that.groupcounter[groupid]) {
				that.groupcounter[groupid] = 0;
			}
			that.groupcounter[groupid] += counter;
		});
		this.checkActivity();
	}


	FeedSelector.prototype.checkActivity = function() {
		var that = this;
		// console.log("CHECK activity");

		if (this.showAllLock) return;

		this.el.find('li.group').each(function(i, element) {
			var groupid = $(element).data('selector').group;
			if (that.groupcounter[groupid]) {
				$(element).show();
			} else {
				// console.log("Current group", that.currentGroup);
				if (that.currentSelector && that.currentSelector.group && that.currentSelector.group === groupid) {
					// console.log("I'm not hiding an active group, obvioulsy.");
				} else {
					$(element).hide();
				}

			}
		});
	}

	FeedSelector.prototype.draw = function() {


		console.log("FeedSelector draw", this.groups);

		var list = this.el.find('ul.list');
		var that = this;

		list.empty();

		console.log("drawing selector and current group is", this.currentSelector);

		var all= $('<li class=""><a class="actGroupSelect" href="#!/"><i class="glyphicon glyphicon-home"></i> All groups</a></li>')
			.data('selector', {})
			.data('content', '<i class="glyphicon glyphicon-home"></i> All groups');


		if (this.currentSelector === null) {

			all.addClass('active');	
		}

		all.appendTo(list);

		var you = $('<li class=""><a class="actGroupSelect" href="#!/"><i class="glyphicon glyphicon-user"></i> Your entries</a></li>')
			.data('selector', {user: '@me'})
			.data('content', '<i class=" icon-user"></i> Your entries');

		if (this.currentSelector && 
			this.currentSelector.user === '@me') {
			
			you.addClass('active');	
		}

		you.appendTo(list);


		$.each(this.groups, function(i, item) {

			var ne = $('<li class="group"><a class="actGroupSelect" id="entr_' + i + '" href="#">' +
					'<span class="glyphicon glyphicon-tag"></span> ' + UWAP.utils.escape(item.displayName) + 
					'<span class="notifications"></span></a></li>')
				.data('selector', {group: i})
				.data('content', '<span class="glyphicon glyphicon-tag"></span> ' + item.displayName);

			if (that.currentSelector && that.currentSelector.group && that.currentSelector.group === i) {
				ne.addClass('active'); console.log("Checking ", i , "against", that.currentGroup);
			}

			list.append(ne);

			// if ()

			that.groupels[item.id] = ne;
		});

		$.each(this.subscriptions, function(i, item) {

			var ne = $('<li class="group"><a class="actGroupSelect" id="entr_' + i + '" href="#">' +
					'<span class="glyphicon glyphicon-globe"></span> ' + UWAP.utils.escape(item.displayName) + 
					'<span class="notifications"></span></a></li>')
				.data('selector', {group: i});
				// .data('content', '<span class="glyphicon glyphicon-globe"></span> ' + item.title);
			if (that.currentSelector && that.currentSelector.group && that.currentSelector.group === i) {
				ne.addClass('active');
			}
			list.append(ne);
			that.groupels[item.id] = ne;
		});
	}


	FeedSelector.prototype.onSelect = function(callback) {
		this.callback = callback;
	}

	return FeedSelector;

});

