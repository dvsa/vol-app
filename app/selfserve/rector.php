<?php

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/module', __DIR__ . '/test'])
    ->withPhpVersion(PhpVersion::PHP_83)
    ->withPhpSets($php83 = true)
    ->withSets(
        [
            PHPUnitSetList::PHPUNIT_50,
            PHPUnitSetList::PHPUNIT_60,
            PHPUnitSetList::PHPUNIT_70,
            PHPUnitSetList::PHPUNIT_80,
            PHPUnitSetList::PHPUNIT_90,
            PHPUnitSetList::PHPUNIT_100,
            PHPUnitSetList::PHPUNIT_110,
            PHPUnitSetList::PHPUNIT_120,
        ]
    );
