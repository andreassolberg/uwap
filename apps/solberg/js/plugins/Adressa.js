define(function() {

	var Adressa = function(container) {
		this.container = container;
		this.url = 'http://m.adressa.no';
		$(this.container).append('<iframe class="adressa" src="' + this.url + '"></iframe>');
		
		setInterval($.proxy(this.refresh, this), 3*60*1000); // 3 minutes
	};

	Adressa.prototype.refresh = function() {
		$(this.container).find("iframe.adressa").attr("src", this.url + "?rand=" + Math.random());
	}

	return Adressa;

});