<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\CommunityLic;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicWithdrawalReason as Entity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicWithdrawal;
use Mockery as m;

/**
 * CommunityLicWithdrawalReason Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class CommunityLicWithdrawalReasonEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testUpdateReason(): void
    {
        $withdrawal = m::mock(CommunityLicWithdrawal::class);
        $type = 'type';
        $sut = new Entity($withdrawal, $type);
        $this->assertSame($withdrawal, $sut->getCommunityLicWithdrawal());
        $this->assertEquals($type, $sut->getType());
    }
}
