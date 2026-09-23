<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\Decision as Repo;
use Dvsa\Olcs\Api\Entity\Pi\Decision as Entity;
use Dvsa\Olcs\Transfer\Query\Decision\DecisionList;

final class DecisionTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('listFilterProvider')]
    public function testApplyListFilters(array $data, string $expectedWhere): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchList(DecisionList::create($data)));

        $this->assertSame(
            'SELECT m, w0 FROM ' . Entity::class . ' m LEFT JOIN m.goodsOrPsv w0' . $expectedWhere,
            $qb->getDQL(),
        );
        $this->assertTrue($qb->getParameter('isNi')->getValue());
    }

    public static function listFilterProvider(): \Iterator
    {
        yield 'goodsOrPsv value' => [
            ['isNi' => 'Y', 'goodsOrPsv' => 'lcat_psv'],
            ' WHERE m.isNi = :isNi AND m.goodsOrPsv = :goodsOrPsv',
        ];
        // The literal string 'NULL' switches the filter to an IS NULL check.
        yield 'goodsOrPsv NULL means transport manager decisions' => [
            ['isNi' => 'Y', 'goodsOrPsv' => 'NULL'],
            ' WHERE m.isNi = :isNi AND m.goodsOrPsv IS NULL',
        ];
    }
}
