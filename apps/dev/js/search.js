define(function(require, exports, module) {
	
	var 
		$ = require('jquery')
		;
	var that = this;
	/*
		Initialise the search box.
		*/

	var waiter = function (setCallback) {
		var my = {};

		// Number of milliseconds to wait for more events.
		my.delay = 400;
		my.counter = 0;

		// Call back to fire, when the waiter is pinged, and waited for the timeout 
		// (without subsequent events).
		my.callback = setCallback;

		// Ping
		function ping (event) {
			//console.log('Search box detected a change. Executing refresh...')
			my.counter++;
			setTimeout(function() {
				if (--my.counter === 0) {
					my.callback(event);
				}
			}, my.delay);
		}

		my.ping = ping;
		return my;
	}

	var performSearch = function(searchfield, callback, getSelection) {


		return waiter(function(event) {

			var term = searchfield.val();

			// Will not perform a search when search term is only one character..
			if (term.length === 1) return; 

			console.log("Perform a search on term " + term);
			callback(term, getSelection());
		});

	};




	$.fn.uwapsearch = function( callback, getSelection ) {  

		return this.each(function() {

			var $this = $(this);

			var ps = performSearch($this, callback, getSelection);

			// this.parent.Utils.log(this.ui.popup.find("input.discojuice_search"));
			$this.keydown(function (event) {
			 	var 
					charCode, term;

			    if (event && event.which){
			        charCode = event.which;
			    } else if(window.event){
			        event = window.event;
			        charCode = event.keyCode;
			    }

			    if(charCode == 13) {
					// Enter? DO anything special then?
					return;
			    }
			    if(charCode == 27) {
					// Escape? Wanna do anything special then?
					return;
			    }

				ps.ping(event);
			});
			$this.change(ps.ping);
			$this.mousedown(ps.ping);

		});

	};
	


});

