define([

], function() {

	var NotificationsController = function(app,el) {
		this.el = el;
		this.app = app;

		this.notifications = null;

		// this.el.on("click", ".actPost", $.proxy(this.actPost, this));
		// this.el.find("button.posttype").on('click', $.proxy(this.actTypeSelect, this));
		// this.el.on('click', '#sharewithgrouplist a.actShareWith', $.proxy(this.shareWith, this));
		// this.el.on('click', 'a.resetsharedwith', $.proxy(this.resetShareWith, this));
		// this.el.find("button#btn-message").button('toggle').click();
		
		this.el.on('click', '.notificationsentry', $.proxy(this.select, this));
		this.el.on('click', '.markallread', $.proxy(this.markAllRead, this));

		this.items = null;

		this.load();

		setInterval($.proxy(this.load, this), 30 * 1000); // Every 30 seconds...
	} 


	NotificationsController.prototype.load = function() {
		var that = this;

		// var settings = this.app.mainnewsfeed.getSettings();
		// console.log("Seetings", settings);

		UWAP.feed.notifications({}, function(data) {
			that.updateNotifications(data);
			that.app.feedselector.setNotifications(data.groups);
			// console.log("Updating group notifiations ", data.groups);

		});
	}

	NotificationsController.prototype.select = function(e) {
		var that = this;
		e.preventDefault();

		console.log('click', e.currentTarget);
	}

	NotificationsController.prototype.markAllRead = function(e) {
		var that = this;
		e.preventDefault();

		console.log('mark all read', e.currentTarget);
		console.log(this.items);

		// return;

		var allids = [];
		$.each(this.items, function(i, item) {
			allids.push(item.item.id);
			if (item.responses) {
				$.each(item.responses, function(j, response) {
					console.log("ABOUT TO MARK RESPONSE", response);
					allids.push(response.id);
				});
			}
		});


		UWAP.feed.notificationsMarkRead(allids, function(data) {
			that.load();
		});
	}

	NotificationsController.prototype.markRead = function(ids) {
		var that = this;

		console.log("About to mark read for ", ids);

		UWAP.feed.notificationsMarkRead(ids, function(data) {
			that.load();
		});
	}

	NotificationsController.prototype.processNotifications = function(items) {

		var 
			refs = {};

		$.each(items, function(i, item) {

			if (item.inresponseto) {
				if (refs[item.inresponseto]) {
						
					if (!refs[item.inresponseto].refs) {
						refs[item.inresponseto].refs = [];
					}
					refs[item.inresponseto].refs.push(item);

				} else {
					refs[item.inresponseto] = item;	
				}

				
			}
		});
		
		return items;

	}


	NotificationsController.prototype.updateNotifications = function(data) {
		n = data.items;
		this.items = n.slice(0);



		// n = this.processNotifications(n);
		// console.log(" ››››› Processed", n);

		var length = n.length;
		if (n.length > 0)  {
			$(".notificationcount").show();
		} else {
			$(".notificationcount").hide();
		}
		$(".notificationlist").find('.notificationentry').empty();

		if (n.length > 10) {
			$(".notificationlist").prepend('<li class="notificationentry"><a href="">' + (n.length-10) + ' more notifications...</a></li>');
			n.splice(0, (n.length-10));
		}

		$.each(n, function(i, item) {
			if (i > 10) return;
			var id = item.item.id;
			var icon = 'glyphicon-chevron-right';
			if ($.inArray('comment', item.item.class) !== -1) {
				icon = 'glyphicon-comment-alt';
			} else if ($.inArray('event', item.item.class) !== -1) {
				icon = 'glyphicon-calendar';
			}
			

			if (item.isread) {
				$(".notificationlist").prepend('<li class="isread notificationentry"><a href="#!/item/' + id + '"><i class="glyphicon ' + icon + '"></i> ' + item.summary + '</a></li>');
			} else {
				$(".notificationlist").prepend('<li class="notificationentry"><a href="#!/item/' + id + '"><i class="glyphicon ' + icon + '"></i> ' + item.summary + '</a></li>');
			}
			
		});

		var ncount = $(".notificationcount").empty().hide();
		if (this.items.length > 0) {
			ncount.text(this.items.length).show();
		} 
		
	}


	return NotificationsController;

});

