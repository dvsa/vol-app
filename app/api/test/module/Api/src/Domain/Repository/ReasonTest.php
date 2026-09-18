<?php

declare(strict_types=1);

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
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class ReasonTest extends RepositoryTestCase
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
        $mockQb->shouldReceive('expr')
            ->andReturn(new \Doctrine\ORM\Query\Expr());

        $mockQb->shouldReceive('andWhere')
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
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('isVisibleInInternal', true)
            ->andReturnSelf();

        $query = ReasonList::create(['isProposeToRevoke' => 'Y', 'isNi' => 'Y', 'goodsOrPsv' => 'lcat_gv']);

        $this->sut->applyListFilters($mockQb, $query);
    }

    /**
     * Branch tests where goodsOrPsv contains the string 'NULL'
     */
    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testApplyListFiltersNullGoodsOrPsv(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr')
            ->andReturn(new \Doctrine\ORM\Query\Expr());

        $mockQb->shouldReceive('andWhere')
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
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('isVisibleInInternal', true)
            ->andReturnSelf();

        $query = ReasonList::create(['isProposeToRevoke' => 'Y', 'isNi' => 'Y', 'goodsOrPsv' => 'NULL']);

        $this->sut->applyListFilters($mockQb, $query);
    }

    /**
     * Multi-column sort/order lists may contain whitespace after the comma: the transfer Order validator trims
     * each element, so 'sectionCode, description' / 'ASC, ASC' is valid input on the wire. Doctrine ORM 3.7
     * rejects ' ASC' outright, so the repository must trim too.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('sortAndOrderProvider')]
    public function testBuildDefaultListQueryOrdersBy(string $sort, string $order, string $expectedOrderBy): void
    {
        $this->setUpRealSut(Repo::class, true);
        $qb = $this->createRealQb();

        $this->sut->buildDefaultListQuery($qb, ReasonList::create(['sort' => $sort, 'order' => $order]));

        $this->assertStringEndsWith(' ORDER BY ' . $expectedOrderBy, $qb->getDQL());
    }

    public static function sortAndOrderProvider(): \Iterator
    {
        // Exactly what the internal PI data services send (VOL-6852).
        yield 'spaced multi column' => [
            'sectionCode, description',
            'ASC, ASC',
            'm.sectionCode ASC, m.description ASC',
        ];
        yield 'unspaced multi column' => [
            'sectionCode,description',
            'ASC,DESC',
            'm.sectionCode ASC, m.description DESC',
        ];
        yield 'single column' => [
            'sectionCode',
            'DESC',
            'm.sectionCode DESC',
        ];
        // Fewer directions than columns falls back to the first, which must also be trimmed.
        yield 'spaced sort with a single direction' => [
            'sectionCode, description',
            'DESC',
            'm.sectionCode DESC, m.description DESC',
        ];
    }
}
