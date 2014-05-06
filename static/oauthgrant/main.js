require( ['jquery', 'uwap-core/bootstrap3/js/bootstrap', 'uwap-core/bootstrap3/js/modal', 'uwap-core/bootstrap3/js/dropdown'], function( $ ) {

	$(document).ready(function() {
		$('#submit').on('click', function() {
			$("form").submit();
		});
	});

});
