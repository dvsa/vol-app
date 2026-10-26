<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\TmEmployment as Repo;
use Dvsa\Olcs\Api\Entity\Tm\TmEmployment as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class TmEmploymentTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' te';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchById(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->fetchById(834));

        // TmEmployment has no RefData associations, so withRefdata() contributes nothing.
        $this->assertSame(
            'SELECT te, cd, ad, cc' . self::FROM
            . ' LEFT JOIN te.contactDetails cd LEFT JOIN cd.address ad LEFT JOIN ad.countryCode cc'
            . ' WHERE te.id = :byId',
            $qb->getDQL(),
        );
        $this->assertSame(834, $qb->getParameter('byId')->getValue());
    }

    public function testFetchByTransportManager(): void
    {
        $qb = $this->createRealQb()->willReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByTransportManager(534));

        $this->assertSame(
            'SELECT te, cd, w0' . self::FROM
            . ' LEFT JOIN te.contactDetails cd LEFT JOIN cd.address w0'
            . ' WHERE te.transportManager = :tmId',
            $qb->getDQL(),
        );
        $this->assertSame(534, $qb->getParameter('tmId')->getValue());
    }

    public function testApplyListJoins(): void
    {
        $qb = $this->createRealQb();

        $previous = $this->newRealQb();
        $previous->select('other')->from(Entity::class, 'other');
        $this->queryBuilder->modifyQuery($previous);

        $this->sut->applyListJoins($qb);

        $this->assertSame(
            'SELECT te, cd, add, w0' . self::FROM
            . ' LEFT JOIN te.contactDetails cd LEFT JOIN cd.address add'
            . ' LEFT JOIN add.countryCode w0',
            $qb->getDQL(),
        );
        $this->assertSame('SELECT other FROM ' . Entity::class . ' other', $previous->getDQL());
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(QueryInterface::class);
        $query->expects('getTransportManager')->withNoArgs()->andReturn(12);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT te' . self::FROM . ' WHERE te.transportManager = :transportManager',
            $qb->getDQL(),
        );
        $this->assertSame(12, $qb->getParameter('transportManager')->getValue());
    }
}
