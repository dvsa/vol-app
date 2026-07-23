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
        $rows = [];
        $result = $this->repo('BusRegBrowseView')->fetchForExport(
            ['licNo', 'regNo', 'serviceNo', 'startPoint', 'finishPoint', 'acceptedDate'],
            '2020-01-01',
            ['B', 'C'],
        );

        foreach ($result as $row) {
            $rows[] = $row;
        }

        $this->assertIsList($rows);

        if ($rows !== []) {
            $this->assertArrayHasKey('licNo', $rows[0]);
        }
    }
}
