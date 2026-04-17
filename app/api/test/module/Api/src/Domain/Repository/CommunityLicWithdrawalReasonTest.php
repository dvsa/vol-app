<?php

declare(strict_types=1);

/**
 * Community Lic Withdrawal Reason Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLicWithdrawalReason as CommunityLicWithdrawalReasonRepo;

/**
 * Community Lic Withdrawal Reason Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommunityLicWithdrawalReasonTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(CommunityLicWithdrawalReasonRepo::class);
    }

    public function testFetchByWithdrawalIds(): void
    {
        $ids = [1];
        $mockQb = m::mock();
        $mockQb->shouldReceive('expr->in')
            ->with('m.communityLicWithdrawal', ':communityLicWithdrawal')
            ->once()
            ->andReturn('foo');
        $mockQb->shouldReceive('andWhere')->with('foo')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('communityLicWithdrawal', $ids)->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->execute')->once()->andReturn('result');

        $this->assertEquals('result', $this->sut->fetchByWithdrawalIds($ids));
    }
}
