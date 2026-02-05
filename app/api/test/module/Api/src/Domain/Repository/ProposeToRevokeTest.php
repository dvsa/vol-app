<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\ProposeToRevoke::class)]
class ProposeToRevokeTest extends RepositoryTestCase
{
    /** @var  Repository\ProposeToRevoke */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repository\ProposeToRevoke::class);
    }

    public function testFetchProposeToRevokeUsingCase(): void
    {
        $caseId = 24;

        $command = m::mock(\Dvsa\Olcs\Transfer\Query\Cases\ProposeToRevoke\ProposeToRevokeByCase::class);
        $command->shouldReceive('getCase')
            ->andReturn($caseId);

        $qb = $this->createMockQb('{{QUERY}}');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getOneOrNullResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn('EXPECT');

        $actual = $this->sut->fetchProposeToRevokeUsingCase($command, Query::HYDRATE_OBJECT);

        static::assertEquals('EXPECT', $actual);

        $expected = '{{QUERY}} ' .
            'AND m.case = [[' . $caseId . ']]';

        static::assertEquals($expected, $this->query);
    }
}
