<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\SubmissionAction as Repo;
use Dvsa\Olcs\Api\Entity\Submission\SubmissionAction as Entity;

final class SubmissionActionTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchByIdJoinsReasonsOnItsOwnQuery(): void
    {
        $previous = $this->newRealQb();
        $previous->select('other')->from(Entity::class, 'other');
        $this->queryBuilder->modifyQuery($previous);
        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->fetchById(7));
        $this->assertSame(
            'SELECT m, w0, w1 FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.actionTypes w0 LEFT JOIN m.reasons w1 WHERE m.id = :byId',
            $qb->getDQL(),
        );
        $this->assertSame('SELECT other FROM ' . Entity::class . ' other', $previous->getDQL());
    }
}
