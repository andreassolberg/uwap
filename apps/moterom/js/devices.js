define(function(require, exports, module) {

	var 
		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		moment = require('uwap-core/js/moment'),
//		moment = require('moment')
		hogan = require('uwap-core/js/hogan')
		;
	
	
	function checkInterval(){
		setInterval(checkUpdates, 30000);
	}
	function checkUpdates(){
		UWAP.store.queryList({"type": "meetUpdate"}, function(d){
				console.log(d);
				$('#devices').empty();
				$.each(d, function(i,v){
					console.log(v);
					var el = $('#devices').append('<span><strong>'+v.device.name + '</strong> monitoring room <strong>'+v.device.room+'</strong> updated status at <strong>'+v.lastUpdate+'</strong></span>');
					if(v.lastUpdate<moment().subtract('minutes', 2).format('YYYY-MM-DD HH:mm:ss')){
						el.append('<strong class="dead"> dead</strong><br />');
					}
					else{
						el.append('<strong class="alive"> alive</strong><br />');
					}
						
				});
			}, function (err){
				console.log(err);
		});
	}
	
	$(document).ready(function() {
		checkInterval();
		checkUpdates();
	});
});