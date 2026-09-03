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

    public const ACTIONABLE = 'actionable';
    public const INEXPRESSIBLE = 'inexpressible';

    /**
     * Which of the two buckets a drift statement belongs in.
     *
     * "Inexpressible" means the mapping has no vocabulary for the difference, so it can never
     * be resolved by changing an entity - only by changing the database to match a name
     * Doctrine generates, which is not worth a migration. It is confined to implicit
     * ManyToMany join tables: those have no entity to carry an #[ORM\Index], and ORM 3's
     * JoinTable attribute takes only name, schema, joinColumns, inverseJoinColumns and
     * options - no way to name an index, declare a unique constraint, or map an extra column.
     * SchemaTool therefore builds their indexes with no name at all and DBAL falls through to
     * _generateIdentifierName(), producing IDX_<hash>.
     *
     * A join table's own columns are the exception: their declarations derive from the
     * referenced entities' primary keys, so a CHANGE is fixable there like anywhere else.
     * A compound statement carrying both is actionable, so that the half which can be fixed
     * stays visible rather than being filed away with the half that cannot.
     *
     * @param list<string> $mappedTables tables that an entity maps directly
     */
    public static function classify(string $statement, array $mappedTables): string
    {
        $table = self::tableOf($statement);

        if ($table === null || in_array($table, $mappedTables, true)) {
            return self::ACTIONABLE;
        }

        if (!str_starts_with($statement, 'ALTER TABLE')) {
            // CREATE / DROP / RENAME INDEX on a join table: the name is generated, not mapped.
            return self::INEXPRESSIBLE;
        }

        foreach (self::alterOperations($statement) as $operation) {
            if (str_starts_with($operation, 'CHANGE ')) {
                return self::ACTIONABLE;
            }
        }

        return self::INEXPRESSIBLE;
    }

    /** The table a drift statement acts on, or null if it cannot be read off. */
    public static function tableOf(string $statement): ?string
    {
        foreach (
            [
                '/^ALTER TABLE (\S+) /',
                '/^CREATE TABLE (\S+) /',
                '/^(?:CREATE|DROP)(?: UNIQUE)? INDEX \S+ ON (\S+)/',
            ] as $pattern
        ) {
            if (preg_match($pattern, $statement, $matches) === 1) {
                return $matches[1];
            }
        }

        return null;
    }

    /** @return list<string> the comma-separated operations inside one ALTER TABLE */
    private static function alterOperations(string $statement): array
    {
        $body = preg_replace('/^ALTER TABLE \S+ /', '', $statement, 1) ?? '';

        return preg_split('/,\s*(?=CHANGE |DROP |ADD |RENAME )/', $body) ?: [];
    }

    /**
     * Render the baseline grouped by bucket, so the number that needs acting on is legible
     * without counting lines. Headings are comments; parseBaseline() ignores them, and the
     * comparison itself still sees every statement.
     *
     * @param list<string> $statements
     * @param list<string> $mappedTables
     */
    public static function renderBaseline(array $statements, array $mappedTables): string
    {
        $buckets = [self::ACTIONABLE => [], self::INEXPRESSIBLE => []];
        foreach ($statements as $statement) {
            $buckets[self::classify($statement, $mappedTables)][] = $statement;
        }

        $out = [
            '# Schema drift baseline - statements Doctrine would run to bring the database in',
            '# line with the entity metadata. Regenerate with:',
            '#',
            '#   REGENERATE_SCHEMA_DRIFT_BASELINE=1 vendor/bin/phpunit \\',
            '#     -c phpunit-integration.xml --filter SchemaDriftTest',
            '#',
            '# Grouping is presentational. Every statement below is compared; nothing is hidden.',
            '',
            sprintf('# ---- actionable (%d) ----', count($buckets[self::ACTIONABLE])),
            '# A real disagreement between an entity and the schema. Resolve by changing the',
            '# mapping, or by adding a Liquibase migration in olcs-etl.',
            '',
        ];
        $out = array_merge($out, $buckets[self::ACTIONABLE]);
        $out[] = '';
        $out[] = sprintf('# ---- inexpressible from mapping (%d) ----', count($buckets[self::INEXPRESSIBLE]));
        $out[] = '# Differences on implicit ManyToMany join tables that no entity change can fix -';
        $out[] = '# generated index names, and columns a JoinTable cannot carry. See classify().';
        $out[] = '# Recorded and compared like any other statement, but not work anyone can pick up.';
        $out[] = '';
        $out = array_merge($out, $buckets[self::INEXPRESSIBLE]);

        return implode(PHP_EOL, $out) . PHP_EOL;
    }

    /**
     * The statements in a baseline file, ignoring headings and spacing.
     *
     * @return list<string>
     */
    public static function parseBaseline(string $contents): array
    {
        $lines = preg_split('/\R/', $contents) ?: [];

        return array_values(array_filter(
            array_map('rtrim', $lines),
            static fn (string $line): bool => $line !== '' && !str_starts_with($line, '#'),
        ));
    }
}
