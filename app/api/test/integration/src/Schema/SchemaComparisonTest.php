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
