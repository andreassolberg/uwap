define(function(require, exports, module) {
	
	var Adressa = function(container) {
		this.container = container;
		this.url = 'http://www.adressa.no/nyheter/';
		this.cover = 0;
		this.loaded = false;
		this.covers = [
			'http://static.buyandread.com/thumbnail/adresseavisen/adresseavisen.jpg',
			'http://static.buyandread.com/thumbnail/verdensgang/verdensgang.jpg',
			'http://static.buyandread.com/thumbnail/dagbladet/dagbladet.jpg',
			'http://static.buyandread.com/thumbnail/helgelandsblad/helgelandsblad.jpg',
			'http://static.buyandread.com/thumbnail/laagendalsposten/laagendalsposten.jpg',
			'http://www.e-pages.dk/helgeland_no/teasers/small.jpg'
		];
	
		setInterval($.proxy(this.loadVGP, this), 60*60*1000); // 60 minutes
		setInterval($.proxy(this.refresh, this), 3*60*1000); // 3 minutes
		setInterval($.proxy(this.refreshcover, this), 10*1000); // 10 seconds

		this.refresh();
		this.refreshcover();
		this.loadVGP();
	};



	Adressa.prototype.loadVGP = function() {
		var that = this;

		var replace = [];

		for(var i = 0; i < this.covers.length; i++) {
			if (!this.covers[i].match(/vg/)) {
				replace.push(this.covers[i]);
			}
		}
		this.covers = replace;

		var vgurl = 'http://pluss.vg.no/utgaver';
		UWAP.data.get(vgurl,  null, function(data) {
			var obj = $(data);
			var imgurl = obj.find("li a img").eq(0).attr('src');

			that.covers.push(imgurl);
			console.log("COVER LIST IS ", that.covers);
		});

	}


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