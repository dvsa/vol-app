<?php

declare(strict_types=1);

/**
 * PsvDiscs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\PsvDiscs;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Transfer\Query\Licence\PsvDiscs as Qry;

/**
 * PsvDiscs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvDiscsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PsvDiscs();
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('PsvDisc', \Dvsa\Olcs\Api\Domain\Repository\PsvDisc::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111, 'includeCeased' => true]);

        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('serialize')
            ->once()
            ->with([])
            ->andReturn(['foo' => 'bar', 'remainingSpacesPsv' => 10])
            ->shouldReceive('getRemainingSpacesPsv')
            ->andReturn(10)
            ->once();

        $licence->shouldReceive('getPsvDiscs->count')
            ->andReturn(10);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($licence);

        $this->repoMap['PsvDisc']->shouldReceive('fetchList')
            ->with($query)
            ->once()
            ->andReturn(['DISCS']);
        $this->repoMap['PsvDisc']->shouldReceive('fetchCount')
            ->with($query)
            ->once()
            ->andReturn(101);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(
            ['foo' => 'bar', 'remainingSpacesPsv' => 10, 'totalPsvDiscs' => 101, 'psvDiscs' => ['DISCS']],
            $result->serialize()
        );
    }
}
