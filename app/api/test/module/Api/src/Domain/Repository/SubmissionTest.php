<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\Submission as Repo;
use Dvsa\Olcs\Api\Entity\Submission\Submission as Entity;

final class SubmissionTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchWithCaseAndLicenceById(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->withNoArgs()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchWithCaseAndLicenceById(1));

        $this->assertSame(
            'SELECT m, c, cl FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.case c LEFT JOIN c.licence cl'
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('byId')->getValue());
    }
}
