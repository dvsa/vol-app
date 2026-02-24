<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\PresidingTc;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\PresidingTc\GetList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\PresidingTc as Repo;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as PresidingTcEntity;
use Dvsa\Olcs\Transfer\Query\Cases\PresidingTc\GetList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryHandler\Cases\PresidingTc\GetList
 */
class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('PresidingTc', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['QUERY']);

        $presidingTcEntity = m::mock(PresidingTcEntity::class);
        $presidingTcEntity->shouldReceive('serialize')->once()->andReturn('SERIALIZED');

        $this->repoMap['PresidingTc']
            ->shouldReceive('fetchList')
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)
            ->andReturn([$presidingTcEntity])
            ->shouldReceive('fetchCount')->with($query)->andReturn('COUNT');

        $result = $this->sut->handleQuery($query);

        $this->assertSame(['SERIALIZED'], $result['result']);
        $this->assertSame('COUNT', $result['count']);
    }
}
