<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox as Entity;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Correspondence::class)]
final class CorrespondenceTest extends RepositoryTestCase
{
    /** @var  Repository\Correspondence */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repository\Correspondence::class, true);
    }

    public function testApplyListMethods(): void
    {
        $orgId = 9999;

        $qb = $this->createRealQb();

        $mockQry = m::mock(TransferQry\Correspondence\Correspondences::class)
            ->shouldReceive('getOrganisation')->once()->andReturn($orgId)
            ->getMock();

        $this->sut->applyListJoins($qb);
        $this->sut->applyListFilters($qb, $mockQry);

        $this->assertSame(
            'SELECT co, l, d FROM ' . Entity::class . ' co'
            . ' INNER JOIN co.licence l INNER JOIN co.document d'
            . ' WHERE l.organisation = :ORG_ID',
            $qb->getDQL(),
        );
        $this->assertSame($orgId, $qb->getParameter('ORG_ID')->getValue());
    }
}
