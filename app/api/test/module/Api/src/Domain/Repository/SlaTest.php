<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\System\Sla as Entity;
use Mockery as m;

final class SlaTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repository\Sla::class);
    }

    public function testFetchByCategories(): void
    {
        $categories = ['foo', 'bar'];

        $qb = $this->createRealQb()->willReturn('foobar');

        $this->assertSame('foobar', $this->sut->fetchByCategories($categories));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.category IN(:category)',
            $qb->getDQL(),
        );
        $this->assertSame($categories, $qb->getParameter('category')->getValue());
    }

    public function testFetchByCategoryFieldAndCompareTo(): void
    {
        $sla = m::mock(Entity::class);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn($sla);

        $this->assertSame($sla, $this->sut->fetchByCategoryFieldAndCompareTo('cat', 'fld', 'cmp'));

        // andWhere() is variadic, so all three predicates land. The old double recorded only
        // the first argument, which is why the field and compareTo checks went unasserted.
        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE m.category = :category AND m.field = :field AND m.compareTo = :compareTo',
            $qb->getDQL(),
        );
        $this->assertSame('cat', $qb->getParameter('category')->getValue());
        $this->assertSame('fld', $qb->getParameter('field')->getValue());
        $this->assertSame('cmp', $qb->getParameter('compareTo')->getValue());
    }
}
