<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Repository;

use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\OlcsTest\Integration\IntegrationTestCase;

/**
 * Runs the CPID export query (used by the CpidOrganisationExport queue
 * consumer) against the real schema. This path broke in production when
 * iterate() was swapped for toIterable() (VOL-7445): the DQL executed fine in
 * unit tests because the query builder was mocked.
 */
class OrganisationCpidExportTest extends IntegrationTestCase
{
    public function testFetchAllByStatusForCpidExportForAllOperators(): void
    {
        $rows = [];
        foreach ($this->repo('Organisation')->fetchAllByStatusForCpidExport(Organisation::OPERATOR_CPID_ALL) as $row) {
            $rows[] = $row;
        }

        $this->assertNotEmpty($rows, 'Expected the seeded test dataset to contain organisations');
        $this->assertSame(['id', 'name', 'cpid'], array_keys($rows[0]));
    }

    public function testFetchAllByStatusForCpidExportForNullCpid(): void
    {
        $rows = [];
        foreach ($this->repo('Organisation')->fetchAllByStatusForCpidExport(null) as $row) {
            $rows[] = $row;
        }

        $this->assertNotEmpty($rows, 'Expected the seeded test dataset to contain organisations without a CPID');
        $this->assertNull($rows[0]['cpid']);
    }
}
