<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\DocTemplate as Repo;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate as Entity;
use Dvsa\Olcs\Transfer\Query\DocTemplate\GetList;

final class DocTemplateTest extends RepositoryTestCase
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
        $this->sut->expects('fetchPaginatedList')->andReturn('RESULTS');

        $this->assertSame('RESULTS', $this->sut->fetchList(GetList::create($data)));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m' . $expectedWhere . ' ORDER BY m.id ASC',
            $qb->getDQL(),
        );
    }

    public static function listFilterProvider(): \Iterator
    {
        yield 'no filters' => [[], ''];
        yield 'category and subCategory' => [
            ['category' => 1, 'subCategory' => 12],
            ' WHERE m.category = :category AND m.subCategory = :subCategory',
        ];
    }
}
