<?php

/**
 * EntityManager loader for phpstan-doctrine (parameters.doctrine.objectManagerLoader).
 *
 * Builds an EntityManager from the entity attribute mappings alone so PHPStan can
 * validate column/association types against real ORM metadata. Deliberately avoids
 * the MVC bootstrap: that requires a live database, and pinning serverVersion below
 * means DBAL never opens a connection either — so analysis works in CI with no DB.
 *
 * The custom DBAL types and DQL functions registered here must match the doctrine
 * configuration in module/Api/config/module.config.php and config/autoload/global.php.
 */

chdir(__DIR__);
require 'vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

$types = [
    'yesno' => \Dvsa\Olcs\Api\Entity\Types\YesNoType::class,
    'yesnonull' => \Dvsa\Olcs\Api\Entity\Types\YesNoNullType::class,
    'date' => \Dvsa\Olcs\Api\Entity\Types\DateType::class,
    'datetime' => \Dvsa\Olcs\Api\Entity\Types\DateTimeType::class,
    'encrypted_string' => \Dvsa\Olcs\Api\Entity\Types\EncryptedStringType::class,
];
foreach ($types as $name => $class) {
    Type::hasType($name) ? Type::overrideType($name, $class) : Type::addType($name, $class);
}

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__ . '/module/Api/src/Entity'],
    isDevMode: true,
);

$config->setCustomDatetimeFunctions([
    'date' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'time' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'timestamp' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'convert_tz' => \Oro\ORM\Query\AST\Functions\DateTime\ConvertTz::class,
]);
$config->setCustomNumericFunctions([
    'timestampdiff' => \Oro\ORM\Query\AST\Functions\Numeric\TimestampDiff::class,
    'dayofyear' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'dayofmonth' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'dayofweek' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'week' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'day' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'hour' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'minute' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'month' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'quarter' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'second' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'year' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'sign' => \Oro\ORM\Query\AST\Functions\Numeric\Sign::class,
    'pow' => \Oro\ORM\Query\AST\Functions\Numeric\Pow::class,
]);
$config->setCustomStringFunctions([
    'md5' => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
    'group_concat' => \Oro\ORM\Query\AST\Functions\String\GroupConcat::class,
    'cast' => \Oro\ORM\Query\AST\Functions\Cast::class,
    'concat_ws' => \Oro\ORM\Query\AST\Functions\String\ConcatWs::class,
    'replace' => \Oro\ORM\Query\AST\Functions\String\Replace::class,
    'date_format' => \Oro\ORM\Query\AST\Functions\String\DateFormat::class,
    'ifnull' => \DoctrineExtensions\Query\Mysql\IfNull::class,
]);

$connection = DriverManager::getConnection(
    ['driver' => 'pdo_mysql', 'serverVersion' => '8.0'],
    $config,
);

return new EntityManager($connection, $config);
