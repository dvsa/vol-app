<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Support;

use Doctrine\ORM\EntityManagerInterface;

/**
 * An EntityManager carrying the production entity metadata and no database.
 *
 * Reuses phpstan-object-manager.php, which builds the mappings from the same config the
 * application uses and pins serverVersion so DBAL never opens a connection. That loader
 * already serves PHPStan and (with a connection swapped in) the integration suite; this is
 * the third consumer, and the only one that needs it exactly as-is.
 *
 * Unit tests use it so a QueryBuilder can resolve real associations and compile real DQL:
 * a mistyped field or a join to a non-existent association then fails the test rather than
 * passing through a hand-written double. Metadata is parsed once per process (~200ms) and
 * the DQL parser caches, so each query afterwards costs well under a millisecond.
 */
final class DoctrineMetadata
{
    private static ?EntityManagerInterface $entityManager = null;

    public static function entityManager(): EntityManagerInterface
    {
        if (self::$entityManager === null) {
            // The loader chdir()s to app/api to resolve its own requires; restore the
            // working directory so nothing else in the suite sees the side effect.
            $cwd = getcwd();

            self::$entityManager = require self::appRoot() . '/phpstan-object-manager.php';

            if ($cwd !== false) {
                chdir($cwd);
            }
        }

        return self::$entityManager;
    }

    public static function appRoot(): string
    {
        return dirname(__DIR__, 3);
    }
}
