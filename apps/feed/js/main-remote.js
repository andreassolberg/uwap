/*
 * This is the main js app for the to be loaded remotely for share widget.
 * It loads an iframe and communiacates with it.
 * 
 */

define(function(require, exports, module) {

	var 

		$ = require('jquery'),
		UWAP = require('uwap-core/js/core'),
		jso = require('uwap-core/js/oauth'),

		moment = require('uwap-core/js/moment'),
		prettydate = require('uwap-core/js/pretty')
		;


	$(document).ready(function() {

		console.log("Share app is running...");
		
		var t = $("div#uwap-share-container").
			append('<iframe id="uwap-share-frame" style="width: 400px; height: 400px; border: 1px solid #ccc" src="http://feed.app.bridge.uninett.no/share.html"></iframe>');

		console.log("DEBUG", window.document, "t", t);
		if (!t) {
			console.error("Cannot find injected iframe, backing out..."); return;
		}
		var win = t.contentWindow;

		$("iframe#uwap-share-container").on("load", function() {
			console.log("iframe loaded...", this.contentWindow);
			
			setTimeout(function() {
				console.log("posting message");
				win.postMessage({"message": "foo"}, "*");
			}, 5000);
		});

	});


});