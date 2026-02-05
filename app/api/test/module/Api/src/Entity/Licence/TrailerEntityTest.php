<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Licence\Trailer as Entity;
use Mockery as m;

/**
 * Trailer Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class TrailerEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetRelatedOrganisationWithNoLicence(): void
    {
        $sut = new Entity();

        $this->assertSame(null, $sut->getRelatedOrganisation());
    }

    public function testGetRelatedOrganisation(): void
    {
        $sut = new Entity();

        $mockLicence = m::mock();
        $mockLicence->shouldReceive('getOrganisation')->with()->once()->andReturn('ORG1');
        $sut->setLicence($mockLicence);

        $this->assertSame('ORG1', $sut->getRelatedOrganisation());
    }
}
