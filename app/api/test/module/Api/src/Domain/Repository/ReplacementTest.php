<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\System\Replacement as ReplacementEntity;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Replacement as ReplacementRepo;

/**
 * ReplacementTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ReplacementTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(ReplacementRepo::class);
    }

    public function testFetchAll(): void
    {
        $hydrationMode = Query::HYDRATE_ARRAY;
        $alias = 'r';
        $queryResult = ['RESULTS'];

        $queryBuilder = m::mock(QueryBuilder::class);
        $queryBuilder->expects('select')->with($alias)->andReturnSelf();
        $queryBuilder->expects('from')->with(ReplacementEntity::class, $alias)->andReturnSelf();
        $queryBuilder->expects('getQuery->getResult')->with($hydrationMode)->andReturn($queryResult);

        $this->em->expects('createQueryBuilder')->andReturn($queryBuilder);

        self::assertEquals($queryResult, $this->sut->fetchAll($hydrationMode));
    }
}
