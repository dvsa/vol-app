<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\Reason as Repo;
use Dvsa\Olcs\Api\Entity\Pi\Reason as Entity;
use Dvsa\Olcs\Transfer\Query\Reason\ReasonList;

final class ReasonTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * Only reasons marked visible internally are ever listed, whatever else is asked for. A
     * transport manager reason has no operator type, which arrives on the wire as the string
     * 'NULL' so that it is not mistaken for an absent filter.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('listFilterProvider')]
    public function testApplyListFilters(array $criteria, string $expectedWhere, array $expectedParameters): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListFilters($qb, ReasonList::create($criteria));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE ' . $expectedWhere,
            $qb->getDQL(),
        );

        foreach ($expectedParameters as $name => $expected) {
            $this->assertSame($expected, $qb->getParameter($name)->getValue(), sprintf('parameter %s', $name));
        }

        $this->assertTrue($qb->getParameter('isVisibleInInternal')->getValue());
    }

    public static function listFilterProvider(): \Iterator
    {
        yield 'a goods licence in Northern Ireland' => [
            ['isProposeToRevoke' => 'Y', 'isNi' => 'Y', 'goodsOrPsv' => 'lcat_gv'],
            'm.isNi = :isNi AND m.goodsOrPsv = :goodsOrPsv'
            . ' AND m.isProposeToRevoke = :isProposeToRevoke'
            . ' AND m.isVisibleInInternal = :isVisibleInInternal',
            ['isNi' => true, 'goodsOrPsv' => 'lcat_gv', 'isProposeToRevoke' => true],
        ];
        yield 'a transport manager' => [
            ['isProposeToRevoke' => 'Y', 'isNi' => 'Y', 'goodsOrPsv' => 'NULL'],
            'm.isNi = :isNi AND m.goodsOrPsv IS NULL'
            . ' AND m.isProposeToRevoke = :isProposeToRevoke'
            . ' AND m.isVisibleInInternal = :isVisibleInInternal',
            ['isNi' => true, 'isProposeToRevoke' => true],
        ];
        // An 'N' is a value, not an absence, so it still filters.
        yield 'not in Northern Ireland' => [
            ['isNi' => 'N'],
            'm.isNi = :isNi AND m.isVisibleInInternal = :isVisibleInInternal',
            ['isNi' => false],
        ];
        yield 'no criteria' => [
            [],
            'm.isVisibleInInternal = :isVisibleInInternal',
            [],
        ];
    }

    /**
     * Multi-column sort/order lists may contain whitespace after the comma: the transfer Order validator trims
     * each element, so 'sectionCode, description' / 'ASC, ASC' is valid input on the wire. Doctrine ORM 3.7
     * rejects ' ASC' outright, so the repository must trim too.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('sortAndOrderProvider')]
    public function testBuildDefaultListQueryOrdersBy(string $sort, string $order, string $expectedOrderBy): void
    {
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
