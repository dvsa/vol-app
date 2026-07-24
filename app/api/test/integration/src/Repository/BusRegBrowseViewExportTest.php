<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Repository;

use Dvsa\OlcsTest\Integration\IntegrationTestCase;

/**
 * Runs the bus registration browse export query (used by the BusRegBrowseExport
 * query handler) against the real bus_reg_browse_view database view. This path
 * broke in production when iterate() was swapped for toIterable() (VOL-7445),
 * and it also verifies the view itself exists and matches the entity mapping -
 * something no unit test can do. The seeded view is typically empty, so the
 * assertions cover execution and shape rather than row content.
 */
class BusRegBrowseViewExportTest extends IntegrationTestCase
{
    public function testFetchForExportExecutesAgainstTheView(): void
    {
        // Export every mapped field (the production handler passes a user-chosen
        // subset of the same names), derived from metadata so entity changes are
        // exercised automatically rather than tracked by hand
        $columns = array_values(array_diff(
            $this->em()->getClassMetadata(\Dvsa\Olcs\Api\Entity\View\BusRegBrowseView::class)->getFieldNames(),
            ['id'],
        ));

        $rows = [];
        $result = $this->repo('BusRegBrowseView')->fetchForExport($columns, '2020-01-01', ['B', 'C']);

        foreach ($result as $row) {
            $rows[] = $row;
        }

        $this->assertIsList($rows);

        if ($rows !== []) {
            $this->assertArrayHasKey('licNo', $rows[0]);
        }
    }
}
