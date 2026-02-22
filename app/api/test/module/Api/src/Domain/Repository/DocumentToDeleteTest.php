<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\DocumentToDelete;
use Dvsa\Olcs\Api\Entity\Doc\DocumentToDelete as Entity;
use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * DocumentToDeleteTest
 */
class DocumentToDeleteTest extends RepositoryTestCase
{
    /**
     * @var (\Doctrine\ORM\QueryBuilder & \Mockery\MockInterface)
     */
    public $mockDqb;
    /**
     * @var (\Dvsa\Olcs\Transfer\Query\QueryInterface & \Mockery\MockInterface)
     */
    public $mockQi;
    /**
     * @var DocumentToDelete
     */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(DocumentToDelete::class);
        $this->mockDqb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $this->mockQi = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);
    }

    /**
     * @param $qb
     * @return m\MockInterface
     */
    public function getMockRepo(mixed $qb): mixed
    {
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        return $repo;
    }

    public function testFetchListOfDocumentToDelete(): void
    {
        /** @var QueryBuilder $qb */
        $mockQb = $this->createMockQb('{{QUERY}}');
        $now = (new DateTime())->format("Y-m-d H:i:s");

        $this->mockCreateQueryBuilder($mockQb);
        $mockQb->shouldReceive('getQuery->getResult')->with()->once()->andReturn(['FOO']);

        $this->assertSame(['FOO'], $this->sut->fetchListOfDocumentToDelete(77));

            $expected = '{{QUERY}}' .
            ' AND m.attempts < [[3]]' .
            ' AND m.documentStoreId != [[]]' .
            ' AND (m.processAfterDate IS NULL OR m.processAfterDate <= [[' . $now . ']])' .
            ' LIMIT 77';

        static::assertEquals($expected, $this->query);
    }

    public function testFetchListOfDocumentToDeleteIncludingPostponed(): void
    {
        /** @var QueryBuilder $qb */
        $mockQb = $this->createMockQb('{{QUERY}}');

        $this->mockCreateQueryBuilder($mockQb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf()
            ->shouldReceive('order')->with('m.processAfterDate', 'ASC')->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->with()->once()->andReturn(['FOO']);

        $this->assertSame(['FOO'], $this->sut->fetchListOfDocumentToDeleteIncludingPostponed(77));

        $expected = '{{QUERY}}' .
            ' AND m.attempts < [[3]]' .
            ' AND m.documentStoreId != [[]]' .
            ' LIMIT 77';

        static::assertEquals($expected, $this->query);
    }
}
