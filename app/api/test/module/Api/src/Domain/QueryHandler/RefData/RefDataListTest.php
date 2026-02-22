<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\RefData;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\RefData\RefDataList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\RefData as Repo;
use Dvsa\Olcs\Transfer\Query\RefData\RefDataList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;

/**
 * Get RefData test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class RefDataListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('RefData', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['QUERY']);

        $refData = m::mock(RefDataEntity::class);
        $refData->shouldReceive('serialize')->once()->andReturn('SERIALIZED');

        $this->repoMap['RefData']->shouldReceive('fetchList')
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)->andReturn([$refData]);
        $this->repoMap['RefData']->shouldReceive('fetchCount')->with($query)->andReturn('COUNT');

        $result = $this->sut->handleQuery($query);

        $this->assertSame(['SERIALIZED'], $result['result']);
        $this->assertSame('COUNT', $result['count']);
    }
}
