<?php

/**
 * EntityManager loader for phpstan-doctrine (parameters.doctrine.objectManagerLoader).
 *
 * Builds an EntityManager from the entity attribute mappings alone so PHPStan can
 * validate column/association types against real ORM metadata. Deliberately avoids
 * the MVC bootstrap: that requires a live database, and pinning serverVersion below
 * means DBAL never opens a connection either — so analysis works in CI with no DB.
 *
 * The custom DBAL types, entity paths and DQL functions are read from the same
 * config files the application uses, rather than restated here, so the two cannot
 * drift apart:
 *
 *   module/Api/config/module.config.php  doctrine.types, doctrine.driver
 *   config/autoload/global.php           doctrine.configuration.orm_default
 *
 * Both are plain arrays with no environment or container lookups, so they can be
 * required directly without the MVC bootstrap — which is the point, since that
 * bootstrap needs AWS and a database.
 */

chdir(__DIR__);
require 'vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

$moduleConfig = require __DIR__ . '/module/Api/config/module.config.php';
$globalConfig = require __DIR__ . '/config/autoload/global.php';
$ormConfig    = $globalConfig['doctrine']['configuration']['orm_default'];

foreach ($moduleConfig['doctrine']['types'] as $name => $class) {
    Type::hasType($name) ? Type::overrideType($name, $class) : Type::addType($name, $class);
}

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: $moduleConfig['doctrine']['driver']['EntityDriver']['paths'],
    isDevMode: true,
);

$config->setCustomDatetimeFunctions($ormConfig['datetime_functions']);
$config->setCustomNumericFunctions($ormConfig['numeric_functions']);
$config->setCustomStringFunctions($ormConfig['string_functions']);

// Not a preference: ORM 3 proxies through either Symfony's LazyGhost or PHP native
// lazy objects, and symfony/var-exporter 8 removed LazyGhost, so this is the only
// strategy left. Passing false makes EntityManager construction throw outright.
// Roave reaches the same value on its own — its ConfigurationFactory defaults
// enable_native_lazy_objects to PHP_VERSION_ID >= 80400 — so runtime matches
// without any config key, and there is no key here to keep in step with it.
$config->enableNativeLazyObjects(true);

$connection = DriverManager::getConnection(
    ['driver' => 'pdo_mysql', 'serverVersion' => '8.0'],
    $config,
);

return new EntityManager($connection, $config);
