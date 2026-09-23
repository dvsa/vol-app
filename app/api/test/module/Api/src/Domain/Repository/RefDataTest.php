<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\RefData as Repo;
use Dvsa\Olcs\Api\Entity\System\RefData as Entity;
use Dvsa\Olcs\Transfer\Query\RefData\RefDataList;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Gedmo\Translatable\TranslatableListener;
use Mockery as m;

final class RefDataTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = $qb->stubbedQuery();
        $query->expects('setHint')->with(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class);
        $query->expects('setHint')->with(TranslatableListener::HINT_FALLBACK, 1);
        $query->expects('setHint')->with(TranslatableListener::HINT_TRANSLATABLE_LOCALE, 'en');

        $this->sut->applyListFilters($qb, RefDataList::create(['refDataCategory' => 'cat', 'language' => 'en']));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.refDataCategoryId = :category'
            . ' ORDER BY m.displayOrder ASC, m.description ASC',
            $qb->getDQL(),
        );
        $this->assertSame('cat', $qb->getParameter('category')->getValue());
    }

    public function testApplyListJoins(): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListJoins($qb);

        $this->assertSame(
            'SELECT m, p FROM ' . Entity::class . ' m LEFT JOIN m.parent p',
            $qb->getDQL(),
        );
    }

    public function testFetchByCategoryId(): void
    {
        $categoryId = 'permit_status';
        $refDataEntities = [m::mock(Entity::class), m::mock(Entity::class)];

        $qb = $this->newRealQb()->willReturn($refDataEntities);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame($refDataEntities, $this->sut->fetchByCategoryId($categoryId));

        $this->assertSame(
            'SELECT r FROM ' . Entity::class . ' r WHERE r.refDataCategoryId = ?1 ORDER BY r.displayOrder ASC',
            $qb->getDQL(),
        );
        $this->assertSame($categoryId, $qb->getParameter(1)->getValue());
    }
}
