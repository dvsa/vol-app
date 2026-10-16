<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke as Entity;
use Dvsa\Olcs\Transfer\Query\Cases\ProposeToRevoke\ProposeToRevokeByCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\ProposeToRevoke::class)]
final class ProposeToRevokeTest extends RepositoryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repository\ProposeToRevoke::class);
    }

    public function testFetchProposeToRevokeUsingCase(): void
    {
        $caseId = 24;

        $command = m::mock(ProposeToRevokeByCase::class);
        $command->shouldReceive('getCase')->andReturn($caseId);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getOneOrNullResult')->with(Query::HYDRATE_OBJECT)->andReturn('EXPECT');

        $this->assertSame(
            'EXPECT',
            $this->sut->fetchProposeToRevokeUsingCase($command, Query::HYDRATE_OBJECT),
        );

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.case = :byCase',
            $qb->getDQL(),
        );
        $this->assertSame($caseId, $qb->getParameter('byCase')->getValue());
    }
}
