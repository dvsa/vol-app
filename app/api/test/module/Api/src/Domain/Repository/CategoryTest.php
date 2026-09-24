<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\Category as Repo;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Transfer\Query as TransferQry;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Category::class)]
final class CategoryTest extends RepositoryTestCase
{
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

        $this->assertSame('RESULTS', $this->sut->fetchList(TransferQry\Category\GetList::create($query)));

        $this->assertSame($expectedDql, $qb->getDQL());
    }

    public static function dpTestApplyListX(): \Iterator
    {
        // withRefdata() joins taskAllocationType as w0 on every branch.
        $from = ' FROM ' . CategoryEntity::class . ' m LEFT JOIN m.taskAllocationType w0';
        $allThree = ' WHERE m.isTaskCategory = :isTaskCategory'
            . ' AND m.isDocCategory = :isDocCategory'
            . ' AND m.isScanCategory = :isScanCategory';

        yield 'no filters' => [[], 'SELECT m, w0' . $from];

        yield 'doc category excluded, so no template joins' => [
            [
                'isTaskCategory' => 'Y',
                'isDocCategory' => 'N',
                'isScanCategory' => 'Y',
                'isOnlyWithItems' => 'Y',
            ],
            'SELECT m, w0' . $from . $allThree,
        ];

        yield 'doc category without onlyWithItems' => [
            ['isDocCategory' => 'Y', 'isOnlyWithItems' => 'N'],
            'SELECT m, w0' . $from . ' WHERE m.isDocCategory = :isDocCategory',
        ];

        // The DISTINCT select replaces the refdata addSelect, leaving w0 joined but unselected.
        yield 'doc category with onlyWithItems joins templates' => [
            [
                'isTaskCategory' => 'N',
                'isDocCategory' => 'Y',
                'isScanCategory' => 'N',
                'isOnlyWithItems' => 'Y',
            ],
            'SELECT DISTINCT m' . $from
            . ' INNER JOIN ' . Entity\Doc\DocTemplate::class . ' dct WITH dct.category = m.id'
            . ' INNER JOIN ' . Entity\Doc\Document::class . ' dc WITH dc.id = dct.document'
            . $allThree,
        ];
    }
}
