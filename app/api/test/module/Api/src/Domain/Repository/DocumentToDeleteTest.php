<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\DocumentToDelete as Repo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentToDelete as Entity;

final class DocumentToDeleteTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchListOfDocumentToDelete(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn(['FOO']);

        $this->assertSame(['FOO'], $this->sut->fetchListOfDocumentToDelete(77));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE m.attempts < :maxAttempts AND m.documentStoreId <> :documentStoreId'
            . ' AND (m.processAfterDate IS NULL OR m.processAfterDate <= :now)',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::MAX_ATTEMPTS, $qb->getParameter('maxAttempts')->getValue());
        $this->assertSame('', $qb->getParameter('documentStoreId')->getValue());
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $qb->getParameter('now')->getValue(),
        );
        $this->assertSame(77, $qb->getMaxResults());
    }

    public function testFetchListOfDocumentToDeleteIncludingPostponed(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn(['FOO']);

        $this->assertSame(['FOO'], $this->sut->fetchListOfDocumentToDeleteIncludingPostponed(77));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE m.attempts < :maxAttempts AND m.documentStoreId <> :documentStoreId'
            . ' ORDER BY m.processAfterDate ASC',
            $qb->getDQL(),
        );
        $this->assertSame(77, $qb->getMaxResults());
    }
}
