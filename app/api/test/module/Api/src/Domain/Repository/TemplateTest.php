<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Template as Repo;
use Dvsa\Olcs\Api\Entity\Template\Template as Entity;
use Dvsa\OlcsTest\Support\TestQueryBuilder;
use Mockery as m;

final class TemplateTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' t';

    #[\Override]
    public function setUp(): void
    {
        // Not a partial mock: resolvePrimaryVariantId() is private and reached by reflection.
        $this->setUpRealSut(Repo::class);
    }

    /** A template is identified by all three of locale, format and name, never by name alone. */
    public function testFetchByLocaleFormatName(): void
    {
        $template = m::mock(Entity::class);

        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getSingleResult')->withNoArgs()->andReturn($template);

        $this->assertSame(
            $template,
            $this->sut->fetchByLocaleFormatName('en_GB', 'plain', 'send-ecmt-successful'),
        );

        $this->assertSame(
            'SELECT t' . self::FROM
            . ' WHERE t.locale = ?1 AND t.format = ?2 AND t.name = ?3',
            $qb->getDQL(),
        );
        $this->assertSame('en_GB', $qb->getParameter(1)->getValue());
        $this->assertSame('plain', $qb->getParameter(2)->getValue());
        $this->assertSame('send-ecmt-successful', $qb->getParameter(3)->getValue());
    }

    public function testFetchByLocaleFormatNameNotFound(): void
    {
        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getSingleResult')->withNoArgs()->andThrow(new NoResultException());

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Resource not found');

        $this->sut->fetchByLocaleFormatName('en_GB', 'plain', 'send-ecmt-successful');
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
        return $reflection->invokeArgs($this->sut, $args);
    }

    /** The row being edited is excluded: it is not a sibling of itself. */
    public function testFetchSiblings(): void
    {
        $siblings = [
            ['id' => 2, 'locale' => 'cy_GB', 'format' => 'html'],
            ['id' => 3, 'locale' => 'en_GB', 'format' => 'md'],
        ];

        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getArrayResult')->withNoArgs()->andReturn($siblings);

        $this->assertSame($siblings, $this->sut->fetchSiblings('auth-forgot-password', 1));

        $this->assertSame(
            'SELECT t.id, t.locale, t.format' . self::FROM
            . ' WHERE t.name = :name AND t.id != :id'
            . ' ORDER BY t.locale ASC, t.format ASC',
            $qb->getDQL(),
        );
        $this->assertSame('auth-forgot-password', $qb->getParameter('name')->getValue());
        $this->assertSame(1, $qb->getParameter('id')->getValue());
    }

    public function testFetchDistinctCategories(): void
    {
        $categories = [['id' => 4, 'description' => 'Permits']];

        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn($categories);

        $this->assertSame($categories, $this->sut->fetchDistinctCategories());

        $this->assertSame(
            'SELECT DISTINCT cat.description, cat.id' . self::FROM
            . ' INNER JOIN t.category cat'
            . ' WHERE t.category IS NOT NULL',
            $qb->getDQL(),
        );
    }

    private function expectEntityManagerQb(): TestQueryBuilder
    {
        $qb = $this->newRealQb();

        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        return $qb;
    }
}
