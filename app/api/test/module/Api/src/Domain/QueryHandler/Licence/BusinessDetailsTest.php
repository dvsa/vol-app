<?php

declare(strict_types=1);

/**
 * Business Details Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\BusinessDetails;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Transfer\Query\Licence\BusinessDetails as Qry;

/**
 * Business Details Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetailsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new BusinessDetails();
        $this->mockRepo('Organisation', OrganisationRepo::class);
        $this->mockRepo('Licence', OrganisationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $expectedBundle = [
            'contactDetails' => [
                'address' => [
                    'countryCode'
                ]
            ]
        ];

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(222);
        $organisation->shouldReceive('serialize')
            ->with($expectedBundle)
            ->once()
            ->andReturn(
                ['foo' => 'bar']
            );

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setOrganisation($organisation);

        $licence->shouldReceive('getTradingNames')
            ->andReturn(
                [
                    m::mock()->shouldReceive('serialize')->andReturn(['foo' => 'bar'])->getMock()
                ]
            );

        $licence->shouldReceive('getCompanySubsidiaries')
            ->andReturn(
                [
                    m::mock()->shouldReceive('serialize')->andReturn(['bar' => 'foo'])->getMock()
                ]
            );

        $query = Qry::create(['id' => 111]);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($licence);

        $this->repoMap['Organisation']->shouldReceive('fetchBusinessDetailsById')
            ->with(222)
            ->andReturn($organisation);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $expected = [
            'foo' => 'bar',
            'tradingNames' => [
                ['foo' => 'bar']
            ],
            'companySubsidiaries' => [
                ['bar' => 'foo']
            ]
        ];

        $this->assertEquals($expected, $result->serialize());
    }
}
