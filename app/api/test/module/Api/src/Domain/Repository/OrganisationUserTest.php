<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\OrganisationUser as Repo;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser as Entity;

#[\PHPUnit\Framework\Attributes\CoversClass(Repo::class)]
final class OrganisationUserTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchByUserId(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn(['res']);

        $this->assertSame(['res'], $this->sut->fetchByUserId(1));

        // The user id is inlined rather than bound.
        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.user = 1',
            $qb->getDQL(),
        );
    }

    public function testDeleteByUserId(): void
    {
        $this->sut->expects('fetchByUserId')->with(1)->andReturn(['FOO']);
        $this->sut->expects('delete')->with('FOO');

        $this->assertNull($this->sut->deleteByUserId(1));
    }
}
