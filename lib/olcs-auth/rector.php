<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Class_\NarrowUnusedSetUpDefinedPropertyRector;
use Rector\Php84\Rector\Class_\DeprecatedAnnotationToDeprecatedAttributeRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\RemoveNeverUsedMockPropertyRector;
use Rector\PHPUnit\CodeQuality\Rector\Expression\DecorateWillReturnMapWithExpectsMockRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    // Scoped to test/ only: this ticket migrates the PHPUnit test suite. Production
    // src/ code (Doctrine entities, forms, validators) carries annotations that are
    // converted to attributes under separate tickets — keep rector away from them.
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
        // Mockery/PHPUnit expectation match. Not needed for the PHPUnit upgrade.
        ArrayToFirstClassCallableRector::class,
        // Decorates willReturnMap stubs with expects(exactly(N)) inferred from the map
        // size; breaks when not every mapped entry is consumed by the code path.
        DecorateWillReturnMapWithExpectsMockRector::class,
        // Strip "unused" mock properties / setUp assignments. Unsafe for tests
        // whose data providers reference a mock by its string property name
        // (dynamic this-> access): rector can't see the use, deletes the
        // property, and the test fatals with "undefined property". Keep them.
        NarrowUnusedSetUpDefinedPropertyRector::class,
        RemoveNeverUsedMockPropertyRector::class,
        // Leave @deprecated PHPDoc as-is. Rewriting it to the PHP 8.4
        // #[\Deprecated] attribute changes runtime behaviour: calling the
        // symbol then emits E_USER_DEPRECATED, which failOnDeprecation turns
        // into a failure. These are internal "use the new test format"
        // markers on helpers that ARE called, not runtime deprecations.
        DeprecatedAnnotationToDeprecatedAttributeRector::class,
    ]);
