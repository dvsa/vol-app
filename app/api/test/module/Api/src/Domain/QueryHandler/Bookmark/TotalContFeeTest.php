<?php

declare(strict_types=1);

/**
 * TotalContFee Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\TotalContFee;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as Repo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\TotalContFee as Qry;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as Entity;

/**
 * TotalContFee Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TotalContFeeTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new TotalContFee();
        $this->mockRepo('FeeType', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(
            [
                'goodsOrPsv' => 'Goods',
                'licenceType' => 'Standard',
                'effectiveFrom' => '2015-01-01',
                'trafficArea' => null
            ]
        );

        $mockFeeType = m::mock(RefData::class);
        $mockGoods = m::mock(RefData::class);
        $mockStandard = m::mock(RefData::class);

        /** @var Entity $entity */
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('serialize')
            ->with([])
            ->andReturn(['id' => 111]);

        $this->repoMap['FeeType']
            ->shouldReceive('getRefdataReference')
            ->with(Entity::FEE_TYPE_CONT)
            ->andReturn($mockFeeType)
            ->shouldReceive('getRefdataReference')
            ->with('Goods')
            ->andReturn($mockGoods)
            ->shouldReceive('getRefdataReference')
            ->with('Standard')
            ->andReturn($mockStandard)
            ->shouldReceive('fetchLatest')
            ->with(
                $mockFeeType,
                $mockGoods,
                $mockStandard,
                m::type(\DateTime::class),
                null
            )->andReturn($entity);

        $this->assertEquals(['id' => 111], $this->sut->handleQuery($query));
    }
}
