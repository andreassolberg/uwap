define(function() {

	var Adressa = function(container) {
		this.container = container;
		this.url = 'http://www.adressa.no/nyheter/';
	
		
		setInterval($.proxy(this.refresh, this), 3*60*1000); // 3 minutes
		this.refresh();
	};

	Adressa.prototype.refresh = function() {
		var that = this;

		UWAP.data.get(this.url,  null, function(data) {
			var obj = $(data);
			var content = obj.find("div#content div.polarisStories div.article");
			console.log(content);
			$(that.container).empty().append(content);

		});


	}

	return Adressa;

});