<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Repository;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\OlcsTest\Integration\IntegrationTestCase;

/**
 * Queries built by the With query partial, run against the real ORM. The unit tests mock
 * the query builder and compare DQL as a string, so a query naming an alias that no longer
 * exists passes them and only breaks when the parser sees it.
 */
class QueryPartialJoinsTest extends IntegrationTestCase
{
    public function testLicenceFetchWithAddressesUsingId(): void
    {
        $licence = $this->repo('Licence')->fetchWithAddressesUsingId($this->aLicenceId());

        $this->assertInstanceOf(Licence::class, $licence);
    }

    public function testConditionUndertakingFetchListForLicenceReadOnly(): void
    {
        $this->assertIsArray($this->repo('ConditionUndertaking')->fetchListForLicenceReadOnly($this->aLicenceId()));
    }

    private function aLicenceId(): int
    {
        $id = $this->em()->createQuery('SELECT MIN(l.id) FROM ' . Licence::class . ' l')->getSingleScalarResult();
        $this->assertNotNull($id, 'Expected the seeded test dataset to contain a licence');

        return (int) $id;
    }
}
