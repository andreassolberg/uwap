define([

], function() {


	var ViewController = function(el) {
		var that = this;

		this.views = {
			'feed': {
				'icon': 'icon-align-left',
				'name': 'List view'
			},
			'media': {
				'icon': 'icon-th',
				'name': 'Media'
			},
			'calendar': {
				'icon': 'icon-calendar',
				'name': 'Calendar'
			},
			'file': {
				'icon': 'icon-download',
				'name': 'Files'
			},
			'members': {
				'icon': 'icon-user',
				'name': 'Participants'
			}
		};
		this.opt = {
			'view': 'feed'
		};


		this.el = el;

		this.opt = {};
		
		this.el.on("click", ".viewtype", $.proxy(this.actSelect, this));

		var viewcontainer = this.el.find('#viewtypes');

		$.each(this.views, function(i, v) {
			$('<li class="viewtype"><a href="">' + that.getText(i) + '</li>')
				.data('opt', i).appendTo(viewcontainer);
		});
		this.el.find('.selectedView').empty().append(this.getText('feed'));
		// viewcontainer.children().eq(0).addClass('active');


	} 


	ViewController.prototype.getText = function(key) {
		return '<i class="' + this.views[key].icon + '"></i> ' + this.views[key].name + '</a>';
	}

	ViewController.prototype.actSelect = function(e) {
 		e.preventDefault();

 		var currentItem = $(e.currentTarget).data('opt');
 		this.opt.view = currentItem;

 		this.el.find('.selectedView').empty().append(this.getText(this.opt.view));

 		console.log("Click on item", currentItem, this.views[currentItem]);
 		if (this.callback) {
 			this.callback(this.opt);
 		}

 		

	}

	ViewController.prototype.onChange = function(callback) {
		this.callback = callback;
	}

	return ViewController;

});

