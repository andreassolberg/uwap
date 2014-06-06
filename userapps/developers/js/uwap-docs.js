// NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
// IT'S ALL JUST JUNK FOR OUR DOCS!
// ++++++++++++++++++++++++++++++++++++++++++


$(document).ready(function() {


	// make code pretty
	window.prettyPrint && prettyPrint();


	$(document).scroll(function(){

		console.log("Scroll");

		// If has not activated (has no attribute "data-top"
		if (!$('.subnav').attr('data-top')) {
			// If already fixed, then do nothing
			if ($('.subnav').hasClass('subnav-fixed')) return;
			// Remember top position
			var offset = $('.subnav').offset()
			$('.subnav').attr('data-top', offset.top);
		}

		if ($('.subnav').attr('data-top') - $('.subnav').outerHeight() <= $(this).scrollTop())
			$('.subnav').addClass('subnav-fixed');
		else
			$('.subnav').removeClass('subnav-fixed');
	});



});

