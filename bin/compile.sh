#!/bin/sh
php -d phar.readonly=0 `dirname $0`/compile.php $*
