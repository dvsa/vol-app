<?php

declare(strict_types=1);

/**
 * PiDefinition Repo Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Transfer\Query\Cases\Pi\PiDefinitionList;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\PiDefinition as Repo;

/**
 * PiDefinition Repo Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class PiDefinitionTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(QueryBuilder::class);
        $expr = new \Doctrine\ORM\Query\Expr();

        $mockQb->shouldReceive('expr')
            ->andReturn($expr);

        $mockQb->shouldReceive('andWhere')
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->andReturnSelf();

        $query = PiDefinitionList::create(['isNi' => 'Y', 'goodsOrPsv' => 'lcat_gv']);

        $this->sut->applyListFilters($mockQb, $query);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testApplyListFiltersForTm(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(QueryBuilder::class);
        $expr = new \Doctrine\ORM\Query\Expr();

        $mockQb->shouldReceive('expr')
            ->andReturn($expr);

        $mockQb->shouldReceive('andWhere')
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->andReturnSelf();

        $query = PiDefinitionList::create(['isNi' => 'Y', 'goodsOrPsv' => 'NULL']);

        $this->sut->applyListFilters($mockQb, $query);
    }
}
