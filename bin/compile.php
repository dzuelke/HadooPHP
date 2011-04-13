#!/usr/bin/env php
<?php

if($_SERVER['argc'] < 3) {
	echo "Usage: " . basename(__FILE__) . " <path-to-jobs-folder> <path-to-output-folder>\n\n";
	echo "Options:\n";
	echo "  -i <path>    Directory to package with phar\n\n";
	exit(1);
}

if(!Phar::canWrite()) {
	echo "Phar write mode not allowed; disable phar.readonly in php.ini\n\n";
	exit(2);
}

$jobdir = realpath($_SERVER['argv'][$_SERVER['argc']-2]);
$jobname = basename($jobdir);
$builddir = realpath($_SERVER['argv'][$_SERVER['argc']-1]);
$jobphar = "$builddir/$jobname.phar";
$jobsh = "$builddir/$jobname.sh";

$phar = new Phar($jobphar, RecursiveDirectoryIterator::CURRENT_AS_FILEINFO | RecursiveDirectoryIterator::KEY_AS_FILENAME, 'my.phar');
$phar->startBuffering();
$phar->setStub("#!/usr/bin/env php\n" . $phar->createDefaultStub('run.php', 'run.php'));
// for envs without phar, this will work and not create a checksum error, but invocation needs to be "php archive.phar then":
// $phar->setStub($phar->createDefaultStub('run.php', 'run.php'));
$phar->buildFromDirectory(__DIR__ . '/../lib/'); 
$phar->buildFromDirectory($jobdir);
$opts = getopt('i:');
if(isset($opts['i'])) {
	foreach((array)$opts['i'] as $path) {
		$phar->buildFromDirectory(realpath($path));
	}
}
$phar->stopBuffering();

if(file_exists("$jobdir/ARGUMENTS")) {
	$mapRedArgs = trim(file_get_contents("$jobdir/ARGUMENTS"));
} else {
	$mapRedArgs = array();
	// must do reducer first because of the fallback... -D args must be listed first or hadoop-streaming.jar complains
	if(file_exists("$jobdir/Reducer.php")) {
		$mapRedArgs[] = "-reducer 'php -d detect_unicode=off $jobname.phar reducer'";
	} else {
		$mapRedArgs[] = "-D mapred.reduce.tasks=0";
	}
	if(file_exists("$jobdir/Combiner.php")) {
		$mapRedArgs[] = "-combiner 'php -d detect_unicode=off $jobname.phar combiner'";
	}
	$mapRedArgs[] = "-mapper 'php -d detect_unicode=off $jobname.phar mapper'";
	$mapRedArgs = implode(" \\\n", $mapRedArgs) . " \\";
}

file_put_contents($jobsh, sprintf('#!/bin/sh
confswitch=""
while getopts ":c:" opt; do
	case $opt in
		c) confswitch="--config $OPTARG";;
		\?) echo "Invalid option: -$OPTARG"; exit 1;;
		:) echo "Option -$OPTARG requires an argument."; exit 1;;
	esac
done
shift $((OPTIND-1))

if [ $# -lt 2 ]
then
	echo "Usage: $0 HDFSINPUTDIR ... HDFSOUTPUTDIR"
	exit 1
fi

input=""
output=""
index=0
for path in $*
do
	index=`expr $index + 1`
	if [ $index -ne $# ]
	then
		input=$input" -input $path"
	else
		output="-output $path"
	fi
done

if [ $HADOOP_HOME ]
then
	hadoop=$HADOOP_HOME/bin/hadoop
	streaming=$HADOOP_HOME"/contrib/streaming/hadoop-streaming-*.jar"
else
	hadoop="hadoop"
	streaming="/usr/lib/hadoop/contrib/streaming/hadoop-streaming-*.jar"
fi
dir=`dirname $0`

$hadoop $confswitch jar $streaming \\
%s
$input \\
$output \\
-file $dir/%s.phar
', $mapRedArgs, $jobname));

echo "
Build done, generated files:
  $jobsh
  $jobphar

If you re-built the job, make sure to check the modifications in $jobname.sh

Do not forget to chmod
  $jobname.sh
and
  $jobname.phar
to be executable before checking in.

";

?>