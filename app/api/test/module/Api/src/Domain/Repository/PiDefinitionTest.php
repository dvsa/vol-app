<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\PiDefinition as Repo;
use Dvsa\Olcs\Api\Entity\Pi\PiDefinition as Entity;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\PiDefinitionList;

final class PiDefinitionTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * Transport manager definitions have no operator type, so 'NULL' arrives on the wire as the
     * string and has to become an IS NULL rather than a bound comparison.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('goodsOrPsvProvider')]
    public function testApplyListFilters(string $goodsOrPsv, string $expectedCondition): void
    {
        $qb = $this->createRealQb();

        $query = PiDefinitionList::create(['isNi' => 'Y', 'goodsOrPsv' => $goodsOrPsv]);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE m.isNi = :isNi AND ' . $expectedCondition
            . ' AND m.isVisibleInInternal = :isVisibleInInternal',
            $qb->getDQL(),
        );
        // The transfer query casts the Y/N flag to a boolean before it reaches the repository.
        $this->assertTrue($qb->getParameter('isNi')->getValue());
        $this->assertTrue($qb->getParameter('isVisibleInInternal')->getValue());
    }

    public static function goodsOrPsvProvider(): \Iterator
    {
        yield 'goods' => ['lcat_gv', 'm.goodsOrPsv = :goodsOrPsv'];
        yield 'transport manager' => ['NULL', 'm.goodsOrPsv IS NULL'];
    }
}
