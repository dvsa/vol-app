<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Tm;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Tm\TmEmployment as Entity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Mockery as m;

/**
 * TmEmployment Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class TmEmploymentEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetRelatedOrganisation(): void
    {
        $sut = new Entity();

        $mockOrg1 = m::mock()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->twice()
            ->getMock();

        $mockOrg2 = m::mock()
            ->shouldReceive('getId')
            ->andReturn(2)
            ->twice()
            ->getMock();

        $tmApplications = new ArrayCollection();
        $tmApplication = m::mock()
            ->shouldReceive('getApplication')
            ->andReturn(
                m::mock()
                ->shouldReceive('getLicence')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getOrganisation')
                    ->andReturn($mockOrg1)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();
        $tmApplications->add($tmApplication);

        $tmLicences = new ArrayCollection();
        $tmLicence = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                ->shouldReceive('getOrganisation')
                ->andReturn($mockOrg2)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();
        $tmLicences->add($tmLicence);

        $tm = new TransportManager();
        $tm->setTmApplications($tmApplications);
        $tm->setTmLicences($tmLicences);

        $sut->setTransportManager($tm);
        $organisations = $sut->getRelatedOrganisation();
        $this->assertEquals(1, $organisations[1]->getId());
        $this->assertEquals(2, $organisations[2]->getId());
    }
}
