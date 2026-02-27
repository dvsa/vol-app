<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Template as Repo;
use Dvsa\Olcs\Api\Entity\Template\Template;
use Mockery as m;

/**
 * TemplateTest
 */
class TemplateTest extends RepositoryTestCase
{
    /** @var m\MockInterface|Repo */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchByLocaleFormatName(): void
    {
        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $locale = 'en_GB';
        $format = 'plain';
        $name = 'send-ecmt-successful';

        $template = m::mock(Template::class);

        $queryBuilder->shouldReceive('select')
            ->with('t')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(Template::class, 't')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('t.locale = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('t.format = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('t.name = ?3')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $locale)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $format)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(3, $name)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleResult')
            ->andReturn($template);

        $this->assertSame(
            $template,
            $this->sut->fetchByLocaleFormatName($locale, $format, $name)
        );
    }

    public function testFetchByLocaleFormatNameNotFound(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Resource not found');

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $locale = 'en_GB';
        $format = 'plain';
        $name = 'send-ecmt-successful';

        $queryBuilder->shouldReceive('select')
            ->with('t')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(Template::class, 't')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('t.locale = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('t.format = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('t.name = ?3')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $locale)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $format)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(3, $name)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleResult')
            ->andThrow(new NoResultException());

        $this->sut->fetchByLocaleFormatName($locale, $format, $name);
    }

    public function testFetchDistinctCategories(): void
    {
        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $categories = [
            [
                'id' => 4,
                'description' => 'Permits'
            ]
        ];

        $queryBuilder->shouldReceive('select')
            ->with('cat.description', 'cat.id')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(\Dvsa\Olcs\Api\Entity\Template\Template::class, 't')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('distinct')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->once()
            ->with('t.category', 'cat')
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('t.category IS NOT NULL')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getResult')
            ->andReturn($categories);

        $this->assertSame(
            $categories,
            $this->sut->fetchDistinctCategories()
        );
    }
}
