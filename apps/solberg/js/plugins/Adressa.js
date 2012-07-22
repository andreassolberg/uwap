define(function() {

	var Adressa = function(container) {
		this.container = container;
		this.url = 'http://www.adressa.no/nyheter/';
	
		
		setInterval($.proxy(this.refresh, this), 3*60*1000); // 3 minutes
		setInterval($.proxy(this.refreshcover, this), 60*60*1000); // 3 minutes
		this.refresh();
		this.refreshcover();
	};

	Adressa.prototype.refreshcover = function() {

		var html = '<img src="http://static.buyandread.com/thumbnail/adresseavisen/adresseavisen.jpg" style="border-top: 1px solid #333; border-left: 1px solid #333; position: fixed; bottom: 0px; right: 0px" />';
		$("div#adressacover").empty().append(html)
		
		
	}

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