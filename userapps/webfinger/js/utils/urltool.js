
/**
 * Various utilities for dealing with HTML, encoding and URLs.
 */

 define(
	function () {

		var urltool = {};


		urltool.escapeHTML = function(input) {
			return(
				input.replace(/&/g,'&amp;').replace(/>/g,'&gt;').replace(/</g,'&lt;').replace(/"/g,'&quot;')
	        );
		};


		urltool.addQueryParam = function (url, key, value) {
			var delimiter = ((url.indexOf('?') != -1) ? '&' : '?');
			if (url.charAt(url.length-1) === '?') {
				delimiter = '';
			}
			return url + delimiter + encodeURIComponent(key) + '=' + encodeURIComponent(value);
		}
		return urltool;
	}
);