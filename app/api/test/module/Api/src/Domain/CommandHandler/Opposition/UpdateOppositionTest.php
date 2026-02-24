<?php

declare(strict_types=1);

/**
 * Update Opposition Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Opposition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Opposition\UpdateOpposition;
use Dvsa\Olcs\Api\Domain\Repository\Opposition as OppositionRepo;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as ContactDetailsRepo;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Opposition\UpdateOpposition as Cmd;
use Dvsa\Olcs\Api\Entity\Opposition\Opposition as OppositionEntity;
use Dvsa\Olcs\Api\Entity\Opposition\Opposer as OpposerEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact as PhoneContactEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Update Opposition Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class UpdateOppositionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateOpposition();
        $this->mockRepo('Opposition', OppositionRepo::class);
        $this->mockRepo('ContactDetails', ContactDetailsRepo::class);
        $this->mockRepo('Cases', CasesRepo::class);

        parent::setUp();
    }

    private function getPayload(): array
    {
        return [
            'id' => 99,
            'version' => 3,
            'case' => 24,
            "oppositionType" => "otf_eob",
            "raisedDate" => "2015-05-04",
            "opposerType" => "obj_t_police",
            "isValid" => "opp_v_yes",
            "validNotes" => "Notes",
            "isCopied" => "Y",
            "isWillingToAttendPi" => "Y",
            "isInTime" => "Y",
            "isWithdrawn" => "N",
            "status" => "opp_ack",
            "operatingCentres" => [
                "16"
            ],
            "grounds" => [
                "ogf_env",
                "ogf_parking"
            ],
            "notes" => "Notes",
            "opposerContactDetails" => [
                "emailAddress" => "bobED@jones.com",
                "description" => "CD notes",
                "person" => [
                    "forename" => "Bob",
                    "familyName" => "Jones"
                ],
                "address" => [
                    "addressLine1" => "Unit 5ED",
                    "addressLine2" => "12 Albert Street",
                    "addressLine3" => "Westpoint",
                    "addressLine4" => "",
                    "countryCode" => "GB",
                    "postcode" => "LS9 6NA",
                    "town" => "Leeds"
                ],
                "phoneContacts" => [
                    [
                        "phoneNumber" => "5525225",
                        "phoneContactType" => PhoneContact::TYPE_PRIMARY
                    ]
                ]
            ]
        ];
    }

    private function getReferencedPayload(): array
    {
        return [
            'id' => 99,
            'version' => 3,
            "oppositionType" => "otf_eob",
            "raisedDate" => "2015-05-04",
            "opposerType" => "obj_t_police",
            "isValid" => "opp_v_yes",
            "validNotes" => "Notes",
            "isCopied" => "Y",
            "isWillingToAttendPi" => "Y",
            "isInTime" => "Y",
            "isWithdrawn" => "N",
            "status" => "opp_ack",
            "operatingCentres" => [
                "16"
            ],
            "grounds" => [
                "ogf_env",
                "ogf_parking"
            ],
            "notes" => "Notes",
            "opposerContactDetails" => [
                "emailAddress" => "bob@jones.com",
                "description" => "CD notes",
                "person" => [
                    "forename" => "Bob",
                    "familyName" => "Jones"
                ],
                "address" => [
                    "addressLine1" => "Unit 5",
                    "addressLine2" => "12 Albert Street",
                    "addressLine3" => "Westpoint",
                    "addressLine4" => "",
                    "countryCode" => new CountryEntity(),
                    "postcode" => "LS9 6NA",
                    "town" => "Leeds"
                ],
                "phoneContacts" => [
                    [
                        "phoneNumber" => "5525225",
                        "phoneContactType" => new RefDataEntity()
                    ]
                ]
            ]
        ];
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'ct_obj',
            'otf_eob',
            'obj_t_police',
            'opp_v_yes',
            'opp_ack',
            'ogf_env',
            'ct_obj'
        ];

        $this->references = [
            CasesEntity::class => [
                24 => m::mock(CasesEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $payload = $this->getPayload();
        $referencedPayload = $this->getReferencedPayload();

        $command = Cmd::create(
            $payload
        );

        $this->repoMap['ContactDetails']
            ->shouldReceive('populateRefDataReference')
            ->with($payload['opposerContactDetails'])
            ->once()
            ->andReturn(
                $referencedPayload['opposerContactDetails']
            );

        $mockLicence = m::mock(LicenceEntity::class)->makePartial();

        $mockCase = m::mock(CasesEntity::class)->makePartial();
        $mockCase->setLicence($mockLicence);

        /** @var PersonEntity $person */
        $person = m::mock(PersonEntity::class)->makePartial();
        $person->setId(44);

        /** @var AddressEntity $address */
        $address = m::mock(AddressEntity::class)->makePartial();
        $address->setId(55);

        /** @var PhoneContactEntity $phoneContact */
        $phoneContact = m::mock(PhoneContactEntity::class)->makePartial();
        $phoneContact->setId(66);

        /** @var RefDataEntity $contactType */
        $contactType = m::mock(RefDataEntity::class)->makePartial();
        $contactType->setId('ct_obj');

        /** @var ContactDetailsEntity $contactDetails */
        $contactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $contactDetails->setId(33);
        $contactDetails->setContactType($contactType);
        $contactDetails->setDescription($payload['opposerContactDetails']['description']);
        $contactDetails->setPerson($person);
        $contactDetails->setAddress($address);
        $contactDetails->setPhoneContacts(new ArrayCollection([66 => $phoneContact]));

        /** @var OpposerEntity $opposer */
        $opposer = m::mock(OpposerEntity::class)->makePartial();
        $opposer->setId(66);
        $opposer->setContactDetails($contactDetails);

        /** @var OppositionEntity $opposition */
        $opposition = m::mock(OppositionEntity::class)->makePartial();
        $opposition->setId($command->getId());
        $opposition->setOpposer($opposer);

        $this->repoMap['Opposition']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($opposition)
            ->once()
            ->shouldReceive('generateRefdataArrayCollection')
            ->shouldReceive('save')
            ->with(m::type(OppositionEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Opposition updated', $result->getMessages());
    }
}
