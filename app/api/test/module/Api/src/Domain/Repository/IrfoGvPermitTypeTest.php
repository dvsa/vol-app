<?php

declare(strict_types=1);

/**
 * IrfoGvPermitType test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\IrfoGvPermitType as Repo;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermitType as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

/**
 * IrfoGvPermitType test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class IrfoGvPermitTypeTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListFilters($qb, m::mock(QueryInterface::class));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m ORDER BY m.description ASC',
            $qb->getDQL(),
        );
    }

    public function testFetchActiveRecords(): void
    {
        $qb = $this->createRealQb()->willReturn(['Mocked Result']);

        $this->assertSame(['Mocked Result'], $this->sut->fetchActiveRecords());

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE m.displayUntil IS NULL OR m.displayUntil >= :today'
            . ' ORDER BY m.description ASC',
            $qb->getDQL(),
        );
    }
}
