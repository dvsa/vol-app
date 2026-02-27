<?php

declare(strict_types=1);

/**
 * OtherActiveLicences Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\OtherActiveLicences;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Transfer\Query\Licence\OtherActiveLicences as Qry;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * OtherActiveLicences Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OtherActiveLicencesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new OtherActiveLicences();
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = [];
        $query = Qry::create($data);

        $otherLicence = m::mock(LicenceEntity::class);
        $otherLicence->shouldReceive('serialize')
            ->andReturn(['bar' => 'foo']);

        $otherLicences = [
            $otherLicence
        ];

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getOtherActiveLicences')
            ->andReturn($otherLicences);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($licence);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $licence->shouldReceive('serialize')
            ->once()
            ->with([])
            ->andReturn(['foo' => 'bar']);

        $expected = [
            'foo' => 'bar',
            'otherActiveLicences' => [
                ['bar' => 'foo']
            ]
        ];

        $this->assertEquals($expected, $result->serialize());
    }
}
