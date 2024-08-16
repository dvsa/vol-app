<?php

/**
 * Organisation Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Organisation;

use Dvsa\Olcs\Api\Domain\QueryHandler\Organisation\Organisation;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as TrafficAreaRepo;
use Dvsa\Olcs\Transfer\Query\Organisation\Organisation as Qry;
use Mockery as m;
use SAML2\Utilities\ArrayCollection;

/**
 * Organisation Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OrganisationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Organisation();
        $this->mockRepo('Organisation', OrganisationRepo::class);
        $this->mockRepo('TrafficArea', TrafficAreaRepo::class);

        parent::setUp();
    }

    public function testHandleQueryDisqualified()
    {
        $query = Qry::create(['id' => 111]);

        $mockOrganisation = m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class)->makePartial();
        $mockOrganisation->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockOrganisation->shouldReceive('getDisqualifications->count')->andReturn(2);
        $mockOrganisation->shouldReceive('getAllowedOperatorLocation')->andReturn('GB')->once();

        $mockTa = m::mock()
            ->shouldReceive('getId')
            ->once()
            ->andReturn(1)
            ->shouldReceive('getName')
            ->once()
            ->andReturn('foo')
            ->getMock();

        $this->repoMap['TrafficArea']
            ->shouldReceive('fetchListForNewApplication')
            ->with('GB')
            ->andReturn([$mockTa])
            ->once();

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockOrganisation);

        $expected = [
            'foo' => 'bar',
            'isDisqualified' => true,
            'allowedOperatorLocation' => 'GB',
            'taValueOptions' => [1 => 'foo'],
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query)->serialize());
    }

    public function testHandleQueryNotDisqualified()
    {
        $query = Qry::create(['id' => 111]);

        $mockOrganisation = m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class)->makePartial();
        $mockOrganisation->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockOrganisation->shouldReceive('getDisqualifications->count')->andReturn(0);
        $mockOrganisation->shouldReceive('getAllowedOperatorLocation')->andReturn('GB')->once();

        $mockTa = m::mock()
            ->shouldReceive('getId')
            ->once()
            ->andReturn(1)
            ->shouldReceive('getName')
            ->once()
            ->andReturn('foo')
            ->getMock();

        $this->repoMap['TrafficArea']
            ->shouldReceive('fetchListForNewApplication')
            ->with('GB')
            ->andReturn([$mockTa])
            ->once();

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockOrganisation);

        $expected = [
            'foo' => 'bar',
            'isDisqualified' => false,
            'allowedOperatorLocation' => 'GB',
            'taValueOptions' => [1 => 'foo'],
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query)->serialize());
    }
}
