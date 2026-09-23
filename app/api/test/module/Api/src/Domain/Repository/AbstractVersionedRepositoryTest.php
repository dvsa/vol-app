<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\LetterAppendix;
use Dvsa\Olcs\Api\Domain\Repository\LetterIssue;
use Dvsa\Olcs\Api\Domain\Repository\LetterSection;
use Dvsa\Olcs\Api\Domain\Repository\LetterTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterAppendix as LetterAppendixEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue as LetterIssueEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection as LetterSectionEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodo as LetterTodoEntity;

/**
 * fetchById() is declared once on AbstractVersionedRepository and inherited by all four letter
 * repositories, so it is covered here rather than in each of them.
 */
final class AbstractVersionedRepositoryTest extends RepositoryTestCase
{
    /**
     * The current version is eager loaded: a letter is only ever useful with its content, and a
     * lazy load would be a second query per row.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('versionedRepositoryProvider')]
    public function testFetchById(string $repositoryClass, string $entityClass): void
    {
        $this->setUpRealSut($repositoryClass, true);

        $qb = $this->createRealQb();
        // Asserting the argument pins the default: ORM 3 types getResult() as string|int, and a
        // null default threw a TypeError on every letter generate (#1787).
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->fetchById(1));

        $this->assertSame(
            'SELECT m, cv FROM ' . $entityClass . ' m'
            . ' LEFT JOIN m.currentVersion cv'
            . ' WHERE m.id = :id',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('id')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('repositoryProvider')]
    public function testFetchByIdNotFound(string $repositoryClass): void
    {
        $this->setUpRealSut($repositoryClass, true);
        $this->createRealQb()->stubbedQuery()->expects('getResult')->andReturn([]);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Entity not found');

        $this->sut->fetchById(1);
    }

    /** A caller asking for array hydration gets it; the default is not forced on them. */
    #[\PHPUnit\Framework\Attributes\DataProvider('repositoryProvider')]
    public function testFetchByIdHonoursTheRequestedHydrationMode(string $repositoryClass): void
    {
        $this->setUpRealSut($repositoryClass, true);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn([['id' => 1]]);

        $this->assertSame(['id' => 1], $this->sut->fetchById(1, Query::HYDRATE_ARRAY));
    }

    public static function versionedRepositoryProvider(): \Iterator
    {
        yield 'letter issue' => [LetterIssue::class, LetterIssueEntity::class];
        yield 'letter appendix' => [LetterAppendix::class, LetterAppendixEntity::class];
        yield 'letter section' => [LetterSection::class, LetterSectionEntity::class];
        yield 'letter todo' => [LetterTodo::class, LetterTodoEntity::class];
    }

    public static function repositoryProvider(): \Iterator
    {
        foreach (self::versionedRepositoryProvider() as $name => [$repositoryClass]) {
            yield $name => [$repositoryClass];
        }
    }
}
