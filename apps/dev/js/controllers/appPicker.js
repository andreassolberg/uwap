define(function(require, exports, module) {


	var appPicker = function(element, callback) {
		this.selected = null;

		this.callback = callback;
		console.log("Initing app picker");
		this.element = element;
		$(this.element).on("click", "a", this.proxy(this.select));
	}

	/**
	 * Wraps a function to be called with relative to current object
	 */
	appPicker.prototype.proxy = function(func) {
		return $.proxy(func, this);
	}

	/**
	 * Typically when you move to the frontpage, no apps is selected.
	 * @return {[type]} [description]
	 */
	appPicker.prototype.unselect = function() {
		console.log("UNSELECT");
		$(this.element).find("a").removeClass("active");
	}

	/**
	 * Detects a click on one of the items.
	 * @param  {object} event Event object
	 */
	appPicker.prototype.select = function(event) {
		event.preventDefault(); event.stopPropagation();

		var appid = $(event.currentTarget).attr('data-itemid');
		var type = $(event.currentTarget).attr('data-type');
		console.log("Selected something", appid);
		console.log(this);

		if (appid !== this.selected) {
			this.selected = appid;
			this.callback(appid, type);
			$(this.element).find("a").removeClass("active");
			$(event.currentTarget).addClass("active");
		}
	}

	/**
	 * Utility function that finds the element of a specific Application ID
	 * @param  {[type]} appid [description]
	 * @return {[type]}       [description]
	 */
	appPicker.prototype.findElement = function(appid) {
		var found = null;
		$.each($(this.element).find("a"), function(i, item) {
			if ($(item).data('itemid') === appid) found = $(item);
		});
		console.log("  ----> findElement ", appid, found);
		return found;
	}


	appPicker.prototype.selectApp = function(appid) {
		console.log("SELECT APP");
		var element = this.findElement(appid);
		if (appid !== this.selected) {
			this.selected = appid;
			this.callback(appid);
			$(this.element).find("a").removeClass("active");
			element.addClass("active");
		}
	}

	appPicker.prototype.getAppItem = function(item) {
		var ji = $('<a class="list-group-item" href="#">' + UWAP.utils.escape(item.name) + '</a>');
		ji.attr('data-itemid', UWAP.utils.escape(item.id));
		ji.attr('data-type', UWAP.utils.escape(item.type));
		return ji;
	};

	appPicker.prototype.addList = function(items) {
		var i;
		console.log("adding list of apps to be selected...", list);
		$(this.element).empty();

		var list = {
			"app": [], "proxy": [], "client": []
		};
		for(var i = 0; i < items.length; i++) {
			list[items[i].type].push(items[i]);
		}


		if (list.app) {
			$(this.element).append('<h4 class="list-group-item">Applications</h4>');
			for(i = 0; i < list.app.length; i++) {
				$(this.element).append(this.getAppItem(list.app[i]));
				// $(this.element).append('<li class=""><a href="#">' + list.app[i].name + '</a></li>');
			}
		}

		if (list.proxy) {
			$(this.element).append('<h4 class="list-group-item">Proxies</h4>');
			for(i = 0; i < list.proxy.length; i++) {
				$(this.element).append(this.getAppItem(list.proxy[i]));
			}
		}

		if (list.client) {
			$(this.element).append('<h4 class="list-group-item">Clients</h4>');
			for(i = 0; i < list.client.length; i++) {
				$(this.element).append(this.getAppItem(list.client[i]));
			}
		}
	};


	return appPicker;
})

