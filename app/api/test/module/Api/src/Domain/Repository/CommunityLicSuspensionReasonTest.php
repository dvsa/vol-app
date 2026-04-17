<?php

declare(strict_types=1);

/**
 * Community Lic Suspension Reason Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLicSuspensionReason as CommunityLicSuspensionReasonRepo;

/**
 * Community Lic Suspension Reason Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommunityLicSuspensionReasonTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(CommunityLicSuspensionReasonRepo::class);
    }

    public function testFetchBySuspensionIds(): void
    {
        $ids = [1];
        $mockQb = m::mock();
        $mockQb->shouldReceive('expr->in')
            ->with('m.communityLicSuspension', ':communityLicSuspension')
            ->once()
            ->andReturn('foo');
        $mockQb->shouldReceive('andWhere')->with('foo')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('communityLicSuspension', $ids)->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->execute')->once()->andReturn('result');

        $this->assertEquals('result', $this->sut->fetchBySuspensionIds($ids));
    }
}
