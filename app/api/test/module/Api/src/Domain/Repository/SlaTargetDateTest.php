<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\System\SlaTargetDate as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class SlaTargetDateTest extends RepositoryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repository\SlaTargetDate::class, true);
    }

    public function testFetchUsingEntityIdAndType(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn('foobar');

        $this->assertSame('foobar', $this->sut->fetchUsingEntityIdAndType('document', 100));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.document = :byEntityId',
            $qb->getDQL(),
        );
        $this->assertSame(100, $qb->getParameter('byEntityId')->getValue());
    }

    public function testFetchByDocumentId(): void
    {
        $qb = $this->createRealQb()->willReturn('foobar');

        $this->assertSame('foobar', $this->sut->fetchByDocumentId(1));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.document = :documentId',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('documentId')->getValue());
    }

    /**
     * The entity type names both the column and the parameter, so 'document' yields
     * m.document = :byDocument.
     */
    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getEntityType')->andReturn('document');
        $query->shouldReceive('getEntityId')->andReturn(100);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.document = :byDocument',
            $qb->getDQL(),
        );
        $this->assertSame(100, $qb->getParameter('byDocument')->getValue());
    }
}
