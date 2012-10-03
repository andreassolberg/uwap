define([

], function() {

	var GroupSelectorController = function(el) {
		this.el = el;

		this.callback = null;
		this.groups = {};

		this.el.on("click", '.actGroupSelect', $.proxy(this.actGroupSelect, this));
		
	} 



	GroupSelectorController.prototype.actGroupSelect = function(e) {
		e.preventDefault();
		
		var currentListItem = $(e.currentTarget).closest('li');
		var selector = currentListItem.data('selector');

		// msg.groups = this.item.groups;
		// msg.message = this.el.find('textarea').val();
		// msg.inresponseto = this.item.id;
		// 
		
		this.el.find('li').removeClass('active');
		currentListItem.addClass('active');

		console.log("selector", selector); // return;
		
		if (this.callback) {
			this.callback(selector);
		}

	}

	GroupSelectorController.prototype.setgroups = function(groups) {
		var that = this;
		this.groups = groups;

		this.el.empty();
		$('<li class="nav-header">UWAP</li>').appendTo(this.el);
		// $('<li class=""><a href="#"><i class=" icon-globe"></i> Public feed</a></li>')
		// 	.data('selector', {}).appendTo(this.el);
		$('<li class="active"><a class="actGroupSelect" href="#"><i class=" icon-home"></i> Newsfeed</a></li>')
			.data('selector', {}).appendTo(this.el);

		$('<li class=""><a class="actGroupSelect" href="#"><i class=" icon-user"></i> Your entries</a></li>')
			.data('selector', {user: '@me'}).appendTo(this.el);


		$.each(groups, function(i, item) {
			var ne = $('<li><a class="actGroupSelect" id="entr_' + i + '" href="#">' +
					'<span class="icon icon-tag"></span> ' + item + 
				'</a></li>')
				.data('selector', {group: i});
			that.el.append(ne);
			
			// console.log("ADDING ENTRY ", i, that.el);
		});

		$('<li class="delimiter"></li><li class=""><a target="_blank" href="https://groupmanager.uwap.org">Setup a new group...</a></li>').appendTo(this.el);
	}

	GroupSelectorController.prototype.onSelect = function(callback) {
		this.callback = callback;
	}

	return GroupSelectorController;

});

