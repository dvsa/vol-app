<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Dvsa\Olcs\Api\Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Application\AbstractApplicationOrganisationPerson::class)]
final class ApplicationOrganisationPersonEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity\Application\ApplicationOrganisationPerson::class;

    public function testConstrunctor(): void
    {
        /** @var Entity\Application\Application $mockApp */
        $mockApp = m::mock(Entity\Application\Application::class);
        /** @var Entity\Organisation\Organisation $mockOrg */
        $mockOrg = m::mock(Entity\Organisation\Organisation::class);
        /** @var Entity\Person\Person $mockPerson */
        $mockPerson = m::mock(Entity\Person\Person::class);

        $sut = new Entity\Application\ApplicationOrganisationPerson($mockApp, $mockOrg, $mockPerson);

        $this->assertSame($mockApp, $sut->getApplication());
        $this->assertSame($mockOrg, $sut->getOrganisation());
        $this->assertSame($mockPerson, $sut->getPerson());
    }
}
