<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\ApplicationPathGroup;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup as ApplicationPathGroupEntity;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationPathGroupList;

/**
 * Application Path Group test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplicationPathGroupTest extends RepositoryTestCase
{
    public function testFetchListForApplicationPathGroupList(): void
    {
        $this->setUpSut(ApplicationPathGroup::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf();

        $query = ApplicationPathGroupList::create([]);
        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH AND m.isVisibleInInternal = [[true]]';

        $this->assertEquals($expectedQuery, $this->query);
    }
}
