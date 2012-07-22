define(function() {

	var VGCover = function(container) {
		this.container = container;
		this.url = 'http://pluss.vg.no/utgaver';
	
		
		setInterval($.proxy(this.refresh, this), 60*60*1000); // 60 minutes
		this.refresh();
	};

	VGCover.prototype.refresh = function() {
		var that = this;

		UWAP.data.get(this.url,  null, function(data) {
			var obj = $(data);
			var imgurl = obj.find("li a img").eq(0).attr('src');
			console.log(imgurl);
			$(that.container).empty().append('<img src="' + imgurl + '" />');

		});


	}

	return VGCover;

});