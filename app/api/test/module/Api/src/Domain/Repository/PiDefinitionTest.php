<?php

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
class PiDefinitionTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testApplyListFilters()
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr')
            ->andReturnSelf()
            ->shouldReceive('eq')
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('isNi', 'Y')
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('goodsOrPsv', 'lcat_gv')
            ->andReturnSelf();

        $query = PiDefinitionList::create(['isNi' => 'Y', 'goodsOrPsv' => 'lcat_gv']);

        $this->sut->applyListFilters($mockQb, $query);
    }

    public function testApplyListFiltersForTm()
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr')
            ->andReturnSelf()
            ->shouldReceive('eq')
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('isNi', 'Y')
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('isNull')
            ->with('m.goodsOrPsv')
            ->andReturnSelf();

        $query = PiDefinitionList::create(['isNi' => 'Y', 'goodsOrPsv' => 'NULL']);

        $this->sut->applyListFilters($mockQb, $query);
    }
}
