<?php

namespace Dvsa\OlcsTest\Api\Entity\Publication;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddDays;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddWorkingDays;
use Dvsa\Olcs\Api\Entity\Pi\PiHearing;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as Entity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Cases\Impounding as ImpoundingEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TmEntity;
use Mockery as m;
use Olcs\Logging\Log\Logger;

/**
 * PublicationLink Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class PublicationLinkEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function setUp(): void
    {
        $logWriter = new \Laminas\Log\Writer\Mock();
        $logger = new \Laminas\Log\Logger();
        $logger->addWriter($logWriter);

        Logger::setLogger($logger);
    }

    /**
     * Tests updateText throws an exception if the corresponding publication isn't of status New
     */
    public function testUpdateTextThrowsException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $publicationMock = $this->getPublicationMock(false);

        $entity = new Entity();
        $entity->setPublication($publicationMock);
        $entity->updateText('', '', '');
    }

    /**
     * @throws \Dvsa\Olcs\Api\Domain\Exception\ForbiddenException
     */
    public function testUpdateText()
    {
        $publicationMock = $this->getPublicationMock(true);

        $entity = new Entity();
        $entity->setPublication($publicationMock);

        $text1 = 'text1';
        $text2 = 'text2';
        $text3 = 'text3';

        $entity->updateText($text1, $text2, $text3);

        $this->assertEquals($text1, $entity->getText1());
        $this->assertEquals($text2, $entity->getText2());
        $this->assertEquals($text3, $entity->getText3());
    }

    /**
     * Tests createBusReg
     */
    public function testCreateBusReg()
    {
        $publicationSection = m::mock(PublicationSectionEntity::class);
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $licence = m::mock(LicenceEntity::class);
        $busReg = m::mock(BusRegEntity::class);
        $publication = $this->getPublicationMock(true);
        $text1 = 'text1';

        $entity = new Entity();
        $entity->createBusReg($busReg, $licence, $publication, $publicationSection, $trafficArea, $text1);

        $this->assertSame($publication, $entity->getPublication());
        $this->assertSame($publicationSection, $entity->getPublicationSection());
        $this->assertSame($trafficArea, $entity->getTrafficArea());
        $this->assertSame($licence, $entity->getLicence());
        $this->assertSame($busReg, $entity->getBusReg());
        $this->assertSame($text1, $entity->getText1());
    }

    /**
     * Tests creating bus reg throws ForbiddenException when necessary
     */
    public function testCreateBusRegThrowsException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $publicationSection = m::mock(PublicationSectionEntity::class);
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $licence = m::mock(LicenceEntity::class);
        $busReg = m::mock(BusRegEntity::class);
        $publication = $this->getPublicationMock(false);
        $text1 = 'text1';

        $entity = new Entity();
        $entity->createBusReg($busReg, $licence, $publication, $publicationSection, $trafficArea, $text1);
    }

    /**
     * Tests createTmPiHearing
     */
    public function testCreateTmPiHearing()
    {
        $publicationSection = m::mock(PublicationSectionEntity::class);
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $tm = m::mock(TmEntity::class);
        $pi = m::mock(PiEntity::class);
        $publication = $this->getPublicationMock(true);

        $entity = new Entity();
        $entity->createTmPiHearing($tm, $pi, $publication, $publicationSection, $trafficArea);

        $this->assertSame($publication, $entity->getPublication());
        $this->assertSame($publicationSection, $entity->getPublicationSection());
        $this->assertSame($trafficArea, $entity->getTrafficArea());
        $this->assertSame($tm, $entity->getTransportManager());
        $this->assertSame($pi, $entity->getPi());
    }

    /**
     * Tests creating tm pi hearing throws ForbiddenException when necessary
     */
    public function testCreateTmPiHearingThrowsException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $publicationSection = m::mock(PublicationSectionEntity::class);
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $tm = m::mock(TmEntity::class);
        $pi = m::mock(PiEntity::class);
        $publication = $this->getPublicationMock(false);

        $entity = new Entity();
        $entity->createTmPiHearing($tm, $pi, $publication, $publicationSection, $trafficArea);
    }

    /**
     * Tests createPiHearing
     */
    public function testCreatePiHearing()
    {
        $publicationSection = m::mock(PublicationSectionEntity::class);
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $licence = m::mock(LicenceEntity::class);
        $pi = m::mock(PiEntity::class);
        $publication = $this->getPublicationMock(true);

        $entity = new Entity();
        $entity->createPiHearing($licence, $pi, $publication, $publicationSection, $trafficArea);

        $this->assertSame($publication, $entity->getPublication());
        $this->assertSame($publicationSection, $entity->getPublicationSection());
        $this->assertSame($trafficArea, $entity->getTrafficArea());
        $this->assertSame($licence, $entity->getLicence());
        $this->assertSame($pi, $entity->getPi());
    }

    /**
     * Tests creating pi hearing throws ForbiddenException when necessary
     */
    public function testCreatePiHearingThrowsException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $publicationSection = m::mock(PublicationSectionEntity::class);
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $licence = m::mock(LicenceEntity::class);
        $pi = m::mock(PiEntity::class);
        $publication = $this->getPublicationMock(false);

        $entity = new Entity();
        $entity->createPiHearing($licence, $pi, $publication, $publicationSection, $trafficArea);
    }

    /**
     * Tests createLicence
     */
    public function testCreateLicence()
    {
        $publicationSection = m::mock(PublicationSectionEntity::class);
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $licence = m::mock(LicenceEntity::class);
        $publication = $this->getPublicationMock(true);

        $entity = new Entity();
        $entity->createLicence($licence, $publication, $publicationSection, $trafficArea);

        $this->assertSame($publication, $entity->getPublication());
        $this->assertSame($publicationSection, $entity->getPublicationSection());
        $this->assertSame($trafficArea, $entity->getTrafficArea());
        $this->assertSame($licence, $entity->getLicence());
    }

    /**
     * Tests creating licence throws ForbiddenException when necessary
     */
    public function testCreateLicenceThrowsException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $publicationSection = m::mock(PublicationSectionEntity::class);
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $licence = m::mock(LicenceEntity::class);
        $publication = $this->getPublicationMock(false);

        $entity = new Entity();
        $entity->createLicence($licence, $publication, $publicationSection, $trafficArea);
    }

    /**
     * Tests createApplication
     */
    public function testCreateApplication()
    {
        $publicationSection = m::mock(PublicationSectionEntity::class);
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $licence = m::mock(LicenceEntity::class);
        $application = m::mock(ApplicationEntity::class);
        $publication = $this->getPublicationMock(true);

        $entity = new Entity();
        $entity->createApplication($application, $licence, $publication, $publicationSection, $trafficArea);

        $this->assertSame($publication, $entity->getPublication());
        $this->assertSame($publicationSection, $entity->getPublicationSection());
        $this->assertSame($trafficArea, $entity->getTrafficArea());
        $this->assertSame($licence, $entity->getLicence());
        $this->assertSame($application, $entity->getApplication());
    }

    /**
     * Tests creating application throws ForbiddenException when necessary
     */
    public function testCreateApplicationThrowsException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $publicationSection = m::mock(PublicationSectionEntity::class);
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $licence = m::mock(LicenceEntity::class);
        $application = m::mock(ApplicationEntity::class);
        $publication = $this->getPublicationMock(false);

        $entity = new Entity();
        $entity->createApplication($application, $licence, $publication, $publicationSection, $trafficArea);
    }

    /**
     * Tests createImpounding
     */
    public function testCreateImpounding()
    {
        $impounding = m::mock(ImpoundingEntity::class);
        $publicationSection = m::mock(PublicationSectionEntity::class);
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $licence = m::mock(LicenceEntity::class);
        $application = m::mock(ApplicationEntity::class);
        $publication = $this->getPublicationMock(true);

        $entity = new Entity();
        $entity->createImpounding($impounding, $publication, $publicationSection, $trafficArea, $licence, $application);

        $this->assertSame($impounding, $entity->getImpounding());
        $this->assertSame($publication, $entity->getPublication());
        $this->assertSame($publicationSection, $entity->getPublicationSection());
        $this->assertSame($trafficArea, $entity->getTrafficArea());
        $this->assertSame($licence, $entity->getLicence());
        $this->assertSame($application, $entity->getApplication());
    }

    /**
     * Tests creating impounding throws ForbiddenException when necessary
     */
    public function testCreateImpoundingThrowsException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $impounding = m::mock(ImpoundingEntity::class);
        $publicationSection = m::mock(PublicationSectionEntity::class);
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $licence = m::mock(LicenceEntity::class);
        $application = m::mock(ApplicationEntity::class);
        $publication = $this->getPublicationMock(false);

        $entity = new Entity();
        $entity->createImpounding($impounding, $publication, $publicationSection, $trafficArea, $licence, $application);
    }

    public function testMaybeSetPublishAfterDateDo()
    {
        $sut = new Entity();

        /** @var PiEntity $pi */
        $pi = m::mock(PiEntity::class);
        $sut->setPi($pi);

        $sut->maybeSetPublishAfterDate();

        $dateTimeDaysProcessor = new AddDays();
        $dateTimeWorkingDaysProcessor = new AddWorkingDays($dateTimeDaysProcessor);
        $publishAfterDate = $dateTimeWorkingDaysProcessor->calculateDate(
            new DateTime(),
            PiHearing::PUBLISH_AFTER_DAYS
        );

        $this->assertEquals($publishAfterDate, $sut->getPublishAfterDate());
    }

    public function testMaybeSetPublishAfterDateDoNot()
    {
        $sut = new Entity();

        $sut->setPi(null);

        $sut->maybeSetPublishAfterDate();
        $this->assertNull($sut->getPublishAfterDate());
    }

    /**
     * Gets a publication entity mock with a true/false for whether it can be generated
     *
     * @param bool $canGenerate
     * @return m\MockInterface
     */
    private function getPublicationMock($canGenerate)
    {
        $publicationMock = m::mock(PublicationEntity::class);
        $publicationMock->shouldReceive('canGenerate')->once()->andReturn($canGenerate);

        return $publicationMock;
    }
}
