<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\CommunityLicWithdrawal as Repo;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicWithdrawal as Entity;

final class CommunityLicWithdrawalTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchByCommunityLicIds(): void
    {
        $ids = [1];

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn('result');

        $this->assertSame('result', $this->sut->fetchByCommunityLicIds($ids));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.communityLic IN(:communityLic)',
            $qb->getDQL(),
        );
        $this->assertSame($ids, $qb->getParameter('communityLic')->getValue());
    }
}
