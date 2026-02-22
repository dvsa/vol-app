<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Licence\LicenceReadAudit as Entity;
use Mockery as m;

/**
 * LicenceReadAudit Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class LicenceReadAuditEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstructor(): void
    {
        $user = m::mock(User::class);
        $licence = m::mock(Licence::class);

        $sut = new Entity($user, $licence);

        $this->assertSame($user, $sut->getUser());
        $this->assertSame($licence, $sut->getLicence());
    }
}
