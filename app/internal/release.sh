#!/bin/sh

tar -czf ../release/olcs-internal/$VERSION.tar.gz \
composer.phar composer.json composer.lock config module public data/autoload data/cache vendor \
--exclude="config/autoload/local.php" --exclude="config/autoload/local.php.dist"
