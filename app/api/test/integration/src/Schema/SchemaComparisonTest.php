<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Schema;

use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * Pins the reductions in fidelity SchemaDriftTest applies, so that a rule which is meant to
 * be conditional cannot quietly widen into a blanket one. Needs no database: these operate
 * on Schema objects alone.
 */
final class SchemaComparisonTest extends TestCase
{
    /**
     * No entity currently declares a column comment, so nothing in the real run exercises
     * this branch. It is the half that makes the exclusion self-limiting, so it is the half
     * worth pinning.
     */
    public function testAColumnCommentDeclaredByTheMappingIsStillCompared(): void
    {
        [$databaseSchema, $entitySchema] = $this->schemas('declared in the mapping');

        SchemaComparison::silenceUnmodelledComments($databaseSchema, $entitySchema);

        $this->assertSame('from the DDL', $databaseSchema->getTable('probe')->getColumn('modelled')->getComment());
        $this->assertSame(
            'declared in the mapping',
            $entitySchema->getTable('probe')->getColumn('modelled')->getComment()
        );
    }

    public function testAColumnCommentTheMappingDoesNotModelIsSilenced(): void
    {
        [$databaseSchema, $entitySchema] = $this->schemas('declared in the mapping');

        SchemaComparison::silenceUnmodelledComments($databaseSchema, $entitySchema);

        $this->assertSame('', $databaseSchema->getTable('probe')->getColumn('unmodelled')->getComment());
    }

    public function testATableCommentTheMappingDoesNotModelIsSilencedOnBothSides(): void
    {
        [$databaseSchema, $entitySchema] = $this->schemas('');

        SchemaComparison::silenceUnmodelledComments($databaseSchema, $entitySchema);

        $this->assertSame('', $databaseSchema->getTable('probe')->getOptions()['comment'] ?? null);
        $this->assertSame('', $entitySchema->getTable('probe')->getOptions()['comment'] ?? null);
    }

    /** A table the mapping does not cover is left alone; SchemaDriftTest drops it as a DROP TABLE. */
    public function testAnUnmappedTableIsUntouched(): void
    {
        [$databaseSchema, $entitySchema] = $this->schemas('');
        $etlOnly = $databaseSchema->createTable('etl_only');
        $etlOnly->addColumn('id', 'integer');
        $etlOnly->getColumn('id')->setComment('owned by olcs-etl');

        SchemaComparison::silenceUnmodelledComments($databaseSchema, $entitySchema);

        $this->assertSame('owned by olcs-etl', $databaseSchema->getTable('etl_only')->getColumn('id')->getComment());
    }


    /**
     * Every difference is real; these are the ones no entity change can resolve, because
     * ORM 3's JoinTable attribute cannot name an index, declare a unique constraint or map
     * an extra column, and an implicit join table has no entity to hang one on.
     *
     * @param non-empty-string $statement
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpClassify')]
    public function testClassify(string $expected, string $statement): void
    {
        $this->assertSame($expected, SchemaComparison::classify($statement, ['licence', 'address']));
    }

    public function testEnumColumnMappedAsStringIsInexpressible(): void
    {
        $databaseSchema = new Schema();
        $databaseTable = $databaseSchema->createTable('document_analysis');
        $databaseTable->addColumn('status', 'enum', ['values' => ['PENDING', 'COMPLETE']]);
        $databaseTable->addColumn('assessment_status', 'enum', ['values' => ['APPROVED', 'REJECTED']]);

        $entitySchema = new Schema();
        $entityTable = $entitySchema->createTable('document_analysis');
        $entityTable->addColumn('status', 'string');
        $entityTable->addColumn('assessment_status', 'string');

        $enumStringColumns = SchemaComparison::enumColumnsMappedAsStrings($databaseSchema, $entitySchema);

        $this->assertSame(['document_analysis' => ['status', 'assessment_status']], $enumStringColumns);
        $this->assertSame(
            SchemaComparison::INEXPRESSIBLE,
            SchemaComparison::classify(
                "ALTER TABLE document_analysis CHANGE status status VARCHAR(255) DEFAULT 'PENDING' NOT NULL, "
                    . 'CHANGE assessment_status assessment_status VARCHAR(255) DEFAULT NULL',
                ['document_analysis'],
                $enumStringColumns,
            )
        );
    }

    public function testCompoundEnumAndOtherChangesRemainActionable(): void
    {
        $this->assertSame(
            SchemaComparison::ACTIONABLE,
            SchemaComparison::classify(
                'ALTER TABLE document_analysis CHANGE status status VARCHAR(255) NOT NULL, '
                    . 'CHANGE result result JSON DEFAULT NULL',
                ['document_analysis'],
                ['document_analysis' => ['status']],
            )
        );
    }

    public function testEnumColumnWithANonStringMappingIsNotExempted(): void
    {
        $databaseSchema = new Schema();
        $databaseTable = $databaseSchema->createTable('probe');
        $databaseTable->addColumn('status', 'enum', ['values' => ['1', '2']]);

        $entitySchema = new Schema();
        $entityTable = $entitySchema->createTable('probe');
        $entityTable->addColumn('status', 'integer');

        $enumStringColumns = SchemaComparison::enumColumnsMappedAsStrings($databaseSchema, $entitySchema);

        $this->assertSame([], $enumStringColumns);
        $this->assertSame(
            SchemaComparison::ACTIONABLE,
            SchemaComparison::classify(
                'ALTER TABLE probe CHANGE status status INT NOT NULL',
                ['probe'],
                $enumStringColumns,
            )
        );
    }

    /** @return array<string, array{string, string}> */
    public static function dpClassify(): array
    {
        return [
            'generated index name on a join table' => [
                SchemaComparison::INEXPRESSIBLE,
                'ALTER TABLE case_category RENAME INDEX ix_case_category_case_id TO IDX_913163BCCF10D4F5',
            ],
            'legacy column a JoinTable cannot carry' => [
                SchemaComparison::INEXPRESSIBLE,
                'ALTER TABLE bus_reg_local_auth DROP olbs_key',
            ],
            'unique over a join table a JoinTable cannot declare' => [
                SchemaComparison::INEXPRESSIBLE,
                'DROP INDEX uk_pi_type_pi_id_pi_type_id ON pi_type',
            ],
            'join column type derives from the referenced entity, so it is fixable' => [
                SchemaComparison::ACTIONABLE,
                'ALTER TABLE recipient_traffic_area CHANGE traffic_area_id traffic_area_id CHAR(1) NOT NULL',
            ],
            'compound statement stays visible for the half that can be fixed' => [
                SchemaComparison::ACTIONABLE,
                'ALTER TABLE bus_reg_traffic_area DROP olbs_key, '
                    . 'CHANGE traffic_area_id traffic_area_id CHAR(1) NOT NULL',
            ],
            'anything on a table an entity maps' => [
                SchemaComparison::ACTIONABLE,
                'CREATE INDEX ix_address_country_code ON address (country_code)',
            ],
            'column change on a mapped table' => [
                SchemaComparison::ACTIONABLE,
                'ALTER TABLE licence CHANGE traffic_area_id traffic_area_id CHAR(1) DEFAULT NULL',
            ],
        ];
    }

