<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\CommunityLic;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspensionReason as Entity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspension;
use Mockery as m;

/**
 * CommunityLicSuspensionReason Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class CommunityLicSuspensionReasonEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstruct(): void
    {
        $suspension = m::mock(CommunityLicSuspension::class);
        $type = 'type';
        $sut = new Entity($suspension, $type);
        $this->assertSame($suspension, $sut->getCommunityLicSuspension());
        $this->assertEquals($type, $sut->getType());
    }
}
