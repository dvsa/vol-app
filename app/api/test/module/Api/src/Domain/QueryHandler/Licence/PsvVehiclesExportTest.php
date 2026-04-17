<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\PsvVehiclesExport;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\Licence\PsvVehiclesExport as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Doctrine\ORM\Query;

/**
 * Psv vehicles export test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PsvVehiclesExportTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PsvVehiclesExport();
        $this->mockRepo('LicenceVehicle', LicenceVehicle::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $licenceId = 111;
        $includeRemoved = true;

        $query = Qry::create(
            [
                'id' => $licenceId,
                'includeRemoved' => $includeRemoved
            ]
        );

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('fetchPsvVehiclesByLicenceId')
            ->with(111, true)
            ->andReturn(['result'])
            ->once()
            ->getMock();

        $expected = [
            'result' => ['result'],
            'count'  => 1
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query));
    }
}
