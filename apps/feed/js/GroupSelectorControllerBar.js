define([

], function() {

	var GroupSelectorControllerBar = function(el) {
		this.el = el;

		this.callback = null;
		this.groups = {};

		this.el.on("click", '.actGroupSelect', $.proxy(this.actGroupSelect, this));
		
	} 



	GroupSelectorControllerBar.prototype.actGroupSelect = function(e) {
		e.preventDefault();
		
		var currentListItem = $(e.currentTarget).closest('li');
		var selector = currentListItem.data('selector');

		// msg.groups = this.item.groups;
		// msg.message = this.el.find('textarea').val();
		// msg.inresponseto = this.item.id;
		// 
		
		this.el.find('li').removeClass('active');
		currentListItem.addClass('active');

		// console.log("selector", selector); // return;

		this.el.find('span.selectedFeed').empty().append(currentListItem.data('content'));
		
		if (this.callback) {
			this.callback(selector);
		}

	}

	GroupSelectorControllerBar.prototype.setgroups = function(groups) {
		var that = this;
		this.groups = groups;

		var list = this.el.find('ul#feedlist');

		// console.log("About to add to group...")

		// list.find('li.feeditems').empty();
		list.empty();

		// $('<li class="nav-header">UWAP</li>').appendTo(this.el);
		// $('<li class=""><a href="#"><i class=" icon-globe"></i> Public feed</a></li>')
		// 	.data('selector', {}).appendTo(this.el);

		$('<li class="active"><a class="actGroupSelect" href="#"><i class=" icon-home"></i> All groups</a></li>')
			.data('selector', {})
			.data('content', '<i class="icon-home"></i> All groups').appendTo(list);

		$('<li class=""><a class="actGroupSelect" href="#"><i class=" icon-user"></i> Your entries</a></li>')
			.data('selector', {user: '@me'})
			.data('content', '<i class=" icon-user"></i> Your entries').appendTo(list);

		$.each(groups, function(i, item) {
			var ne = $('<li><a class="actGroupSelect" id="entr_' + i + '" href="#">' +
					'<span class="icon icon-tag"></span> ' + item + 
				'</a></li>')
				.data('selector', {group: i})
				.data('content', '<span class="icon icon-tag"></span> ' + item);
			list.append(ne);
			
			// console.log("ADDING ENTRY ", i, that.el);
		});


                  	// <li><a target="_blank" href="https://groupmanager.uwap.org"><i class="icon-cog"></i> Manage groups</a></li>
                   //  <li><a target="_blank" href="https://subscribe.uwap.org"><i class="icon-cog"></i> Subscribe to open groups</a></li>

		// $('<li class="delimiter"></li><li class=""><a target="_blank" href="https://groupmanager.uwap.org">Setup a new group...</a></li>').prependTo(list);	
		list.append('<li class="divider"></li>' +
			'<li><a target="_blank" href="https://groupmanager.uwap.org"><i class="icon-cog"></i> Manage groups</a></li>' + 
			'<li><a target="_blank" href="https://subscribe.uwap.org"><i class="icon-cog"></i> Subscribe to open groups</a></li>');
	}

	GroupSelectorControllerBar.prototype.onSelect = function(callback) {
		this.callback = callback;
	}

	return GroupSelectorControllerBar;

});

