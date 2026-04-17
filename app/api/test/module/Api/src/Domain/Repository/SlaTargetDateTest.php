<?php

declare(strict_types=1);

/**
 * SlaTargetDate Repo Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * SlaTargetDate Repo Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class SlaTargetDateTest extends RepositoryTestCase
{
    /**
     * @var m\MockInterface|Repository\SlaTargetDate
     */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repository\SlaTargetDate::class);
    }

    public function testFetchUsingEntityIdAndType(): void
    {
        $entityType = 'document';
        $entityId = 100;

        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery->getSingleResult')->once()->andReturn('foobar');

        $result = $this->sut->fetchUsingEntityIdAndType($entityType, $entityId);

        $this->assertEquals('QUERY AND m.document = [[100]]', $this->query);

        $this->assertEquals('foobar', $result);
    }

    public function testFetchByDocumentId(): void
    {
        $documentId = 1;

        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery->getResult')->once()->andReturn('foobar');

        $result = $this->sut->fetchByDocumentId($documentId);

        $this->assertEquals('QUERY AND m.document = [[' . $documentId . ']]', $this->query);

        $this->assertEquals('foobar', $result);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repository\SlaTargetDate::class, true);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr')
            ->andReturnSelf()
            ->shouldReceive('eq')
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('byDocument', 100)
            ->andReturnSelf();

        $mockQ = m::mock(QueryInterface::class);
        $mockQ->shouldReceive('getEntityType')
            ->andReturn('document')
            ->shouldReceive('getEntityId')
            ->andReturn(100);

        $this->sut->applyListFilters($mockQb, $mockQ);
    }
}
