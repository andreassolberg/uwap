define(function() {

	var Yr = function(container) {
		this.container = container;
		this.url = 'http://www.yr.no/sted/Norge/Sør-Trøndelag/Trondheim/Trondheim/meteogram.png';
		$(this.container).append('<img  class="yr img-responsive" src="' + this.url + '"></img>');
		
		setInterval($.proxy(this.refresh, this), 3*60*1000); // 3 minutes
	};

	Yr.prototype.refresh = function() {

		$(this.container).find("img.yr")
			.attr("src", this.url + "?rand=" + Math.random());
	}

	return Yr;

});