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
 * Column and table comments are excluded from the comparison: they are owned
 * by olcs-etl DDL, and ORM 2.x cannot express them on association join columns
 * (JoinColumn has no options), so they can never round-trip through entity
 * metadata. Revisit once on ORM 3, where JoinColumn supports options.
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

        // View-backed entities can never be introspected as tables, so SchemaTool
        // would forever propose CREATE TABLE for them - not drift. The Entity\View
        // namespace declares that intent; the information_schema check additionally
        // catches any view-mapped entity living elsewhere.
        $viewNames = $entityManager->getConnection()->fetchFirstColumn(
            'SELECT table_name FROM information_schema.views WHERE table_schema = DATABASE()'
        );
        $metadata = array_values(array_filter(
            $metadata,
            static fn ($classMetadata): bool => !str_starts_with($classMetadata->getName(), 'Dvsa\\Olcs\\Api\\Entity\\View\\')
                && !in_array($classMetadata->getTableName(), $viewNames, true),
        ));

        $connection = $entityManager->getConnection();
        $schemaManager = $connection->createSchemaManager();

        $databaseSchema = $schemaManager->introspectSchema();
        $entitySchema = (new SchemaTool($entityManager))->getSchemaFromMetadata($metadata);

        foreach ([$databaseSchema, $entitySchema] as $schema) {
            foreach ($schema->getTables() as $table) {
                $table->addOption('comment', '');
                foreach ($table->getColumns() as $column) {
                    $column->setComment('');

                    // Custom DBAL types are PHP conversion semantics over a plain
                    // storage type; the Liquibase DDL never carries their DC2Type
                    // comment hint, so compare the storage declaration instead.
                    $type = $column->getType();
                    if (
                        $type instanceof \Dvsa\Olcs\Api\Entity\Types\YesNoType
                        || $type instanceof \Dvsa\Olcs\Api\Entity\Types\YesNoNullType
                    ) {
                        $column->setType(\Doctrine\DBAL\Types\Type::getType('boolean'));
                    } elseif ($type instanceof \Dvsa\Olcs\Api\Entity\Types\EncryptedStringType) {
                        $column->setType(\Doctrine\DBAL\Types\Type::getType('string'));
                    }
                }

                // Foreign key constraint names are cosmetic (olcs-etl uses semantic
                // names, Doctrine generates hashed ones); compare by structure by
                // renaming both sides to a canonical structural name.
                foreach ($table->getForeignKeys() as $foreignKey) {
                    $canonical = strtolower(sprintf(
                        'fk_struct_%s_%s_%s',
                        implode('_', $foreignKey->getLocalColumns()),
                        $foreignKey->getForeignTableName(),
                        implode('_', $foreignKey->getForeignColumns()),
                    ));
                    if (strtolower($foreignKey->getName()) !== $canonical) {
                        $table->removeForeignKey($foreignKey->getName());
                        $table->addForeignKeyConstraint(
                            $foreignKey->getForeignTableName(),
                            $foreignKey->getLocalColumns(),
                            $foreignKey->getForeignColumns(),
                            $foreignKey->getOptions(),
                            $canonical,
                        );
                    }
                }
            }
        }

        $schemaDiff = $schemaManager->createComparator()->compareSchemas($databaseSchema, $entitySchema);
        $statements = $connection->getDatabasePlatform()->getAlterSchemaSQL($schemaDiff);

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
