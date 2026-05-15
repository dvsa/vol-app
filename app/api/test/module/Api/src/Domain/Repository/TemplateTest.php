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

    /**
     * VOL-7238 deliverable 7: when picking a "primary" variant id for the group-by-name
     * view's Edit action, we should prefer en_GB/md, fall back to en_GB/html, then to the
     * lowest id. Testing the private resolution rule via reflection — exercises the rule
     * cleanly without mocking the whole two-pass query.
     */
    public function testResolvePrimaryVariantIdPrefersEnGbMd(): void
    {
        $variants = [
            ['id' => 100, 'locale' => 'cy_GB', 'format' => 'html'],
            ['id' => 101, 'locale' => 'en_GB', 'format' => 'html'],
            ['id' => 113, 'locale' => 'en_GB', 'format' => 'md'],
            ['id' => 102, 'locale' => 'cy_GB', 'format' => 'md'],
        ];

        $this->assertSame(113, $this->callPrivate('resolvePrimaryVariantId', [$variants]));
    }

    public function testResolvePrimaryVariantIdFallsBackToEnGbHtmlWhenNoMd(): void
    {
        $variants = [
            ['id' => 100, 'locale' => 'cy_GB', 'format' => 'html'],
            ['id' => 101, 'locale' => 'en_GB', 'format' => 'html'],
            ['id' => 99, 'locale' => 'cy_GB', 'format' => 'plain'],
        ];

        $this->assertSame(101, $this->callPrivate('resolvePrimaryVariantId', [$variants]));
    }

    public function testResolvePrimaryVariantIdFallsBackToLowestIdForOddCases(): void
    {
        // en_CY-only edge case (e.g. the bsr-lta-email-notification row)
        $variants = [
            ['id' => 124, 'locale' => 'en_CY', 'format' => 'html'],
            ['id' => 121, 'locale' => 'en_CY', 'format' => 'plain'],
            ['id' => 127, 'locale' => 'en_CY', 'format' => 'md'],
        ];

        $this->assertSame(121, $this->callPrivate('resolvePrimaryVariantId', [$variants]));
    }

    private function callPrivate(string $method, array $args): mixed
    {
        $reflection = new \ReflectionMethod($this->sut, $method);
        $reflection->setAccessible(true);
        return $reflection->invokeArgs($this->sut, $args);
    }

    public function testFetchSiblings(): void
    {
        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $siblings = [
            ['id' => 2, 'locale' => 'cy_GB', 'format' => 'html'],
            ['id' => 3, 'locale' => 'en_GB', 'format' => 'md'],
        ];

        $queryBuilder->shouldReceive('select')->with('t.id, t.locale, t.format')->once()->andReturnSelf()
            ->shouldReceive('from')->with(Template::class, 't')->once()->andReturnSelf()
            ->shouldReceive('where')->with('t.name = :name')->once()->andReturnSelf()
            ->shouldReceive('andWhere')->with('t.id != :id')->once()->andReturnSelf()
            ->shouldReceive('setParameter')->with('name', 'auth-forgot-password')->once()->andReturnSelf()
            ->shouldReceive('setParameter')->with('id', 1)->once()->andReturnSelf()
            ->shouldReceive('orderBy')->with('t.locale', 'ASC')->once()->andReturnSelf()
            ->shouldReceive('addOrderBy')->with('t.format', 'ASC')->once()->andReturnSelf()
            ->shouldReceive('getQuery->getArrayResult')->andReturn($siblings);

        $this->assertSame($siblings, $this->sut->fetchSiblings('auth-forgot-password', 1));
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
