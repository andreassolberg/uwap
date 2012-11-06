define([

], function() {

	var NotificationsController = function(el) {
		this.el = el;

		// this.el.on("click", ".actPost", $.proxy(this.actPost, this));
		// this.el.find("button.posttype").on('click', $.proxy(this.actTypeSelect, this));
		// this.el.on('click', '#sharewithgrouplist a.actShareWith', $.proxy(this.shareWith, this));
		// this.el.on('click', 'a.resetsharedwith', $.proxy(this.resetShareWith, this));
		// this.el.find("button#btn-message").button('toggle').click();
		
		this.el.on('click', '.notificationsentry', $.proxy(this.select, this));
		this.el.on('click', '.markallread', $.proxy(this.markAllRead, this));

		this.items = null;

		this.load();
	} 


	NotificationsController.prototype.load = function() {
		var that = this;

		UWAP.feed.notifications({}, function(data) {
			that.updateNotifications(data);
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

		var allids = [];
		$.each(this.items, function(i, item) {
			if (!item.isread) {
				allids.push(item.id);
			}
		});


		UWAP.feed.notificationsMarkRead(allids, function(data) {
			that.load();
		});
	}

	NotificationsController.prototype.markRead = function(ids) {
		var that = this;
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
		console.log(" ››››› Proicessed", n);

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
			var linked = item.id;
			var icon = 'icon-exclamation-sign';

			if ($.inArray('comment', item.class) !== -1) {
				icon = 'icon-comment';
				console.log("Comment in class", item.class)
			}
			
			if (item.inresponseto) {
				linked = item.inresponseto;
			}

			if (item.isread) {
				$(".notificationlist").prepend('<li class="isread notificationentry"><a href="#!/item/' + linked + '"><i class="' + icon + '"></i> ' + item.summary + '</a></li>');
			} else {
				$(".notificationlist").prepend('<li class="notificationentry"><a href="#!/item/' + linked + '"><i class="' + icon + '"></i> ' + item.summary + '</a></li>');
			}
			
		});

		var ncount = $(".notificationcount").empty().hide();
		if (data.unreadcount > 0) {
			ncount.text(data.unreadcount).show();
		} 
		
	}


	return NotificationsController;

});

