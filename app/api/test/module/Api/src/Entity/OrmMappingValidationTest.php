<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity;

use Doctrine\ORM\Tools\SchemaValidator;
use PHPUnit\Framework\TestCase;

/**
 * Validates the ORM attribute mappings for every entity (association owning/inverse
 * sides, identifier setup, referenced target entities and fields) without needing a
 * database connection. The database sync-check half of orm:validate-schema lives in
 * the integration suite (phpunit-integration.xml), which does need a real schema.
 *
 * Pre-existing errors (mostly entity-generator output: owning sides missing
 * inversedBy, and a few associations declaring RefData inverse fields that do not
 * exist) are recorded in orm-mapping-validation-baseline.txt so this test only
 * fails when NEW mapping errors are introduced. When fixing existing errors,
 * regenerate the baseline so the fix is locked in:
 *
 *   REGENERATE_ORM_MAPPING_BASELINE=1 vendor/bin/phpunit \
 *     test/module/Api/src/Entity/OrmMappingValidationTest.php
 */
class OrmMappingValidationTest extends TestCase
{
    private const BASELINE_FILE = __DIR__ . '/orm-mapping-validation-baseline.txt';

    public function testNoNewEntityMappingErrors(): void
    {
        // Reuses the standalone EntityManager built for phpstan-doctrine: real
        // metadata, custom DBAL types and DQL functions, but no live connection.
        $entityManager = require __DIR__ . '/../../../../../phpstan-object-manager.php';

        $errors = (new SchemaValidator($entityManager))->validateMapping();

        $current = [];
        foreach ($errors as $className => $classErrors) {
            foreach ($classErrors as $error) {
                $current[] = $className . ' | ' . $error;
            }
        }
        sort($current);

        if (getenv('REGENERATE_ORM_MAPPING_BASELINE')) {
            file_put_contents(self::BASELINE_FILE, implode(PHP_EOL, $current) . PHP_EOL);
            $this->assertFileExists(self::BASELINE_FILE);
            return;
        }

        $baseline = is_file(self::BASELINE_FILE)
            ? file(self::BASELINE_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
            : [];

        $this->assertSame(
            [],
            array_values(array_diff($current, $baseline)),
            'New Doctrine mapping errors were introduced (listed above). Fix the entity metadata rather than'
                . ' adding to the baseline - see the class docblock.'
        );
    }
}
