<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\CommunityLicWithdrawalReason as Repo;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicWithdrawalReason as Entity;

final class CommunityLicWithdrawalReasonTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchByWithdrawalIds(): void
    {
        $ids = [1];

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn('result');

        $this->assertSame('result', $this->sut->fetchByWithdrawalIds($ids));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.communityLicWithdrawal IN(:communityLicWithdrawal)',
            $qb->getDQL(),
        );
        $this->assertSame($ids, $qb->getParameter('communityLicWithdrawal')->getValue());
    }
}
