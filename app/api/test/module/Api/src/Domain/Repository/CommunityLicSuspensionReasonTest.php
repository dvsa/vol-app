<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\CommunityLicSuspensionReason as Repo;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspensionReason as Entity;

final class CommunityLicSuspensionReasonTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchBySuspensionIds(): void
    {
        $ids = [1];

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn('result');

        $this->assertSame('result', $this->sut->fetchBySuspensionIds($ids));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.communityLicSuspension IN(:communityLicSuspension)',
            $qb->getDQL(),
        );
        $this->assertSame($ids, $qb->getParameter('communityLicSuspension')->getValue());
    }
}
