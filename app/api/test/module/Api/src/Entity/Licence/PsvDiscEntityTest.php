<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc as Entity;

/**
 * PsvDisc Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class PsvDiscEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCease(): void
    {
        $licence = m::mock(Licence::class)->makePartial();

        $psvDisc = new Entity($licence);

        $this->assertNull($psvDisc->getCeasedDate());
        $psvDisc->cease();
        $this->assertInstanceOf('DateTime', $psvDisc->getCeasedDate());

        $this->assertEquals(date('Y-m-d'), $psvDisc->getCeasedDate()->format('Y-m-d'));
    }

    public function testGetRelatedOrganisation(): void
    {
        $licence = m::mock(Licence::class)->makePartial();
        $sut = new Entity($licence);

        $mockLicence = m::mock();
        $mockLicence->shouldReceive('getOrganisation')->with()->once()->andReturn('ORG1');
        $sut->setLicence($mockLicence);

        $this->assertSame('ORG1', $sut->getRelatedOrganisation());
    }
}
