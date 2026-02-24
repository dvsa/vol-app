<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermitJurisdiction;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitJurisdiction\GetList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitJurisdictionQuota as PermitJurisdictionQuotaRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitJurisdictionQuota as PermitJurisdictionQuotaEntity;
use Dvsa\Olcs\Transfer\Query\IrhpPermitJurisdiction\GetList as ListQuery;

/**
 * GetList Test
 */
class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('IrhpPermitJurisdictionQuota', PermitJurisdictionQuotaRepo::class);

        parent::setUp();
    }

    // Test Handle Command
    public function testHandleCommand(): void
    {
        $query = ListQuery::create([ 'irhpPermitStock' => '1']);

        $item1 = m::mock(PermitJurisdictionQuotaEntity::class)->makePartial();
        $item2 = m::mock(PermitJurisdictionQuotaEntity::class)->makePartial();
        $permitJurisdictions = new ArrayCollection();

        $permitJurisdictions->add($item1);
        $permitJurisdictions->add($item2);

        $this->repoMap['IrhpPermitJurisdictionQuota']
            ->shouldReceive('fetchByIrhpPermitStockId')
            ->with($query->getIrhpPermitStock())
            ->once()
            ->andReturn($permitJurisdictions);

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
