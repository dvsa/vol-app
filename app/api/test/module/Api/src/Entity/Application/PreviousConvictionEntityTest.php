<?php

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Application\PreviousConviction
 * @covers Dvsa\Olcs\Api\Entity\Application\AbstractPreviousConviction
 */
class PreviousConvictionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetRelatedOrganisationWithApplication()
    {
        $sut = new Entity();

        $this->assertSame(null, $sut->getRelatedOrganisation());
    }

    public function testGetCalculatedValues()
    {
        /** @var Application $mockApp */
        $mockApp = m::mock(Application::class);

        $actual = (new Entity($mockApp))
            ->setApplication($mockApp)
            ->jsonSerialize();

        static::assertEquals(null, $actual['application']);
    }

    public function testGetRelatedOrganisation()
    {
        $sut = new Entity();

        $mockApplication = m::mock();
        $mockApplication->shouldReceive('getLicence->getOrganisation')->with()->once()->andReturn('ORG1');
        $sut->setApplication($mockApplication);

        $this->assertSame('ORG1', $sut->getRelatedOrganisation());
    }
}
