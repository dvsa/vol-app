<?php

namespace Dvsa\OlcsTest\Api\Entity\Doc;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Cases\Statement;
use Dvsa\Olcs\Api\Entity\Doc\Document as Entity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Submission\Submission;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Api\Entity\Doc\Document
 * @covers \Dvsa\Olcs\Api\Entity\Doc\AbstractDocument
 */
class DocumentEntityTest extends EntityTester
{
    /** @var  string */
    protected $entityClass = Entity::class;

    /** @var  Entity | m\MockInterface */
    private $sut;

    public function setUp(): void
    {
        /** @var Entity $entity */
        $this->sut = m::mock(Entity::class)->makePartial();

        parent::setUp();
    }

    /**
     * tests the related organisation returns null when nothing is found
     */
    public function testGetRelatedOrganisationNotFound()
    {
        $this->assertNull($this->sut->getRelatedOrganisation());
    }

    /**
     * tests the related organisation is retrieved properly
     *
     * @dataProvider relatedOrganisationProvider
     *
     * @param $setterMethod
     * @param $relationClass
     */
    public function testGetRelatedOrganisation($setterMethod, $relationClass)
    {
        $organisation = m::mock(Organisation::class);
        $relation = m::mock($relationClass);
        $relation->shouldReceive('getRelatedOrganisation')->once()->andReturn($organisation);

        $this->sut->$setterMethod($relation);

        $this->assertEquals($organisation, $this->sut->getRelatedOrganisation());
    }

    /**
     * Provider for testGetRelatedOrganisation
     *
     * @return array
     */
    public function relatedOrganisationProvider()
    {
        return [
            ['setLicence', Licence::class],
            ['setApplication', Application::class],
            ['setTransportManager', TransportManager::class],
            ['setCase', Cases::class],
            ['setOperatingCentre', OperatingCentre::class],
            ['setBusReg', BusReg::class],
            ['setIrfoOrganisation', Organisation::class],
            ['setSubmission', Submission::class],
            ['setStatement', Statement::class],
            ['setEbsrSubmission', EbsrSubmission::class]
        ];
    }

    public function testGetRelatedLicenceNull()
    {
        static::assertNull($this->sut->getRelatedLicence());
    }

    /**
     * @dataProvider dpTestGetRelatedLicence
     */
    public function testGetRelatedLicence($relSetterMethod, $mockRelClass)
    {
        $mockLic = m::mock(Licence::class);

        if ($mockRelClass !== Licence::class) {
            $mockRelClass = m::mock($mockRelClass);
            $mockRelClass->shouldReceive('getLicence')->once()->andReturn($mockLic);
        } else {
            $mockRelClass = $mockLic;
        }

        $this->sut->$relSetterMethod($mockRelClass);

        static::assertSame($mockLic, $this->sut->getRelatedLicence());
    }

    public function dpTestGetRelatedLicence()
    {
        return [
            ['setLicence', Licence::class],
            ['setApplication', Application::class],
            ['setCase', Cases::class],
            ['setBusReg', BusReg::class],
        ];
    }
}
