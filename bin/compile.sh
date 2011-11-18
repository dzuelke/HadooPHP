#!/bin/sh
php -D phar.readonly=0 `dirname $0`/compile.php $*
