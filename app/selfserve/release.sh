#!/bin/sh

tar -czf ../release/olcs-selfserve/$VERSION.tar.gz \
composer.phar composer.json composer.lock init_autoloader.php \
config module public data/autoload data/cache vendor \
--exclude="config/autoload/local.php" --exclude="config/autoload/local.php.dist"
