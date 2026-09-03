<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Schema;

use Doctrine\DBAL\Schema\Schema;

/**
 * The deliberate reductions in fidelity SchemaDriftTest applies before comparing entity
 * metadata against the real schema.
 *
 * Each one buys signal by giving up a class of detection, so each is kept here, named, and
 * pinned by a test rather than left inline where it can quietly widen.
 */
final class SchemaComparison
{
    /**
     * Blank a database comment only where the mapping declares none of its own.
     *
     * Comments are owned by the olcs-etl DDL - 2081 columns and 136 tables carry one - and
     * the entity metadata models exactly none of them: the generator turns a column comment
     * into the property's PHP docblock, not into an ORM\Column options entry. Comparing them
     * outright would report every commented column as drifted, which is true but never
     * actionable, and would bury the statements that are.
     *
     * Conditional rather than blanket so the blind spot shrinks on its own: a column that
     * does declare options: ['comment' => ...] is compared like any other, and coverage
     * grows by itself if the generator ever starts emitting them.
     *
     * The rationale this replaces - that ORM 2's JoinColumn had no options, so a comment
     * could never round-trip - no longer holds. ORM 3's JoinColumn takes options.
     */
    public static function silenceUnmodelledComments(Schema $databaseSchema, Schema $entitySchema): void
    {
        foreach ($entitySchema->getTables() as $entityTable) {
            if (!$databaseSchema->hasTable($entityTable->getName())) {
                continue;
            }

            $databaseTable = $databaseSchema->getTable($entityTable->getName());

            if (($entityTable->getOptions()['comment'] ?? '') === '') {
                $entityTable->addOption('comment', '');
                $databaseTable->addOption('comment', '');
            }

            foreach ($entityTable->getColumns() as $entityColumn) {
                if (!$databaseTable->hasColumn($entityColumn->getName())) {
                    continue;
                }

                if (($entityColumn->getComment() ?? '') === '') {
                    $databaseTable->getColumn($entityColumn->getName())->setComment('');
                }
            }
        }
    }
}
