<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKeyText as Repo;

/**
 * TranslationKeyTextTest
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class TranslationKeyTextTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchByParentLanguage(): void
    {
        $queryBuilder = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($queryBuilder);

        $queryBuilder->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getOneOrNullResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );

        self::assertEquals(['RESULTS'], $this->sut->fetchByParentLanguage(1, 2));

        $expectedQuery = 'BLAH '
            . 'AND m.translationKey = [[1]] '
            . 'AND m.language = [[2]]';

        self::assertEquals($expectedQuery, $this->query);
    }

    public function testFetchAll(): void
    {
        $locale = 'en_GB';
        $hydrationMode = Query::HYDRATE_ARRAY;
        $queryResult = ['RESULTS'];
        $expectedQuery = 'initial select AND l.isoCode = [[' . $locale . ']]';

        $doctrineQuery = m::mock();
        $doctrineQuery->expects('getResult')->with($hydrationMode)->andReturn($queryResult);

        $mockQb = $this->createMockQb('initial select');
        $mockQb->expects('getQuery')->andReturn($doctrineQuery);

        $this->mockCreateQueryBuilder($mockQb);

        $this->queryBuilder->expects('with')->with('translationKey', 't')->andReturnSelf();
        $this->queryBuilder->expects('with')->with('language', 'l')->andReturnSelf();
        $this->queryBuilder->expects('modifyQuery')->with($mockQb)->andReturnSelf();

        self::assertEquals(['RESULTS'], $this->sut->fetchAll($locale, $hydrationMode));
        self::assertEquals($expectedQuery, $this->query);
    }
}
