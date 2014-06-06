# Adobe Connect Widget



TODO: Update this to refelect the ac widget, not the feed widget.


## Share Widget

To share, include this:

	<!-- UWAP Share Widget -->
	<div id="uwap-share-container"></div>
	<script type="text/javascript" data-main="main-remote" 
	 src="http://feed.app.bridge.uninett.no/_/js/require.js"></script>
	<!-- /UWAP Share Widget -->

The remote share script located here:

	http://feed.app.bridge.uninett.no/js/main-remote.js

This script will communicate fromt he remote site, and look include an iframe in the `<div id="uwap-share-container"></div>`. Like this:

	<iframe id="uwap-share-frame" style="width: 400px; height: 400px; border: 1px solid #ccc" 
		src="http://feed.app.bridge.uninett.no/widget.html"></iframe>

The iframe again loads the main js: 

	http://feed.app.bridge.uninett.no/js/main-share-widget.js





## Feed Widget

iframe with:


	<iframe id="uwap-share-frame" style="width: 400px; height: 400px; border: 1px solid #ccc" 
		src="http://feed.app.bridge.uninett.no/widget.html#!/"></iframe>













