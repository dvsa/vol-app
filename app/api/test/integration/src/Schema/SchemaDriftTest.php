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
 * Comments are silenced only where the mapping declares none of its own - see
 * silenceUnmodelledComments(). The former rationale, that ORM 2's JoinColumn had no
 * options so a comment could never round-trip, no longer holds: ORM 3's JoinColumn
 * takes options.
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

        SchemaComparison::silenceUnmodelledComments($databaseSchema, $entitySchema);

        foreach ([$databaseSchema, $entitySchema] as $schema) {
            foreach ($schema->getTables() as $table) {
                foreach ($table->getColumns() as $column) {
                    // YesNoType and YesNoNullType hardcode
                    // "tinyint(1) NOT NULL COMMENT '(DC2Type:yesno)'" as their whole
                    // declaration SQL, so they can never match the column they map however
                    // it is declared. They are PHP conversion semantics over a plain
                    // boolean, so compare the storage declaration instead.
                    //
                    // EncryptedStringType needed the same treatment under DBAL 3, which
                    // appended a DC2Type comment hint to custom types. DBAL 4 dropped that
                    // mechanism and the type declares as its StringType parent, so no
                    // normalisation is required - removing it changes no statement.
                    $type = $column->getType();
                    if (
                        $type instanceof \Dvsa\Olcs\Api\Entity\Types\YesNoType
                        || $type instanceof \Dvsa\Olcs\Api\Entity\Types\YesNoNullType
                    ) {
                        $column->setType(\Doctrine\DBAL\Types\Type::getType('boolean'));
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
