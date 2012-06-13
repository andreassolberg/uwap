define(function() {

	var appPicker = Spine.Class.sub({

		init: function(element) {

			this.selected = null;

			console.log("Initing app picker");
			this.element = element;
			$(this.element).on("click", "li", this.proxy(this.select));
		},

		select: function(event) {
			var appid = $(event.currentTarget).attr('data-itemid');
			console.log("Selected something", appid);
			console.log(this);

			if (appid !== this.selected) {
				this.selected = appid;
				this.trigger('selected', appid);
				$(this.element).find("li").removeClass("active");
				$(event.currentTarget).addClass("active");
			}
		},

		getAppItem: function(item) {
			var ji = $('<li class=""><a href="#">' + item.name + '</a></li>');
			ji.attr('data-itemid', item.id);
			return ji;
		},

		addList: function(list) {
			var i;
			console.log("adding list of apps to be selected...", list);
			$(this.element).empty();

			if (list.app) {
				$(this.element).append('<li class="nav-header">Applications</li>');
				for(i = 0; i < list.app.length; i++) {
					$(this.element).append(this.getAppItem(list.app[i]));
					// $(this.element).append('<li class=""><a href="#">' + list.app[i].name + '</a></li>');
				}
			}

			if (list.proxy) {
				$(this.element).append('<li class="nav-header">Proxies</li>');
				for(i = 0; i < list.proxy.length; i++) {
					$(this.element).append(this.getAppItem(list.proxy[i]));
				}
			}

			if (list.client) {
				$(this.element).append('<li class="nav-header">Clients</li>');
				for(i = 0; i < list.client.length; i++) {
					$(this.element).append(this.getAppItem(list.client[i]));
				}
			}

		}

	});
	appPicker.include(Spine.Events);
	return appPicker;
})
