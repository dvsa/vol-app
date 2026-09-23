<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKeyText as Repo;
use Dvsa\Olcs\Api\Entity\System\TranslationKeyText as Entity;

final class TranslationKeyTextTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchByParentLanguage(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getOneOrNullResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByParentLanguage(1, 2));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE m.translationKey = :translationKey AND m.language = :language',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('translationKey')->getValue());
        $this->assertSame(2, $qb->getParameter('language')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fetchAllProvider')]
    public function testFetchAll(?string $locale, string $expectedWhere): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchAll($locale, Query::HYDRATE_ARRAY));

        $this->assertSame(
            'SELECT m, t, l FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.translationKey t LEFT JOIN m.language l' . $expectedWhere,
            $qb->getDQL(),
        );
    }

    public static function fetchAllProvider(): \Iterator
    {
        yield 'with a locale' => ['en_GB', ' WHERE l.isoCode = :locale'];
        yield 'without a locale' => [null, ''];
    }
}
