<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Schedule41;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Text3Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Text3Test extends MockeryTestCase
{
    /**
     * @var \Dvsa\Olcs\Api\Service\Publication\Process\Variation\Text2
     */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Service\Publication\Process\Schedule41\Text3();

        parent::setUp();
    }

    /**
     * @param string $organisationType
     *
     * @return PublicationLink
     */
    private function getPublicationLink(mixed $organisationType): mixed
    {
        $publicationLink = new PublicationLink();

        $organisation = new Organisation();
        $organisation->setName('ORG_NAME');
        $organisation->setType(new RefData($organisationType));

        $licence = new Licence($organisation, new RefData());
        $licence->setLicNo('LIC12345');
        $publicationLink->setLicence($licence);

        $trafficArea = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $licence->setTrafficArea($trafficArea);

        $application = new \Dvsa\Olcs\Api\Entity\Application\Application($licence, new RefData(), false);
        $application->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL));
        $publicationLink->setApplication($application);

        return $publicationLink;
    }

    public function testText3(): void
    {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);
        $publicationLink->getApplication()->getLicence()->getTrafficArea()->setIsNi(false);

        $donorOrganisation = new Organisation();
        $donorOrganisation->setName('DONOR_ORG');

        $donorLicence = new Licence($donorOrganisation, new RefData());
        $donorLicence->setLicNo('D12345');
        $donorLicence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $ta = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $donorLicence->setTrafficArea($ta);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($publicationLink->getApplication(), $donorLicence);
        $publicationLink->getApplication()->addS4s($s4);

        $oc1 = new \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre();
        $oc1->setAddress((new \Dvsa\Olcs\Api\Entity\ContactDetails\Address())->setAddressLine1('OC1 ADD1'));
        $oc2 = new \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre();
        $oc2->setAddress((new \Dvsa\Olcs\Api\Entity\ContactDetails\Address())->setAddressLine1('OC2 ADD1'));
        $oc3 = new \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre();
        $oc3->setAddress((new \Dvsa\Olcs\Api\Entity\ContactDetails\Address())->setAddressLine1('OC3 ADD1'));

        $aoc1 = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre(
            $publicationLink->getApplication(),
            $oc1
        );
        $aoc1->setS4($s4)
            ->setNoOfVehiclesRequired(4)
            ->setNoOfTrailersRequired(8);
        $aoc2 = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre(
            $publicationLink->getApplication(),
            $oc2
        );
        $aoc2->setNoOfVehiclesRequired(10)
            ->setNoOfTrailersRequired(0);
        $aoc3 = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre(
            $publicationLink->getApplication(),
            $oc3
        );
        $aoc3->setS4($s4)
            ->setNoOfVehiclesRequired(10)
            ->setNoOfTrailersRequired(0);
        $publicationLink->getApplication()->addOperatingCentres($aoc1);
        $publicationLink->getApplication()->addOperatingCentres($aoc2);
        $publicationLink->getApplication()->addOperatingCentres($aoc3);

        $context = new ImmutableArrayObject(
            ['licenceAddress' => 'LICENCE_ADDRESS']
        );

        $this->sut->process($publicationLink, $context);

        $expectedText = "LICENCE_ADDRESS
Operating Centre: OC1 ADD1
Authorisation: 4 vehicle(s), 8 trailer(s)
Operating Centre: OC3 ADD1
Authorisation: 10 vehicle(s)
The Traffic Commissioner has given a direction under paragraph 2 of Schedule 4 that the above operating centre(s) " .
            "shall be transferred from licence D12345 held by DONOR_ORG
The operating centre(s) being removed from D12345 as part of this application.";

        $this->assertSame($expectedText, $publicationLink->getText3());
    }

    public function testText3S4SurrenderAndNi(): void
    {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);
        $publicationLink->getApplication()->getLicence()->getTrafficArea()->setIsNi(true);

        $donorOrganisation = new Organisation();
        $donorOrganisation->setName('DONOR_ORG');

        $donorLicence = new Licence($donorOrganisation, new RefData());
        $donorLicence->setLicNo('D12345');
        $donorLicence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $ta = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $ta->setIsNi(true);
        $donorLicence->setTrafficArea($ta);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($publicationLink->getApplication(), $donorLicence);
        $s4->setSurrenderLicence('Y');
        $publicationLink->getApplication()->addS4s($s4);

        $oc1 = new \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre();
        $oc1->setAddress((new \Dvsa\Olcs\Api\Entity\ContactDetails\Address())->setAddressLine1('OC1 ADD1'));
        $oc2 = new \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre();
        $oc2->setAddress((new \Dvsa\Olcs\Api\Entity\ContactDetails\Address())->setAddressLine1('OC2 ADD1'));
        $oc3 = new \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre();
        $oc3->setAddress((new \Dvsa\Olcs\Api\Entity\ContactDetails\Address())->setAddressLine1('OC3 ADD1'));

        $aoc1 = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre(
            $publicationLink->getApplication(),
            $oc1
        );
        $aoc1->setS4($s4)
            ->setNoOfVehiclesRequired(4)
            ->setNoOfTrailersRequired(8);
        $aoc2 = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre(
            $publicationLink->getApplication(),
            $oc2
        );
        $aoc2->setNoOfVehiclesRequired(10)
            ->setNoOfTrailersRequired(0);
        $aoc3 = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre(
            $publicationLink->getApplication(),
            $oc3
        );
        $aoc3->setS4($s4)
            ->setNoOfVehiclesRequired(10)
            ->setNoOfTrailersRequired(0);
        $publicationLink->getApplication()->addOperatingCentres($aoc1);
        $publicationLink->getApplication()->addOperatingCentres($aoc2);
        $publicationLink->getApplication()->addOperatingCentres($aoc3);

        $context = new ImmutableArrayObject(
            ['licenceAddress' => 'LICENCE_ADDRESS']
        );

        $this->sut->process($publicationLink, $context);

        $expectedText = "LICENCE_ADDRESS
