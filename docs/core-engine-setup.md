# Notes on the arcitecture of UWAP

The Core server

	ServerName app.bridge.uninett.no
	DocumentRoot /var/www/appengine/static/	
	Alias /login /var/www/appengine/engine/login-core.php
	Alias /simplesaml /var/www/appengine/simplesamlphp/www/

Each app

	ServerAlias *.app.bridge.uninett.no
	DocumentRoot /var/www/appengine/static/	
	Alias /_/api /var/www/appengine/engine/api
	Alias /_/js /var/www/appengine/engine/js	
	Alias /_/login /var/www/appengine/engine/login.php
	Alias / /var/www/appengine/engine/engine.php/


