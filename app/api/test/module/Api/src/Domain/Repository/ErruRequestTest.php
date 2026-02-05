<?php

declare(strict_types=1);

/**
 * ErruRequest test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\ErruRequest as ErruRequestRepo;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest;
use Doctrine\ORM\Query\Expr\Comparison;

/**
 * ErruRequest test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ErruRequestTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(ErruRequestRepo::class, true);
    }

    /**
     * Test existsByWorkflowId method
     *
     * @param array $result
     * @param bool $recordFound
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('existsByWorkflowIdProvider')]
    public function testExistsByWorkflowId(mixed $result, mixed $recordFound): void
    {
        $workflowId = '123456';
        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);
        $doctrineComparison = m::mock(Comparison::class);

        $this->em->shouldReceive('getRepository')->with(ErruRequest::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('m')->once()->andReturn($qb);

        $qb->shouldReceive('expr->eq')->with('m.workflowId', ':workflowId')->once()->andReturn($doctrineComparison);
        $qb->shouldReceive('where')->with($doctrineComparison)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('workflowId', $workflowId)->once()->andReturnSelf();
        $qb->shouldReceive('setMaxResults')->with(1)->once()->andReturnSelf();
        $qb->shouldReceive('getQuery->getResult')->with()->once()->andReturn($result);

        $this->assertSame($recordFound, $this->sut->existsByWorkflowId($workflowId));
    }

    /**
     * Data provider for testExistsByLicNo
     *
     * @return array
     */
    public static function existsByWorkflowIdProvider(): array
    {
        return [
            [[0 => 'Result'], true],
            [[], false]
        ];
    }
}
