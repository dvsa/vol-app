<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Every decimal column must declare precision and scale.
 *
 * DBAL 3 tolerated their absence: it emitted a deprecation and silently defaulted
 * to (10, 0). DBAL 4 raises ColumnPrecisionRequired instead, so any decimal column
 * without them breaks schema comparison, orm:validate-schema and migration diffs —
 * SchemaDriftTest in the integration suite fails on the first one it reaches.
 *
 * THIS TEST CURRENTLY FAILS, BY DESIGN, and is excluded from the default suite via
 * the entity-decimal-precision group in phpunit.xml.dist. Run it directly:
 *
 *   vendor/bin/phpunit --group entity-decimal-precision
 *
 * The fix is not to edit the entities by hand. Doctrine3SchemaIntrospector now
 * feeds precision and scale through to DefaultTypeHandler, which has always known
 * how to emit them, so regenerating produces the declarations automatically. Once
 * the entities carry them, delete the <groups> block from phpunit.xml.dist and this
 * becomes a standing guard.
 *
 * Needs no database: the EntityManager is built from the entity attributes alone,
 * reusing the loader written for phpstan-doctrine.
 */
class DecimalPrecisionTest extends TestCase
{
    #[Group('entity-decimal-precision')]
    public function testEveryDecimalColumnDeclaresPrecisionAndScale(): void
    {
        $entityManager = require __DIR__ . '/../../../../../phpstan-object-manager.php';

        $missing = [];
        foreach ($entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            foreach ($metadata->fieldMappings as $fieldName => $mapping) {
                if (($mapping->type ?? null) !== 'decimal') {
                    continue;
                }

                if (($mapping->precision ?? null) === null || ($mapping->scale ?? null) === null) {
                    $missing[] = $metadata->getName() . '::$' . $fieldName;
                }
            }
        }
        sort($missing);

        $this->assertSame(
            [],
            $missing,
            'Decimal columns without precision/scale (listed above). Regenerate the entities rather than'
                . ' editing them by hand - see the class docblock.'
        );
    }
}
