#!/usr/bin/env php
<?php


$uwapBaseDir = dirname(dirname(__FILE__));
$autoload = $uwapBaseDir . '/lib/autoload.php';

# error_log("Checking basedir: " . $autoload); exit;

/* Add library autoloader. */
require_once($autoload);

UWAPLogger::init(array('logLevel' => 3));
UWAPLogger::info('bin-update', 'Running command line update.php cron script.');



$program = array_shift($argv);
if (count($argv) > 0) {
	echo "Wrong number of parameters. Run:   " . $program . " .... TBD\n"; 
	exit(2);
}




/*
 * Remove logentries that are older than (3 hours)
 */
$removebefore = microtime(true) - (3*60*60); // 3 hours ago
$query =  array(
	'time' => array(
		'$lt' => $removebefore
	)
);

UWAPLogger::info('bin-update', 'Removing old logs', $query);

$store = new UWAPStore();
$store->remove('log', null, $query);
// ---- o ---- o ---- o ---- o ---- o ---- o ---- o ---- o 








/*
 * Generate the .htpasswd file.
 */

$store = new UWAPStore();
$lookup = $store->queryList('davcredentials', array(), array('username', 'password'));

$pwdfile = '';

foreach($lookup AS $up) {
	$line = $up['username'] . ':' . Utils::encodeUserPass($up['username'], $up['password']) . "\n";
	$pwdfile .= $line;
}

file_put_contents($uwapBaseDir . '/passwords', $pwdfile);
// ---- o ---- o ---- o ---- o ---- o ---- o ---- o ---- o 



$ac = new ClientDirectory(null);
$listing = $ac->getAllApps();

// print_r($listing->getJSON()); exit;


foreach($listing->getItems() AS $app) {


	// print_r($app->getJSON()); exit;

	// $current = Config::getInstance($app["id"]);
	// $config = $current->getConfig();

	if (!$app->has('uwap-userid')) {
		Utils::cliLog($app->get('id'), "Skipping [" . $app->get('name') . "] without owner.");
		// print_r($config);
		continue;
	}




	if (!$app instanceof App) {
		Utils::cliLog($app->get('id'), "Skipping app that is not an app" . $app->get('name'));
		continue;
	}

	$p = $app->getAppPath('/');

	$apphosting = new AppHosting($app->getOwner());
	$credentials = $apphosting->getDavCredentials($app);


	Utils::cliLog($app->get('id'), "Processing " . $app->get("name") . " at " . $p);



	if (is_dir($p)) {
		// Utils::cliLog($app['id'], " Directory " . $p . " exists.");
	} else {
		mkdir($p);
		chmod($p, 0777);
		Utils::cliLog($app->get('id'), " Creating dir " . $p . "");
		UWAPLogger::info('bin-update', " Creating dir " . $p . "");
	}

	$hta = $p . '.htaccess';


	// print_r($credentials);
	// exit;	

	if (file_exists($hta )) {
		// echo " .htaccess file exists\n";
	} else {
		Utils::cliLog($app->get('id'), " Creating file " . $hta . "");
		file_put_contents($hta, "Require user " . $credentials["username"] . "\n");
		chmod($hta, 0644);
		UWAPLogger::info('bin-update', "Adding .htpasswd file. Require user " . $credentials["username"] . "\n");
	}

	if ($app->hasStatus(array('pendingDAV'))) {
		$app->updateStatus(array('pendingDAV' => false, 'operational' => true));
		Utils::cliLog($app->get('id'), "Setting WebApp status from pendingDAV to operational");
		UWAPLogger::info('bin-update', "Setting WebApp status from pendingDAV to operational");
	}

	// print_r($credentials);
	// echo json_encode($app) . "\n";


}



