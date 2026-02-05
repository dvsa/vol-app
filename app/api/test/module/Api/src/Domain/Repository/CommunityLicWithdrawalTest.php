<?php

declare(strict_types=1);

/**
 * Community Lic Withdrawal Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLicWithdrawal as CommunityLicWithdrawalRepo;

/**
 * Community Lic Withdrawal Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommunityLicWithdrawalTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(CommunityLicWithdrawalRepo::class);
    }

    public function testFetchByCommunityLicIds(): void
    {
        $ids = [1];
        $mockQb = m::mock();
        $mockQb->shouldReceive('expr->in')->with('m.communityLic', ':communityLic')->once()->andReturn('foo');
        $mockQb->shouldReceive('andWhere')->with('foo')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('communityLic', $ids)->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($mockQb);
        $mockQb->shouldReceive('getQuery->execute')->once()->andReturn('result');

        $this->assertEquals('result', $this->sut->fetchByCommunityLicIds($ids));
    }
}
