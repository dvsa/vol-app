<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\TransportManagers as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Licence\TransportManagers as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence;
use Mockery as m;

/**
 * TransportManagersTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagersTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('TransportManagerLicence', TransportManagerLicence::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $licenceId = 1066;
        $query = Query::create(['id' => $licenceId]);

        $licence = m::mock()
            ->shouldReceive('getId')
            ->twice()
            ->andReturn($licenceId)
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($licence);

        $this->repoMap['TransportManagerLicence']
            ->shouldReceive('fetchWithContactDetailsByLicence')
            ->with($licenceId)
            ->andReturn('tmlns')
            ->once();

        $expected = [
            'id' => $licenceId,
            'tmLicences' => 'tmlns'
        ];

        $result = $this->sut->handleQuery($query);

        $this->assertEquals($expected, $result);
    }
}