    public function testTableOfReadsEveryStatementShapeTheComparatorEmits(): void
    {
        $this->assertSame('licence', SchemaComparison::tableOf('ALTER TABLE licence DROP olbs_key'));
        $this->assertSame('answer', SchemaComparison::tableOf('CREATE INDEX ix_x ON answer (a)'));
        $this->assertSame('answer', SchemaComparison::tableOf('CREATE UNIQUE INDEX uk_x ON answer (a)'));
        $this->assertSame('answer', SchemaComparison::tableOf('DROP INDEX ix_x ON answer'));
        $this->assertSame('document_analysis', SchemaComparison::tableOf('CREATE TABLE document_analysis (id INT)'));
        $this->assertNull(SchemaComparison::tableOf('SET FOREIGN_KEY_CHECKS = 0'));
    }

    /** Headings are presentational: what the assertion compares must survive a round trip. */
    public function testRenderedBaselineParsesBackToExactlyTheStatementsItWasGiven(): void
    {
        $statements = [
            'ALTER TABLE address CHANGE country_code country_code VARCHAR(2) DEFAULT NULL',
            'ALTER TABLE bus_reg_local_auth DROP olbs_key',
            'ALTER TABLE case_category RENAME INDEX ix_case_category_case_id TO IDX_913163BCCF10D4F5',
        ];

        $parsed = SchemaComparison::parseBaseline(SchemaComparison::renderBaseline($statements, ['address']));

        sort($statements);
        sort($parsed);
        $this->assertSame($statements, $parsed);
    }

    public function testRenderedBaselineCountsEachBucket(): void
    {
        $rendered = SchemaComparison::renderBaseline(
            [
                'ALTER TABLE address CHANGE country_code country_code VARCHAR(2) DEFAULT NULL',
                "ALTER TABLE document_analysis CHANGE status status VARCHAR(255) DEFAULT 'PENDING' NOT NULL, "
                    . 'CHANGE assessment_status assessment_status VARCHAR(255) DEFAULT NULL',
                'ALTER TABLE bus_reg_local_auth DROP olbs_key',
            ],
            ['address', 'document_analysis'],
            ['document_analysis' => ['status', 'assessment_status']],
        );

        $this->assertStringContainsString('# ---- actionable (1) ----', $rendered);
        $this->assertStringContainsString('# ---- inexpressible from mapping (2) ----', $rendered);
        $this->assertStringContainsString(
            "ALTER TABLE document_analysis CHANGE status status VARCHAR(255) DEFAULT 'PENDING' NOT NULL, "
                . 'CHANGE assessment_status assessment_status VARCHAR(255) DEFAULT NULL',
            $rendered,
        );
    }

    /** @return array{Schema, Schema} */
    private function schemas(string $entityColumnComment): array
    {
        $databaseSchema = new Schema();
        $databaseTable = $databaseSchema->createTable('probe');
        $databaseTable->addColumn('modelled', 'string', ['length' => 10]);
        $databaseTable->getColumn('modelled')->setComment('from the DDL');
        $databaseTable->addColumn('unmodelled', 'string', ['length' => 10]);
        $databaseTable->getColumn('unmodelled')->setComment('from the DDL');
        $databaseTable->addOption('comment', 'table comment from the DDL');

        $entitySchema = new Schema();
        $entityTable = $entitySchema->createTable('probe');
        $entityTable->addColumn('modelled', 'string', ['length' => 10]);
        $entityTable->getColumn('modelled')->setComment($entityColumnComment);
        $entityTable->addColumn('unmodelled', 'string', ['length' => 10]);

        return [$databaseSchema, $entitySchema];
    }
}
