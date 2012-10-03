define([

], function() {


	var ViewController = function(el) {
		var that = this;

		this.views = {
			'feed': {
				'icon': 'icon-align-left',
				'name': 'Feed'
			},
			'media': {
				'icon': 'icon-th',
				'name': 'Media'
			},
			'calendar': {
				'icon': 'icon-calendar',
				'name': 'Calendar'
			},
			'files': {
				'icon': 'icon-download',
				'name': 'Download'
			},
			
		};
		this.opt = {
			'view': 'feed'
		};


		this.el = el;

		this.opt = {};
		
		this.el.on("click", "button.viewtype", $.proxy(this.actSelect, this));

		var viewcontainer = this.el.find('.viewtypes');

		$.each(this.views, function(i, v) {
			$('<button type="button" class="btn viewtype"><i class="' + v.icon + '"></i> ' + v.name + '</button>')
				.data('opt', i).appendTo(viewcontainer);
		});
		viewcontainer.children().eq(0).addClass('active');


	} 


	ViewController.prototype.actSelect = function(e) {
 		e.preventDefault();

 		var currentItem = $(e.currentTarget).data('opt');
 		this.opt.view = currentItem;

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

