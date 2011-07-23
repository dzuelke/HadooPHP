<?php

if($_SERVER['argc'] != 2 || !in_array($mode = strtolower($_SERVER['argv'][1]), array('mapper', 'combiner', 'reducer'))) {
	echo "Usage: " . $_SERVER['argv'][0] . " MODE\n\nMODE can be 'mapper', 'combiner' or 'reducer'\n\n";
	exit(1);
}

if(!defined('HADOOPHP_DEBUG')) {
	define('HADOOPHP_DEBUG', false);
}

$job = basename($argv[0], '.phar');
$mode = ucfirst(strtolower($mode));
require($mode . '.php');
$worker = new $mode();
$worker->handle();

?>