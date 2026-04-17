<?php

declare(strict_types=1);

/**
 * ContinuationDetailTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\ContinuationDetail as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Licence\ContinuationDetail as Query;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * ContinuationDetailTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationDetailTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Licence', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);
        $this->mockRepo('ContinuationDetail', \Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail::class);
        $this->mockRepo('Fee', \Dvsa\Olcs\Api\Domain\Repository\Fee::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 716]);

        $mockLicence = m::mock(Licence::class)->makePartial();
        $mockLicence->shouldReceive('serialize')->with([])->once()->andReturn(['LICENCE']);
        $mockLicence->shouldReceive('getPsvDiscsNotCeased->count')->with()->once()->andReturn(43);
        $mockLicence->shouldReceive('getId')->with()->twice()->andReturn(716);
        $mockContinuationDetail = m::mock(\Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail::class)
            ->shouldReceive('serialize')->with(['continuation', 'licence'])->once()->andReturn(['CD'])
            ->getMock();

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockLicence);
        $this->repoMap['ContinuationDetail']->shouldReceive('fetchForLicence')->with(716)
            ->andReturn([$mockContinuationDetail]);
        $this->repoMap['Fee']->shouldReceive('fetchOutstandingContinuationFeesByLicenceId')->with(716)
            ->andReturn(['FOO']);

        $response = $this->sut->handleQuery($query);
        $this->assertSame(
            [
                'LICENCE',
                'continuationDetail' => ['CD'],
                'numNotCeasedDiscs' => 43,
                'hasOutstandingContinuationFee' => true,
            ],
            $response->serialize()
        );
    }

    public function testHandleQueryNoContinuationDetail(): void
    {
        $query = Query::create(['id' => 716]);

        $mockLicence = m::mock(Licence::class)->makePartial();
        $mockLicence->shouldReceive('serialize')->with([])->once()->andReturn(['LICENCE']);
        $mockLicence->shouldReceive('getPsvDiscsNotCeased->count')->with()->once()->andReturn(43);
        $mockLicence->shouldReceive('getId')->with()->twice()->andReturn(716);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockLicence);
        $this->repoMap['ContinuationDetail']->shouldReceive('fetchForLicence')->with(716)
            ->andReturn([]);
        $this->repoMap['Fee']->shouldReceive('fetchOutstandingContinuationFeesByLicenceId')->with(716)
            ->andReturn(['FOO']);

        $response = $this->sut->handleQuery($query);
        $this->assertSame(
            [
                'LICENCE',
                'continuationDetail' => null,
                'numNotCeasedDiscs' => 43,
                'hasOutstandingContinuationFee' => true,
            ],
            $response->serialize()
        );
    }
}
