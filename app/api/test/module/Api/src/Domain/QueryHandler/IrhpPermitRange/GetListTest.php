<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermitRange;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitRange\GetList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as PermitRangeRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as PermitRangeEntity;
use Dvsa\Olcs\Transfer\Query\IrhpPermitRange\GetList as ListQuery;

/**
 * GetList Test
 */
class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('IrhpPermitRange', PermitRangeRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $query = ListQuery::create([ 'irhpPermitStock' => '1']);

        $item1 = m::mock(PermitRangeEntity::class)->makePartial();
        $item2 = m::mock(PermitRangeEntity::class)->makePartial();
        $permitRanges = new ArrayCollection();

        $permitRanges->add($item1);
        $permitRanges->add($item2);

        $this->repoMap['IrhpPermitRange']
            ->shouldReceive('fetchByIrhpPermitStockId')
            ->with($query->getIrhpPermitStock())
            ->once()
            ->andReturn($permitRanges);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [
                0 => [],
                1 => []
            ]
        ];

        $this->assertIsArray($result);

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            if (is_array($value)) {
                $this->assertIsArray($result[$key]);
                $this->assertCount(count($value), $result[$key]);
            } else {
                $this->assertEquals($value, $result[$key]);
            }
        }
    }
}
