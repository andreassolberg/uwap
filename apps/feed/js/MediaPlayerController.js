define([

], function() {


	(function($){
	     $.fn.extend({
	          center: function (options) {
	               var options =  $.extend({ // Default values
	                    inside:window, // element, center into window
	                    transition: 0, // millisecond, transition time
	                    minX:0, // pixel, minimum left element value
	                    minY:0, // pixel, minimum top element value
	                    vertical:true, // booleen, center vertical
	                    withScrolling:true, // booleen, take care of element inside scrollTop when minX < 0 and window is small or when window is big
	                    horizontal:true // booleen, center horizontal
	               }, options);
	               return this.each(function() {
	                    var props = {position:'absolute'};
	                    if (options.vertical) {
	                         var top = ($(options.inside).height() - $(this).outerHeight()) / 2;
	                         if (options.withScrolling) top += $(options.inside).scrollTop() || 0;
	                         top = (top > options.minY ? top : options.minY);
	                         $.extend(props, {top: top+'px'});
	                    }
	                    if (options.horizontal) {
	                          var left = ($(options.inside).width() - $(this).outerWidth()) / 2;
	                          if (options.withScrolling) left += $(options.inside).scrollLeft() || 0;
	                          left = (left > options.minX ? left : options.minX);
	                          $.extend(props, {left: left+'px'});
	                    }
	                    if (options.transition > 0) $(this).animate(props, options.transition);
	                    else $(this).css(props);
	                    return $(this);
	               });
	          }
	     });
	})(jQuery);


	var MediaPlayerController = function(el) {
		var that = this;
		this.el = el;

		this.el.append('<div id="player" style="display: none"></div>');
		this.el.append('<div id="opaque" style="display: none"></div>');
		
		this.el.on("click", ".videoitem", $.proxy(this.actPlay, this));

		this.el.on("click", "#opaque", function() {
 			that.close();
 		});
		this.el.on("keyup", function(e) {
 			if (e.keyCode == 27) {
 				that.close();
			}
 		});

	} 

	MediaPlayerController.prototype.close = function(e) {
		this.el.find("#opaque").hide();
		this.el.find("#player").empty();
	}

	MediaPlayerController.prototype.actPlay = function(e) {
 		e.preventDefault();

 		console.log("Click on video item");

 		var el = $(e.currentTarget).closest('.item');
 		var item = el.tmplItem().data;

 		console.log('Click on item ', item);
 		this.play(item.media);
	}

	MediaPlayerController.prototype.play = function(item) {
 		this.el.find("#opaque").show();
 		this.el.find("#player").show();

 		var h = $("#videoplayer").tmpl(item);

 		if (item.width > $(document).width()) {
	 		$(h).find("video")
	 			.css("top", "0px")
	 			.css("left", "0px");
	 			// .css("margin-left", "-" + Math.floor(item.media.width / 2) + "px")
	 			// .css("margin-top", "-" + Math.floor(item.media.height / 2) + "px");
 		} else {
	 		$(h).find("video")
	 			.css("top", "50%")
	 			.css("left", "50%")
	 			.css("margin-left", "-" + Math.floor(item.width / 2) + "px")
	 			.css("margin-top", "-" + Math.floor(item.height / 2) + "px");
 		}
 		this.el.find("#player").empty().append(h);
	}

	return MediaPlayerController;

});

