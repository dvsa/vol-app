<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Application;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\Application\Text3 as ApplicationText3;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class Text3Test
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Text3Test extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestProcessSection')]
    public function testProcessSection(mixed $expectTextSet, mixed $sectionId): void
    {
        $sut = new ApplicationText3();

        $organisation = new Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);

        $publicationSection = new PublicationSection();
        $publicationSection->setId($sectionId);

        $publicationLink = new PublicationLink();
        $publicationLink->setApplication($application);
        $publicationLink->setPublicationSection($publicationSection);

        $input = [
            'licenceAddress' => 'LICENCE_ADDRESS',
            'conditionUndertaking' => [],
        ];

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        if ($expectTextSet) {
            $this->assertSame('LICENCE_ADDRESS', $publicationLink->getText3());
        } else {
            $this->assertNull($publicationLink->getText3());
        }
    }

    public static function dataProviderTestProcessSection(): array
    {
        return [
            [true, PublicationSection::APP_NEW_SECTION],
            [true, PublicationSection::APP_GRANTED_SECTION],
            [false, PublicationSection::APP_GRANT_NOT_TAKEN_SECTION],
            [false, PublicationSection::APP_REFUSED_SECTION],
            [false, PublicationSection::APP_WITHDRAWN_SECTION],
        ];
    }

    public function testProcessOc(): void
    {
        $sut = new ApplicationText3();

        $organisation = new Organisation();
        $licence = new Licence($organisation, new RefData());

        $application = m::mock(Application::class)->makePartial();
        $application->initCollections();
        $application->setLicence($licence);
        $application->setIsVariation(false);
        $application->shouldReceive('isVehicleTypeMixedWithLgv')
            ->withNoArgs()
            ->andReturn(false);

        $address = new Address();
        $address->setAddressLine1('ADDRESS1');
        $oc = new OperatingCentre();
        $oc->setAddress($address);
        $aoc = new ApplicationOperatingCentre($application, $oc);
        $aoc->setNoOfVehiclesRequired(5);
        $aoc->setNoOfTrailersRequired(7);
        $application->addOperatingCentres($aoc);

        $publicationSection = new PublicationSection();
        $publicationSection->setId(PublicationSection::APP_GRANTED_SECTION);

        $publicationLink = new PublicationLink();
        $publicationLink->setApplication($application);
        $publicationLink->setPublicationSection($publicationSection);

        $input = [
            'licenceAddress' => 'LICENCE_ADDRESS',
            'conditionUndertaking' => [],
        ];

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $this->assertSame(
            "LICENCE_ADDRESS\nOperating Centre: ADDRESS1\nAuthorisation: 5 vehicle(s), 7 trailer(s)",
            $publicationLink->getText3()
        );
    }

    public function testProcessOcS4Ignored(): void
    {
        $sut = new ApplicationText3();

        $organisation = new Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);

        $address = new Address();
        $address->setAddressLine1('ADDRESS1');
        $oc = new OperatingCentre();
        $oc->setAddress($address);
        $aoc = new ApplicationOperatingCentre($application, $oc);
        $aoc->setNoOfVehiclesRequired(5);
        $aoc->setNoOfTrailersRequired(7);
        $aoc->setS4(m::mock(\Dvsa\Olcs\Api\Entity\Application\S4::class));
        $application->addOperatingCentres($aoc);

        $publicationSection = new PublicationSection();
        $publicationSection->setId(PublicationSection::APP_NEW_SECTION);

        $publicationLink = new PublicationLink();
        $publicationLink->setApplication($application);
        $publicationLink->setPublicationSection($publicationSection);

        $input = [
            'licenceAddress' => 'LICENCE_ADDRESS',
            'conditionUndertaking' => [],
        ];

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $this->assertSame(
            "LICENCE_ADDRESS",
            $publicationLink->getText3()
        );
    }

    public function testProcessTransportManagers(): void
    {
        $sut = new ApplicationText3();

        $organisation = new Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);

        $tm1 = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $tm1->setHomeCd(new \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails(new RefData()));
        $tm1->getHomeCd()->setPerson(new \Dvsa\Olcs\Api\Entity\Person\Person());
        $tm1->getHomeCd()->getPerson()->setForename('Mandy')->setFamilyName('Smith');

        $publicationSection = new PublicationSection();
        $publicationSection->setId(PublicationSection::APP_GRANTED_SECTION);

        $publicationLink = new PublicationLink();
        $publicationLink->setApplication($application);
        $publicationLink->setPublicationSection($publicationSection);

        $input = [
            'licenceAddress' => 'LICENCE_ADDRESS',
            'conditionUndertaking' => [],
            'applicationTransportManagers' => [$tm1],
        ];

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $this->assertSame(
            "LICENCE_ADDRESS\nTransport Manager(s): Mandy Smith",
            $publicationLink->getText3()
        );
    }

    public function testProcessConditionsAndUndertakings(): void
    {
        $sut = new ApplicationText3();

        $organisation = new Organisation();
        $licence = new Licence($organisation, new RefData());
        $application = new Application($licence, new RefData(), false);

        $tm1 = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $tm1->setHomeCd(new \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails(new RefData()));
        $tm1->getHomeCd()->setPerson(new \Dvsa\Olcs\Api\Entity\Person\Person());
        $tm1->getHomeCd()->getPerson()->setForename('Mandy')->setFamilyName('Smith');

        $publicationSection = new PublicationSection();
        $publicationSection->setId(PublicationSection::APP_GRANTED_SECTION);

        $publicationLink = new PublicationLink();
        $publicationLink->setApplication($application);
        $publicationLink->setPublicationSection($publicationSection);

        $input = [
            'licenceAddress' => 'LICENCE_ADDRESS',
            'conditionUndertaking' => ['CU_LINE1', 'CU_LINE2'],
        ];

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $this->assertSame(
            "LICENCE_ADDRESS\nCU_LINE1\nCU_LINE2",
            $publicationLink->getText3()
        );
    }

    public function testProcessMixedFleet(): void
    {
        $sut = new ApplicationText3();

        $organisation = new Organisation();
        $licence = new Licence($organisation, new RefData());

        $application = m::mock(Application::class)->makePartial();
        $application->initCollections();
        $application->setLicence($licence);
        $application->setIsVariation(false);
        $application->shouldReceive('isVehicleTypeMixedWithLgv')
            ->withNoArgs()
            ->andReturn(true);

        $address = new Address();
        $address->setAddressLine1('ADDRESS1');
        $oc = new OperatingCentre();
        $oc->setAddress($address);
        $aoc = new ApplicationOperatingCentre($application, $oc);
        $aoc->setNoOfVehiclesRequired(3);
        $aoc->setNoOfTrailersRequired(5);
        $application->addOperatingCentres($aoc);

        $publicationSection = new PublicationSection();
        $publicationSection->setId(PublicationSection::APP_GRANTED_SECTION);

        $publicationLink = new PublicationLink();
        $publicationLink->setApplication($application);
        $publicationLink->setPublicationSection($publicationSection);

        $input = [
            'licenceAddress' => 'LICENCE_ADDRESS',
            'conditionUndertaking' => [],
            'authorisation' => 'AUTHORISATION_TEXT',
        ];

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $expectedText3 = "LICENCE_ADDRESS\nOperating Centre: ADDRESS1\n" .
            "Authorisation: 3 Heavy goods vehicle(s), 5 trailer(s)\nLICENCE_ADDRESS\nAUTHORISATION_TEXT";

        $this->assertSame(
            $expectedText3,
            $publicationLink->getText3()
        );
    }

    public function testProcessLgvOnly(): void
    {
        $sut = new ApplicationText3();

        $organisation = new Organisation();
        $licence = new Licence($organisation, new RefData());

        $application = new Application($licence, new RefData(), false);
        $publicationSection = new PublicationSection();
        $publicationSection->setId(PublicationSection::APP_GRANTED_SECTION);

        $publicationLink = new PublicationLink();
        $publicationLink->setApplication($application);
        $publicationLink->setPublicationSection($publicationSection);

        $input = [
            'licenceAddress' => 'LICENCE_ADDRESS',
            'conditionUndertaking' => [],
            'authorisation' => 'AUTHORISATION_TEXT',
        ];

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $this->assertSame(
            "LICENCE_ADDRESS\nAUTHORISATION_TEXT",
            $publicationLink->getText3()
        );
    }
}
