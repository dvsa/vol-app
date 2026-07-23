<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Repository;

use Dvsa\Olcs\Transfer\Query\Licence\GoodsVehiclesExport;
use Dvsa\OlcsTest\Integration\IntegrationTestCase;

/**
 * Runs the goods vehicles export query exactly as the Licence\GoodsVehiclesExport
 * query handler does: build the paginated LVA query from the transfer DTO, then
 * stream it with fetchForExport(). This path broke in production when iterate()
 * was swapped for toIterable() (VOL-7445): unit tests mock the query builder so
 * the DQL (including the MAX(gds.id) correlated subquery join) never executes.
 */
class LicenceVehicleExportTest extends IntegrationTestCase
{
    public function testFetchForExportExecutesAndStreamsRows(): void
    {
        $licenceId = $this->em()->getConnection()->fetchOne(
            'SELECT licence_id FROM licence_vehicle WHERE specified_date IS NOT NULL LIMIT 1'
        );
        $this->assertNotFalse($licenceId, 'Expected the seeded test dataset to contain a specified licence vehicle');

        $repo = $this->repo('LicenceVehicle');
        $query = GoodsVehiclesExport::create(['id' => (int) $licenceId]);

        $qb = $repo->createPaginatedVehiclesDataForLicenceQuery($query, (int) $licenceId);

        $rows = [];
        foreach ($repo->fetchForExport($qb) as $row) {
            $rows[] = $row;
        }

        $this->assertNotEmpty($rows, 'Expected the export to return rows for a licence with specified vehicles');
        $this->assertSame(
            ['vrm', 'platedWeight', 'specifiedDate', 'removalDate', 'discId', 'ceasedDate', 'discNo'],
            array_keys($rows[0]),
        );
    }
}