Operating Centre: OC1 ADD1
Authorisation: 4 vehicle(s), 8 trailer(s)
Operating Centre: OC3 ADD1
Authorisation: 10 vehicle(s)
The Department has given a direction under paragraph 2 of Schedule 1(NI) that the above operating centre(s)" .
            " shall be transferred from licence D12345 held by DONOR_ORG
D12345 has been surrendered as part of this application.";

        $this->assertSame($expectedText, $publicationLink->getText3());
    }

    public function testText3Tm(): void
    {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);
        $publicationLink->getApplication()->getLicence()->getTrafficArea()->setIsNi(true);

        $donorOrganisation = new Organisation();
        $donorOrganisation->setName('DONOR_ORG');

        $donorLicence = new Licence($donorOrganisation, new RefData());
        $donorLicence->setLicNo('D12345');
        $donorLicence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $ta = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $ta->setIsNi(true);
        $donorLicence->setTrafficArea($ta);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($publicationLink->getApplication(), $donorLicence);
        $s4->setSurrenderLicence('Y');
        $publicationLink->getApplication()->addS4s($s4);

        $tm1 = m::mock(\Dvsa\Olcs\Api\Entity\Tm\TransportManager::class);
        $tm1->shouldReceive('getHomeCd->getPerson->getFullName')->with()->once()->andReturn('Dave Jones');
        $tm2 = m::mock(\Dvsa\Olcs\Api\Entity\Tm\TransportManager::class);
        $tm2->shouldReceive('getHomeCd->getPerson->getFullName')->with()->once()->andReturn('Shirley Basey');

        $context = new ImmutableArrayObject(
            [
                'licenceAddress' => 'LICENCE_ADDRESS',
                'applicationTransportManagers' => [$tm1, $tm2],
            ]
        );

        $this->sut->process($publicationLink, $context);

        $expectedText = "LICENCE_ADDRESS
Transport Manager(s): Dave Jones, Shirley Basey
The Department has given a direction under paragraph 2 of Schedule 1(NI) that the above operating centre(s)" .
            " shall be transferred from licence D12345 held by DONOR_ORG
D12345 has been surrendered as part of this application.";

        $this->assertSame($expectedText, $publicationLink->getText3());
    }

    public function testText3LicenceUpdgrade(): void
    {
        $refDataSn = new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $refDataSn->setDescription('SN');
        $refDataSi = new RefData(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL);
        $refDataSi->setDescription('SI');

        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);
        $publicationLink->getApplication()->getLicence()->getTrafficArea()->setIsNi(true);
        $publicationLink->getApplication()->setIsVariation(true);
        $publicationLink->getApplication()->setLicenceType($refDataSi);
        $publicationLink->getApplication()->getLicence()->setLicenceType($refDataSn);

        $donorOrganisation = new Organisation();
        $donorOrganisation->setName('DONOR_ORG');

        $donorLicence = new Licence($donorOrganisation, new RefData());
        $donorLicence->setLicNo('D12345');
        $donorLicence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $ta = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $ta->setIsNi(true);
        $donorLicence->setTrafficArea($ta);

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($publicationLink->getApplication(), $donorLicence);
        $s4->setSurrenderLicence('Y');
        $publicationLink->getApplication()->addS4s($s4);

        $context = new ImmutableArrayObject(
            [
                'licenceAddress' => 'LICENCE_ADDRESS',
            ]
        );

        $this->sut->process($publicationLink, $context);

        $expectedText = "LICENCE_ADDRESS
The Department has given a direction under paragraph 2 of Schedule 1(NI) that the above operating centre(s)" .
            " shall be transferred from licence D12345 held by DONOR_ORG
D12345 has been surrendered as part of this application.
Upgrade of Licence from SN to SI";

        $this->assertSame($expectedText, $publicationLink->getText3());
    }
}
