<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Expression\DecorateWillReturnMapWithExpectsMockRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\ValueObject\PhpVersion;

// NOTE: the previous Doctrine ORM upgrade sets (DoctrineSetList::DOCTRINE_ORM_*)
// were removed from this config. They operate on module/ entities and belong to
// the separate Doctrine upgrade ticket; this ticket only migrates the PHPUnit
// test suite and must not rewrite entity/form annotations.
return RectorConfig::configure()
    // Scoped to test/ only — keep rector away from production module/ code.
    ->withPaths([__DIR__ . '/test'])
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withPhpSets(php84: true)
    ->withSets([
        PHPUnitSetList::PHPUNIT_50,
        PHPUnitSetList::PHPUNIT_60,
        PHPUnitSetList::PHPUNIT_70,
        PHPUnitSetList::PHPUNIT_80,
        PHPUnitSetList::PHPUNIT_90,
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_110,
        PHPUnitSetList::PHPUNIT_120,
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    ->withSkip([
        // Rewrites `[$obj, 'method']` callable-arrays into first-class callables.
        // In tests these arrays are frequently *expected mock arguments* (data),
        // not callables to invoke — converting them to Closures breaks the
        // expectation match. Not needed for the PHPUnit upgrade.
        ArrayToFirstClassCallableRector::class,
        // Decorates willReturnMap stubs with expects(exactly(N)) inferred from the map
        // size; breaks when not every mapped entry is consumed by the code path.
        DecorateWillReturnMapWithExpectsMockRector::class,
    ]);
