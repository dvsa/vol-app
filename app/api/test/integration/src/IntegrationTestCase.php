<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration;

use Doctrine\ORM\EntityManager;
use Dvsa\OlcsTest\Integration\Support\Database;
use PHPUnit\Framework\TestCase;

/**
 * Base class for tests that run real queries against the local development
 * database. Tests are skipped (not failed) when the database is unreachable,
 * and each test runs inside a transaction that is rolled back afterwards so
 * tests can insert whatever fixture data they need.
 */
abstract class IntegrationTestCase extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        if (!Database::isAvailable()) {
            self::markTestSkipped(
                'Integration database unavailable: ' . Database::unavailableReason()
                    . ' - start it with `docker compose up -d db` and load the schema with `npm run refresh`.'
            );
        }

        $this->em()->getConnection()->beginTransaction();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if (Database::isAvailable()) {
            $connection = $this->em()->getConnection();

            while ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            $this->em()->clear();
        }

        parent::tearDown();
    }

    protected function em(): EntityManager
    {
        return Database::entityManager();
    }

    /**
     * Fetch a repository by its short service name (e.g. 'LicenceVehicle').
     */
    protected function repo(string $name): mixed
    {
        return Database::repository($name);
    }
}
