<?php

declare(strict_types=1);

/**
 * Community Lic Suspension Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLicSuspension as CommunityLicSuspensionRepo;

/**
 * Community Lic Suspension Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommunityLicSuspensionTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(CommunityLicSuspensionRepo::class);
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
