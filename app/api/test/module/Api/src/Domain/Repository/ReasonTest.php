<?php

/**
 * Reason Repo Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Transfer\Query\Reason\ReasonList;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Reason as Repo;

/**
 * Reason Repo Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ReasonTest extends RepositoryTestCase
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
            ->with('isProposeToRevoke', 'Y')
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('goodsOrPsv', 'lcat_gv')
            ->andReturnSelf();

        $query = ReasonList::create(['isProposeToRevoke' => 'Y', 'isNi' => 'Y', 'goodsOrPsv' => 'lcat_gv']);

        $this->sut->applyListFilters($mockQb, $query);
    }

    /**
     * Branch tests where goodsOrPsv contains the string 'NULL'
     */
    public function testApplyListFiltersNullGoodsOrPsv()
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
            ->with('isProposeToRevoke', 'Y')
            ->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('goodsOrPsv', 'NULL')
            ->shouldReceive('isNull')
            ->andReturnSelf();

        $query = ReasonList::create(['isProposeToRevoke' => 'Y', 'isNi' => 'Y', 'goodsOrPsv' => 'NULL']);

        $this->sut->applyListFilters($mockQb, $query);
    }
}
