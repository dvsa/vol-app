<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Variation;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Text3Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Text3Test extends MockeryTestCase
{
    /**
     * @var \Dvsa\Olcs\Api\Service\Publication\Process\Variation\Text3
     */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Service\Publication\Process\Variation\Text3();

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
        $this->addOrganisationPerson($organisation, 'Randy', 'Couture');
        $this->addOrganisationPerson($organisation, 'Rachel', 'Jones');
        $this->addOrganisationPerson($organisation, 'Fred', 'Smith');

        $licence = new Licence($organisation, new RefData());
        $licence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $publicationLink->setLicence($licence);

        $application = new \Dvsa\Olcs\Api\Entity\Application\Application($licence, new RefData(), true);
        $application->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $publicationLink->setApplication($application);

        return $publicationLink;
    }

    /**
     * @param string $foreName
     * @param string $familyName
     */
    private function addOrganisationPerson(Organisation $organisation, mixed $foreName, mixed $familyName): void
    {
        $person = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person->setForename($foreName)->setFamilyName($familyName);

        $organisationPerson = new \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson();
        $organisationPerson->setPerson($person);
        $organisation->addOrganisationPersons($organisationPerson);
    }

    public function testCorrespondanceAddress(): void
    {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);
        $context = new ImmutableArrayObject(
            [
                'licenceAddress' => 'LICENCE_ADDRESS'
            ]
        );

        $this->sut->process($publicationLink, $context);

        $expectedText3 = "LICENCE_ADDRESS";

        $this->assertSame($expectedText3, $publicationLink->getText3());
    }

    public function testOperatingCentres(): void
    {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);
        $context = new ImmutableArrayObject(
            [
                'operatingCentres' => ['OC_LINE1', 'OC_LINE2']
            ]
        );

        $this->sut->process($publicationLink, $context);

        $expectedText3 = "OC_LINE1\nOC_LINE2";

        $this->assertSame($expectedText3, $publicationLink->getText3());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpAuthorisationsWhereNoOperatingCentres')]
    public function testAuthorisationsWhereNoOperatingCentres(mixed $contextArray): void
    {
        $authorisationText = "AUTHORISATION_TEXT1\nAUTHORISATION_TEXT2";

        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);
        $context = new ImmutableArrayObject($contextArray);

        $this->sut->process($publicationLink, $context);

        $this->assertSame(
            $authorisationText,
            $publicationLink->getText3()
        );
    }

    public static function dpAuthorisationsWhereNoOperatingCentres(): array
    {
        return [
            [
                [
                    'authorisation' => [
                        'AUTHORISATION_TEXT1',
                        'AUTHORISATION_TEXT2',
                    ],
                ],
            ],
            [
                [
                    'authorisation' => [
                        'AUTHORISATION_TEXT1',
                        'AUTHORISATION_TEXT2',
                    ],
                    'operatingCentres' => null,
                ],
            ],
            [
                [
                    'authorisation' => [
                        'AUTHORISATION_TEXT1',
                        'AUTHORISATION_TEXT2',
                    ],
                    'operatingCentres' => [],
                ],
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpAuthorisationsAndOperatingCentresWhereOneOrMoreOperatingCentres')]
    public function testAuthorisationsAndOperatingCentresWhereOneOrMoreOperatingCentres(
        mixed $operatingCentres,
        mixed $expectedText
    ): void {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);
        $context = new ImmutableArrayObject(
            [
                'authorisation' => ['AUTHORISATION_TEXT1', 'AUTHORISATION_TEXT2'],
                'operatingCentres' => $operatingCentres,
                'licenceAddress' => 'LICENCE_ADDRESS',
            ]
        );

        $this->sut->process($publicationLink, $context);

        $this->assertEquals(
            $expectedText,
            $publicationLink->getText3()
        );
    }

    public static function dpAuthorisationsAndOperatingCentresWhereOneOrMoreOperatingCentres(): array
    {
        return [
            [
                ['OC_LINE1'],
                "LICENCE_ADDRESS\nOC_LINE1\nAUTHORISATION_TEXT1\nAUTHORISATION_TEXT2",
            ],
            [
                ['OC_LINE1', 'OC_LINE2'],
                "LICENCE_ADDRESS\nOC_LINE1\nOC_LINE2\nAUTHORISATION_TEXT1\nAUTHORISATION_TEXT2",
            ],
        ];
    }

    public function testTransportManagers(): void
    {
        $person1 = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person1->setForename('John')->setFamilyName('Jones');
        $cd1 = new \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails(new RefData());
        $cd1->setPerson($person1);
        $tm1 = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $tm1->setHomeCd($cd1);

        $person2 = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person2->setForename('Fred')->setFamilyName('Smith');
        $cd2 = new \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails(new RefData());
        $cd2->setPerson($person2);
        $tm2 = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $tm2->setHomeCd($cd2);

        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);
        $context = new ImmutableArrayObject(
            [
                'applicationTransportManagers' => [$tm1, $tm2]
            ]
        );

        $this->sut->process($publicationLink, $context);

        $expectedText3 = "Transport Manager(s): John Jones, Fred Smith";

        $this->assertSame($expectedText3, $publicationLink->getText3());
    }

    public function testConditionUndertaking(): void
    {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);
        $context = new ImmutableArrayObject(
            [
                'conditionUndertaking' => ['LINE1', 'LINE2']
            ]
        );

        $this->sut->process($publicationLink, $context);

        $expectedText3 = "LINE1\nLINE2";

        $this->assertSame($expectedText3, $publicationLink->getText3());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestUpgrade')]
    public function testUpgrade(mixed $licenceTypeId, mixed $applicationLicenceTypeId, mixed $isUpgrade): void
    {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_LLP);
        $publicationLink->getApplication()->getLicenceType()->setId($applicationLicenceTypeId);
        $publicationLink->getApplication()->getLicenceType()->setDescription('APP_DESC');
        $publicationLink->getLicence()->getLicenceType()->setId($licenceTypeId);
        $publicationLink->getLicence()->getLicenceType()->setDescription('LIC_DESC');
        $context = new ImmutableArrayObject();

        $this->sut->process($publicationLink, $context);

        if ($isUpgrade) {
            $this->assertSame('Upgrade of Licence from LIC_DESC to APP_DESC', $publicationLink->getText3());
        } else {
            $this->assertEmpty($publicationLink->getText3());
        }
    }

    public static function dataProviderTestUpgrade(): array
    {
        return [
            [Licence::LICENCE_TYPE_RESTRICTED, Licence::LICENCE_TYPE_STANDARD_NATIONAL, true],
            [Licence::LICENCE_TYPE_RESTRICTED, Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL, true],
            [Licence::LICENCE_TYPE_STANDARD_NATIONAL, Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL, true],
            [Licence::LICENCE_TYPE_RESTRICTED, Licence::LICENCE_TYPE_RESTRICTED, false],
            [Licence::LICENCE_TYPE_RESTRICTED, Licence::LICENCE_TYPE_SPECIAL_RESTRICTED, false],
            [Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL, Licence::LICENCE_TYPE_STANDARD_NATIONAL, false],
        ];
    }
}
