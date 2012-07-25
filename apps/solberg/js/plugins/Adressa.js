define(function() {

	var Adressa = function(container) {
		this.container = container;
		this.url = 'http://www.adressa.no/nyheter/';
		this.cover = 0;
		this.covers = [
			'http://static.buyandread.com/thumbnail/adresseavisen/adresseavisen.jpg',
			'http://static.buyandread.com/thumbnail/verdensgang/verdensgang.jpg',
			'http://static.buyandread.com/thumbnail/dagbladet/dagbladet.jpg',
			'http://static.buyandread.com/thumbnail/helgelandsblad/helgelandsblad.jpg',
			'http://static.buyandread.com/thumbnail/laagendalsposten/laagendalsposten.jpg',
			'http://www.e-pages.dk/helgeland_no/teasers/small.jpg'
		];
	
		
		setInterval($.proxy(this.refresh, this), 3*60*1000); // 3 minutes
		setInterval($.proxy(this.refreshcover, this), 2*60*1000); // 3 minutes
		this.refresh();
		this.refreshcover();
	};

	Adressa.prototype.refreshcover = function() {
		++this.cover;
		var cu = this.covers[this.cover % this.covers.length];
		var html = '<img src="' + cu + '" style="border-top: 1px solid #333; border-bottom: none; border-left: 1px solid #333; position: fixed; bottom: 0px; right: 0px" />';
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