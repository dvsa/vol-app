<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Letter\LetterIssueType;

use Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterIssueType\GetList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterIssueType as Repo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueType as LetterIssueTypeEntity;
use Dvsa\Olcs\Transfer\Query\Letter\LetterIssueType\GetList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Get List of Letter Issue Types Test
 *
 * @covers \Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterIssueType\GetList
 */
class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('LetterIssueType', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $query = Query::create(['sort' => 'displayOrder', 'order' => 'ASC']);

        $letterIssueTypeEntity = m::mock(LetterIssueTypeEntity::class);
        $letterIssueTypeEntity->shouldReceive('serialize')->once()->andReturn([
            'id' => 1,
            'name' => 'Adverts',
            'code' => 'AD',
            'displayOrder' => 1
        ]);

        $this->repoMap['LetterIssueType']
            ->shouldReceive('fetchList')
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)
            ->andReturn([$letterIssueTypeEntity])
            ->shouldReceive('fetchCount')->with($query)->andReturn(1);

        $result = $this->sut->handleQuery($query);

        $this->assertSame([['id' => 1, 'name' => 'Adverts', 'code' => 'AD', 'displayOrder' => 1]], $result['result']);
        $this->assertSame(1, $result['count']);
    }
}
