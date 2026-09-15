<?php

declare(strict_types=1);

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

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Doc\Document::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Doc\AbstractDocument::class)]
final class DocumentEntityTest extends EntityTester
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
    public function testGetRelatedOrganisationNotFound(): void
    {
        $this->assertNull($this->sut->getRelatedOrganisation());
    }

    /**
     * tests the related organisation is retrieved properly
     *
     *
     * @param $setterMethod
     * @param $relationClass
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('relatedOrganisationProvider')]
    public function testGetRelatedOrganisation(mixed $setterMethod, mixed $relationClass): void
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
     * @return \Iterator<(int | string), mixed>
     */
    public static function relatedOrganisationProvider(): \Iterator
    {
        yield ['setLicence', Licence::class];
        yield ['setApplication', Application::class];
        yield ['setTransportManager', TransportManager::class];
        yield ['setCase', Cases::class];
        yield ['setOperatingCentre', OperatingCentre::class];
        yield ['setBusReg', BusReg::class];
        yield ['setIrfoOrganisation', Organisation::class];
        yield ['setSubmission', Submission::class];
        yield ['setStatement', Statement::class];
        yield ['setEbsrSubmission', EbsrSubmission::class];
    }

    public function testGetRelatedLicenceNull(): void
    {
        $this->assertNotInstanceOf(\Dvsa\Olcs\Api\Entity\Licence\Licence::class, $this->sut->getRelatedLicence());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetRelatedLicence')]
    public function testGetRelatedLicence(mixed $relSetterMethod, mixed $mockRelClass): void
    {
        $mockLic = m::mock(Licence::class);

        if ($mockRelClass !== Licence::class) {
            $mockRelClass = m::mock($mockRelClass);
            $mockRelClass->shouldReceive('getLicence')->once()->andReturn($mockLic);
        } else {
            $mockRelClass = $mockLic;
        }

        $this->sut->$relSetterMethod($mockRelClass);

        $this->assertSame($mockLic, $this->sut->getRelatedLicence());
    }

    public static function dpTestGetRelatedLicence(): \Iterator
    {
        yield ['setLicence', Licence::class];
        yield ['setApplication', Application::class];
        yield ['setCase', Cases::class];
        yield ['setBusReg', BusReg::class];
    }
}
