<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\CommunityLic;

use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicWithdrawal as Entity;
use Mockery as m;

/**
 * CommunityLicWithdrawal Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class CommunityLicWithdrawalEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testUpdateCommunityLicWithdrawal(): void
    {
        /** @var CommunityLic $communityLic */
        $communityLic = new CommunityLic();
        $communityLic->setId(1);

        /** @var Entity|m\Mock $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->updateCommunityLicWithdrawal($communityLic);

        $dateTimeObject = new \DateTime('29 June 2017');
        $sut->setStartDate($dateTimeObject);

        $this->assertEquals($communityLic, $sut->getCommunityLic());
        $this->assertEquals($dateTimeObject, $sut->getStartDate());
    }
}
