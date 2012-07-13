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







$ac = new AppDirectory();
$listing = $ac->getAllApps();


foreach($listing["app"] AS $app) {


	$current = Config::getInstance($app["id"]);
	$config = $current->getConfig();

	if (empty($config['uwap-userid'])) {
		Utils::cliLog($app['id'], "Skipping [" . $config["name"] . "] without owner.");
		// print_r($config);
		continue;
	}

	$p = $current->getAppPath('/');
	$credentials = $current->getDavCredentials();

	Utils::cliLog($app['id'], "Processing " . $config["name"]);
	

	if (is_dir($p)) {
		// Utils::cliLog($app['id'], " Directory " . $p . " exists.");
	} else {
		mkdir($p);
		chmod($p, 0777);
		Utils::cliLog($app['id'], " Creating dir " . $p . "");
		UWAPLogger::info('bin-update', " Creating dir " . $p . "");
	}

	$hta = $p . '.htaccess';

	if (file_exists($hta )) {
		// echo " .htaccess file exists\n";
	} else {
		Utils::cliLog($app['id'], " Creating file " . $hta . "");
		file_put_contents($hta, "Require user " . $credentials["username"] . "\n");
		chmod($hta, 0644);
		UWAPLogger::info('bin-update', "Adding .htpasswd file. Require user " . $credentials["username"] . "\n");
	}

	if ($current->hasStatus(array('pendingDAV'))) {
		$current->updateStatus(array('pendingDAV' => false, 'operational' => true));
		Utils::cliLog($app['id'], "Setting WebApp status from pendingDAV to operational");
		UWAPLogger::info('bin-update', "Setting WebApp status from pendingDAV to operational");
	}

	// print_r($credentials);
	// echo json_encode($app) . "\n";


}



