<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Schedule41;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Text1Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Text1Test extends MockeryTestCase
{
    /**
     * @var \Dvsa\Olcs\Api\Service\Publication\Process\Variation\Text2
     */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Service\Publication\Process\Schedule41\Text1();

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

        $application = new \Dvsa\Olcs\Api\Entity\Application\Application($licence, new RefData(), false);
        $application->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL));
        $publicationLink->setApplication($application);

        return $publicationLink;
    }

    public function testText1(): void
    {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);

        $donorOrganisation = new Organisation();
        $donorOrganisation->setName('DONOR_ORG');
        $donorOrganisation->addTradingNames(
            new \Dvsa\Olcs\Api\Entity\Organisation\TradingName('TRAD_NAME', $donorOrganisation)
        );
        $donorOrganisation->setType(new RefData(Organisation::ORG_TYPE_REGISTERED_COMPANY));

        $organisationPerson = new \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson();
        $organisationPerson->setPerson(
            (new \Dvsa\Olcs\Api\Entity\Person\Person())->setForename('Derek')->setFamilyName('Dooley')
        );
        $donorOrganisation->addOrganisationPersons($organisationPerson);

        $donorLicence = new Licence($donorOrganisation, new RefData());
        $donorLicence->setLicNo('D12345');
        $donorLicence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));

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
            ['applicationPeople' =>
                [(new \Dvsa\Olcs\Api\Entity\Person\Person())->setForename('Andy')->setFamilyName('Adams')]
            ]
        );

        $this->sut->process($publicationLink, $context);

        $expectedText = "Operating Centre(s):
OC1 ADD1
4 vehicle(s), 8 trailer(s)
OC3 ADD1
10 vehicle(s)
Transferred from D12345 SN (DONOR_ORG T/A TRAD_NAME Director(s): Derek Dooley) to" .
            " LIC12345 SI (ORG_NAME Partner(s): Andy Adams).
The operating centre(s) being removed from D12345 as part of this application.";

        $this->assertSame($expectedText, $publicationLink->getText1());
    }

    public function testText1SurrenderS4(): void
    {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);

        $donorOrganisation = new Organisation();
        $donorOrganisation->setName('DONOR_ORG');
        $donorOrganisation->addTradingNames(
            new \Dvsa\Olcs\Api\Entity\Organisation\TradingName('TRAD_NAME', $donorOrganisation)
        );
        $donorOrganisation->setType(new RefData(Organisation::ORG_TYPE_REGISTERED_COMPANY));

        $organisationPerson = new \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson();
        $organisationPerson->setPerson(
            (new \Dvsa\Olcs\Api\Entity\Person\Person())->setForename('Derek')->setFamilyName('Dooley')
        );
        $donorOrganisation->addOrganisationPersons($organisationPerson);

        $donorLicence = new Licence($donorOrganisation, new RefData());
        $donorLicence->setLicNo('D12345');
        $donorLicence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));

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
            ['applicationPeople' =>
                [(new \Dvsa\Olcs\Api\Entity\Person\Person())->setForename('Andy')->setFamilyName('Adams')]
            ]
        );

        $this->sut->process($publicationLink, $context);

        $expectedText = "Operating Centre(s):
OC1 ADD1
4 vehicle(s), 8 trailer(s)
OC3 ADD1
10 vehicle(s)
Transferred from D12345 SN (DONOR_ORG T/A TRAD_NAME Director(s): Derek Dooley) to LIC12345 SI" .
            " (ORG_NAME Partner(s): Andy Adams).
D12345 has been surrendered as part of this application.";

        $this->assertSame($expectedText, $publicationLink->getText1());
    }

    public function testText1Upgrade(): void
    {
        $refDataSn = new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $refDataSn->setDescription('SN');
        $refDataSi = new RefData(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL);
        $refDataSi->setDescription('SI');

        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);
        $publicationLink->getApplication()->setIsVariation(true);
        $publicationLink->getApplication()->setLicenceType($refDataSi);
        $publicationLink->getApplication()->getLicence()->setLicenceType($refDataSn);

        $donorOrganisation = new Organisation();
        $donorOrganisation->setName('DONOR_ORG');
        $donorOrganisation->setType(new RefData(Organisation::ORG_TYPE_REGISTERED_COMPANY));

        $donorLicence = new Licence($donorOrganisation, new RefData());
        $donorLicence->setLicNo('D12345');
        $donorLicence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));

        $s4 = new \Dvsa\Olcs\Api\Entity\Application\S4($publicationLink->getApplication(), $donorLicence);
        $s4->setSurrenderLicence('Y');
        $publicationLink->getApplication()->addS4s($s4);

        $context = new ImmutableArrayObject(
            [
                'applicationPeople' => []
            ]
        );

        $this->sut->process($publicationLink, $context);

        $expectedText = "Operating Centre(s):
Transferred from D12345 SN (DONOR_ORG) to LIC12345 SI" .
            " (ORG_NAME).
D12345 has been surrendered as part of this application.
Upgrade of Licence from SN to SI";

        $this->assertSame($expectedText, $publicationLink->getText1());
    }
}
