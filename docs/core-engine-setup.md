# Notes on the arcitecture of UWAP

The Core server

	ServerName core.uwap.org
	DocumentRoot /var/www/uwap/static/	
	Alias /login /var/www/uwap/engine/login-core.php
	Alias /simplesaml /var/www/uwap/simplesamlphp/www/

DAV access
	
	dav.uwap.org


Each app

	ServerAlias *.uwap.org
	DocumentRoot /var/www/uwap/static/	
	Alias /_/api /var/www/uwap/engine/api
	Alias /_/js /var/www/uwap/engine/js	
	Alias /_/login /var/www/uwap/engine/login.php
	Alias / /var/www/uwap/engine/engine.php/



