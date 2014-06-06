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
			append('<iframe id="uwap-share-frame" style="width: 400px; height: 400px; border: 0px solid #ccc" src="http://feed.app.bridge.uninett.no/share.html"></iframe>');

		console.log("DEBUG", window.document, "t", t);
		if (!t) {
			console.error("Cannot find injected iframe, backing out..."); return;
		}
		var iframe = $("iframe#uwap-share-frame");
		var win = document.getElementById("uwap-share-frame").contentWindow;

		console.log("iframe, win", iframe, win);

		var url = document.location.href;
		var title = url;
		if ($("title").length > 0) {
			title = $("title").eq(0).text();
		}
		if ($("h1").length > 0) {
			title = $("h1").eq(0).text();
		}
		if ($("h2").length > 0) {
			title = $("h2").eq(0).text();
		}

 		var o = {
			"activity": {}
		};
		o.activity.verb = 'share';
		o.activity["object"] = {
			"objectType": "article",
			"url": url,
			"displayName": title,
			"hostname": window.location.hostname
		};


		iframe.on("load", function() {
			// console.log("iframe loaded...", this.contentWindow);
			
			setTimeout(function() {
				console.log("posting message", o);
				win.postMessage(o, "*");
			}, 1000);
		});

	});


});