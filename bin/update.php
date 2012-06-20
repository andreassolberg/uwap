#!/usr/bin/env php
<?php


$uwapBaseDir = dirname(dirname(__FILE__));
$autoload = $uwapBaseDir . '/lib/autoload.php';

# error_log("Checking basedir: " . $autoload); exit;

/* Add library autoloader. */
require_once($autoload);

$program = array_shift($argv);
if (count($argv) > 0) {
	echo "Wrong number of parameters. Run:   " . $program . " .... TBD\n"; 
	exit(2);
}



function crypt_apr1_md5($plainpasswd) {
    $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
    $len = strlen($plainpasswd);
    $text = $plainpasswd.'$apr1$'.$salt;
    $bin = pack("H32", md5($plainpasswd.$salt.$plainpasswd));
    for($i = $len; $i > 0; $i -= 16) { $text .= substr($bin, 0, min(16, $i)); }
    for($i = $len; $i > 0; $i >>= 1) { $text .= ($i & 1) ? chr(0) : $plainpasswd{0}; }
    $bin = pack("H32", md5($text));
    for($i = 0; $i < 1000; $i++) {
        $new = ($i & 1) ? $plainpasswd : $bin;
        if ($i % 3) $new .= $salt;
        if ($i % 7) $new .= $plainpasswd;
        $new .= ($i & 1) ? $bin : $plainpasswd;
        $bin = pack("H32", md5($new));
    }
    for ($i = 0; $i < 5; $i++) {
        $k = $i + 6;
        $j = $i + 12;
        if ($j == 16) $j = 5;
        $tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
    }
    $tmp = chr(0).chr(0).$bin[11].$tmp;
    $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
    "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
    return "$"."apr1"."$".$salt."$".$tmp;
}

function h($u, $p) {
	$hash = base64_encode(sha1($p, true));
	// return crypt(crypt($p, base64_encode($p)));
	// return crypt($p,rand(10000, 99999));
	$realm = 'UWAP';
	return $realm . ':' . md5($u . ':' . $realm . ':' .$p);
	return crypt_apr1_md5($p);
	return '{SHA}'.$hash;
}


$store = new UWAPStore();
$lookup = $store->queryList('davcredentials', array(), array('username', 'password'));

$pwdfile = '';

foreach($lookup AS $up) {
	$line = $up['username'] . ':' . h($up['username'], $up['password']) . "\n";
	$pwdfile .= $line;
}

// echo $pwdfile;
// echo "store to " . $uwapBaseDir . '/passwords'; exit;
file_put_contents($uwapBaseDir . '/passwords', $pwdfile);

// print_r($lookup);





$ac = new Config(false);
$listing = $ac->getAllApps();


foreach($listing["app"] AS $app) {


	$current = new Config($app["id"]);
	$config = $current->getConfig();

	if (empty($config['uwap-userid'])) {
		echo "Skipping [" . $config["name"] . "] without owner.\n";
		print_r($config);
		continue;
	}

	$p = $current->getAppPath();
	$credentials = $current->getDavCredentials();


	echo "\n\n------------------------------------\n";
	echo "Processing [" . $app["name"] . "]\n";

	

	if (is_dir($p)) {
		echo " Dir " . $p . " exists.\n";
	} else {
		mkdir($p);
		echo " Dir " . $p . " does not exists.\n";
	}

	$hta = $p . '.htaccess';

	if (file_exists($hta )) {
		echo " .htaccess file exists\n";
	} else {
		echo " .htaccess file does not exists\n";
		file_put_contents($hta, "Require user " . $credentials["username"] . "\n");
	}

	// print_r($credentials);
	// echo json_encode($app) . "\n";



}






