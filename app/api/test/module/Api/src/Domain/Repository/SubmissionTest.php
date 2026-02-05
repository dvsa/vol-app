<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Submission as Repo;
use Doctrine\ORM\QueryBuilder;

/**
 * SubmissionTest
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SubmissionTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchByUserWithOpenOnly(): void
    {
        $submissionId = 1;
        $qb = m::mock(QueryBuilder::class);
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')->once()->with($qb)->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('case', 'c')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('c.licence', 'cl')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('byId')->with($submissionId)->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getSingleResult')->andReturn('RESULT');

        $result = $this->sut->fetchWithCaseAndLicenceById($submissionId);
        $this->assertEquals('RESULT', $result);
    }
}
