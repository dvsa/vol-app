<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\SubCategory as Repo;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Transfer\Query as TransferQry;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\SubCategory::class)]
final class SubCategoryTest extends RepositoryTestCase
{
    public const int CATEGORY = 90001;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestApplyListX')]
    public function testApplyListX(array $query, string $expectedDql): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn('RESULTS');

        $this->assertSame('RESULTS', $this->sut->fetchList(TransferQry\SubCategory\GetList::create($query)));

        $this->assertSame($expectedDql, $qb->getDQL());
    }

    public static function dpTestApplyListX(): \Iterator
    {
        // SubCategory has no RefData associations, so withRefdata() adds nothing.
        $from = ' FROM ' . SubCategoryEntity::class . ' m';
        $order = ' ORDER BY m.id ASC';
        $allFour = ' WHERE m.isTask = :isTaskCategory'
            . ' AND m.isDoc = :isDocCategory'
            . ' AND m.isScan = :isScanCategory'
            . ' AND m.category = :category';

        yield 'no filters' => [[], 'SELECT m' . $from . $order];

        yield 'doc category excluded, so no template joins' => [
            [
                'isTaskCategory' => 'Y',
                'isDocCategory' => 'N',
                'isScanCategory' => 'Y',
                'isOnlyWithItems' => 'Y',
                'category' => self::CATEGORY,
            ],
            'SELECT m' . $from . $allFour . $order,
        ];

        yield 'doc category without onlyWithItems' => [
            ['isDocCategory' => 'Y', 'isOnlyWithItems' => 'N'],
            'SELECT m' . $from . ' WHERE m.isDoc = :isDocCategory' . $order,
        ];

        yield 'doc category with onlyWithItems joins templates' => [
            [
                'isTaskCategory' => 'N',
                'isDocCategory' => 'Y',
                'isScanCategory' => 'N',
                'isOnlyWithItems' => 'Y',
                'category' => self::CATEGORY,
            ],
            'SELECT DISTINCT m' . $from
            . ' INNER JOIN ' . Entity\Doc\DocTemplate::class . ' dct'
            . ' WITH dct.category = m.category AND dct.subCategory = m.id'
            . ' INNER JOIN ' . Entity\Doc\Document::class . ' dc WITH dc.id = dct.document'
            . $allFour . $order,
        ];
    }
}
