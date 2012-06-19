# Notes on installing UWAP

Basic setup

	apt-get install memcached apache2 php5

	cd /var/www
	git clone uwap


	cd /var/www/uwap/
	
PHP MongoDB Drivers

	apt-get install php-pear php5-dev
	pecl install mongo


Setup DAV.

	a2enmod dav_fs
	a2enmod dav


Setup apache + ssl

	/etc/ssl/localcerts

Selfsigned, so far. Waiting for uwap.org certs.






