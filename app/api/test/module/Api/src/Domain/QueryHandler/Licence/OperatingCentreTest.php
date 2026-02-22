<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\OperatingCentre as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Licence\OperatingCentre as Qry;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;

/**
 * OperatingCentreTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OperatingCentreTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = ['id' => 234];
        $query = Qry::create($data);

        $mockLicence = m::mock(LicenceEntity::class)
            ->shouldReceive('serialize')->with(
                [
                    'operatingCentres' => [
                        'operatingCentre' => ['address']
                    ],
                ]
            )->once()->andReturn(['LIC'])->getMock();

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($query)->once()->andReturn($mockLicence);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['LIC'], $result->serialize());
    }
}
