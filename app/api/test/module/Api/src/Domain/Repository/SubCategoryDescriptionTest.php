<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\SubCategoryDescription as Repo;
use Dvsa\Olcs\Api\Entity\System\SubCategoryDescription as Entity;
use Dvsa\Olcs\Transfer\Query\SubCategoryDescription\GetList;

final class SubCategoryDescriptionTest extends RepositoryTestCase
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
        yield 'by subCategory' => [['subCategory' => '212'], ' WHERE m.subCategory = :subCategory'];
    }
}
