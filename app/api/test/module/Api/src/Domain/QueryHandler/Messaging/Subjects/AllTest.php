<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Messaging\Subjects;

use Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Subjects\All;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class AllTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new All();
        $this->mockRepo(Repository\MessagingSubject::class, Repository\MessagingSubject::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);
        $mockResult = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);
        $mockResult->shouldReceive('serialize')->with([])->once()->andReturn(['test' => 'a']);

        $this->repoMap[Repository\MessagingSubject::class]->shouldReceive('fetchList')
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn([$mockResult]);

        $expected = [
            'result' => [
                ['test' => 'a']
            ],
            'count' => 1,
        ];

        $result = $this->sut->handleQuery($query);

        $this->assertSame($expected, $result);
    }
}
