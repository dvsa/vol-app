<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermitSector;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitSector\GetList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitSectorQuota as PermitSectorQuotaRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitSectorQuota as PermitSectorQuotaEntity;
use Dvsa\Olcs\Transfer\Query\IrhpPermitSector\GetList as ListQuery;

/**
 * GetList Test
 */
class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('IrhpPermitSectorQuota', PermitSectorQuotaRepo::class);

        parent::setUp();
    }

    // Test Handle Command
    public function testHandleCommand(): void
    {
        $query = ListQuery::create([ 'irhpPermitStock' => '1']);

        $item1 = m::mock(PermitSectorQuotaEntity::class)->makePartial();
        $item2 = m::mock(PermitSectorQuotaEntity::class)->makePartial();
        $permitSectors = new ArrayCollection();

        $permitSectors->add($item1);
        $permitSectors->add($item2);

        $this->repoMap['IrhpPermitSectorQuota']
            ->shouldReceive('fetchByIrhpPermitStockId')
            ->with($query->getIrhpPermitStock())
            ->once()
            ->andReturn($permitSectors);

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
