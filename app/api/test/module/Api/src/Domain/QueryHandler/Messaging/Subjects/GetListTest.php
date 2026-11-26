<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Messaging\Subjects;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Subjects\GetList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\MessagingSubject as Repo;
use Dvsa\Olcs\Transfer\Query\Messaging\Subjects\GetList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

final class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo(Repo::class, Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['sort' => 'description', 'order' => 'ASC']);

        $subject = m::mock(BundleSerializableInterface::class);
        $subject->shouldReceive('serialize')
            ->once()
            ->with(['category', 'subCategory'])
            ->andReturn(['id' => 1]);

        $this->repoMap[Repo::class]->shouldReceive('fetchList')
            ->once()
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)
            ->andReturn([$subject]);
        $this->repoMap[Repo::class]->shouldReceive('fetchCount')->once()->with($query)->andReturn(1);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(['result' => [['id' => 1]], 'count' => 1], $result);
    }
}
