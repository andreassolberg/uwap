# UNINETT WebApp Park


app.bridge.uninett.no
	Alias /login /var/www/appengine/engine/login-core.php
	Alias /simplesaml /var/www/appengine/simplesamlphp/www/


*.app.bridge.uninett.no
	Alias /_/api /var/www/appengine/engine/api
	Alias /_/js /var/www/appengine/engine/js
	Alias /_/login /var/www/appengine/engine/login.php
	Alias / /var/www/appengine/engine/engine.php/


The endpoints:

/_/api is used by the UWAP core API to communicate with the server with the same origin.
/_/js is hosting of the Core javascript library (on same domain). May be this should be moved to common domain?
/_/login endpoint to login the user, UWAP core is redirecting the user here.
/ engine/engin.php is processing each file and pushing it to the user.

An app runs at `http://test.app.bridge.uninett.no/`

Container HTML refers an js API:

	<script type="text/javascript" src="/_/js/core.js"></script>

The script is located here: /var/www/appengine/engine/js

The script communicates with the endpoints under:

	http://test.app.bridge.uninett.no/_/api/*

APIS:

For authentication, redirect to:
	/_/login with the return parameter.

For data requsts, AJAX to: /_/api/data.php?url=

For OAuth requests, AJAX to: /_/api/dataoauth.php?url='
If this returns status=redirect, then redirect the user.

The data APIs, is located here: /engine/api/*
