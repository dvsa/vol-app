<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Schema;

use Doctrine\ORM\Tools\SchemaTool;
use Dvsa\OlcsTest\Integration\IntegrationTestCase;

/**
 * Compares the entity metadata against the real (Liquibase-migrated) database
 * schema - the sync-check half of orm:validate-schema. Pre-existing drift is
 * recorded in schema-drift-baseline.txt; the test fails only when NEW drift is
 * introduced (an entity change with no matching migration, or vice versa).
 *
 * When resolving drift, or after a legitimate coordinated entity + migration
 * change, regenerate the baseline:
 *
 *   REGENERATE_SCHEMA_DRIFT_BASELINE=1 vendor/bin/phpunit \
 *     -c phpunit-integration.xml --filter SchemaDriftTest
 */
class SchemaDriftTest extends IntegrationTestCase
{
    private const BASELINE_FILE = __DIR__ . '/../../schema-drift-baseline.txt';

    public function testNoNewSchemaDrift(): void
    {
        $entityManager = $this->em();
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        // The entity generator emits every unique key as both #[ORM\Index] and
        // #[ORM\UniqueConstraint] with the same name (119 occurrences), which DBAL
        // rejects when building the target schema. A unique constraint is an index
        // in MySQL, so drop the redundant Index declarations from the in-memory
        // metadata. Remove this once the generator stops emitting the duplicates.
        foreach ($metadata as $classMetadata) {
            $duplicateNames = array_intersect_key(
                $classMetadata->table['indexes'] ?? [],
                $classMetadata->table['uniqueConstraints'] ?? [],
            );
            foreach (array_keys($duplicateNames) as $name) {
                unset($classMetadata->table['indexes'][$name]);
            }
        }

        $statements = (new SchemaTool($entityManager))->getUpdateSchemaSql($metadata);

        // Tables with no entity mapping (audit *_hist tables, ETL working tables
        // and so on) are owned by olcs-etl and are not drift the API cares about.
        $statements = array_values(
            array_filter($statements, static fn (string $sql): bool => !str_starts_with($sql, 'DROP TABLE')),
        );
        sort($statements);

        if (getenv('REGENERATE_SCHEMA_DRIFT_BASELINE')) {
            file_put_contents(self::BASELINE_FILE, implode(PHP_EOL, $statements) . PHP_EOL);
            $this->assertFileExists(self::BASELINE_FILE);
            return;
        }

        $baseline = is_file(self::BASELINE_FILE)
            ? file(self::BASELINE_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
            : [];

        $this->assertSame(
            [],
            array_values(array_diff($statements, $baseline)),
            'New drift between entity metadata and the database schema was detected (statements above are what'
                . ' Doctrine would run to bring the database in line with the entities). Either the entity change'
                . ' needs a Liquibase migration in olcs-etl, or the migration needs an entity change. See the class'
                . ' docblock for how to regenerate the baseline after resolving.'
        );
    }
}
