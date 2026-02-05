<?php

declare(strict_types=1);

/**
 * Create Opposition Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Opposition;

use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Opposition\CreateOpposition;
use Dvsa\Olcs\Api\Domain\Repository\Opposition as OppositionRepo;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as ContactDetailsRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Opposition\CreateOpposition as Cmd;
use Dvsa\Olcs\Api\Entity\Opposition\Opposition as OppositionEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;

/**
 * Create Opposition Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class CreateOppositionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateOpposition();
        $this->mockRepo('Opposition', OppositionRepo::class);
        $this->mockRepo('ContactDetails', ContactDetailsRepo::class);

        parent::setUp();
    }

    private function getPayload(): array
    {
        return [
            'case' => 24,
            "oppositionType" => "otf_eob",
            "contactDetailsDescription" => "CD notes",
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
            PhoneContact::TYPE_PRIMARY,
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

        /** @var OppositionEntity $opp */
        $opp = null;

        $this->repoMap['Opposition']
            ->shouldReceive('generateRefdataArrayCollection')
            ->shouldReceive('save')
            ->with(m::type(OppositionEntity::class))
            ->andReturnUsing(
                function (OppositionEntity $opposition) use (&$opp) {
                    $opp = $opposition;
                    $opposition->setId(99);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Opposition created', $result->getMessages());
    }
}
