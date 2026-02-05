<?php

declare(strict_types=1);

/**
 * Update IrfoDetails Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\UpdateIrfoDetails;
use Dvsa\Olcs\Api\Domain\Command\Organisation\UpdateTradingNames;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\Organisation;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPartner;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPartner as IrfoPartnerEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoDetails as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Update IrfoDetails Test
 */
class UpdateIrfoDetailsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateIrfoDetails();
        $this->mockRepo('Organisation', Organisation::class);
        $this->mockRepo('IrfoPartner', IrfoPartner::class);
        $this->mockRepo('ContactDetails', ContactDetails::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ContactDetailsEntity::CONTACT_TYPE_IRFO_OPERATOR
        ];

        $this->references = [
            Country::class => [
                'GB' => m::mock(Country::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithTradingNames(): void
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'irfoNationality' => 'GB',
            'tradingNames' => [
                ['id' => 101, 'name' => 'updated trading name', 'version' => 1],
                ['name' => 'new trading name'],
            ],
            'irfoPartners' => null,
        ];

        $command = Cmd::create($data);

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(111);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        /** @var OrganisationEntity $savedOrganisation */
        $savedOrganisation = null;

        $this->repoMap['Organisation']->shouldReceive('save')
            ->once()
            ->with(m::type(OrganisationEntity::class))
            ->andReturnUsing(
                function (OrganisationEntity $organisation) use (&$savedOrganisation) {
                    $savedOrganisation = $organisation;
                }
            );

        // Update trading names
        $expectedTradingNamesData = [
            'licence' => null,
            'organisation' => 111,
            'tradingNames' => [
                'updated trading name',
                'new trading name'
            ]
        ];
        $result1 = new Result();
        $result1->setFlag('hasChanged', true);
        $result1->addMessage('Trading names updated');
        $this->expectedSideEffect(UpdateTradingNames::class, $expectedTradingNamesData, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'organisation' => 111,
            ],
            'messages' => [
                'IRFO Details updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $this->references[Country::class][$data['irfoNationality']],
            $savedOrganisation->getIrfoNationality()
        );
    }

    public function testHandleCommandWithIrfoPartners(): void
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'irfoPartners' => [
                ['id' => 101, 'name' => 'updated partner', 'version' => 1],
                ['name' => 'new partner'],
                ['name' => ''],
            ],
            'tradingNames' => null,
        ];

        $command = Cmd::create($data);

        /** @var IrfoPartnerEntity $irfoPartner */
        $irfoPartner1 = m::mock(IrfoPartnerEntity::class)->makePartial();
        $irfoPartner1->setId(101);
        $irfoPartner1->setName('existing partner');

        $irfoPartner2 = m::mock(IrfoPartnerEntity::class)->makePartial();
        $irfoPartner2->setId(102);
        $irfoPartner2->setName('deleted partner');

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(111);
        $organisation->setIrfoPartners([$irfoPartner1, $irfoPartner2]);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        $this->repoMap['IrfoPartner']->shouldReceive('fetchById')
            ->once()
            ->with(101, Query::HYDRATE_OBJECT, 1)
            ->andReturn($irfoPartner1);

        /** @var OrganisationEntity $savedOrganisation */
        $savedOrganisation = null;

        $this->repoMap['Organisation']->shouldReceive('save')
            ->once()
            ->with(m::type(OrganisationEntity::class))
            ->andReturnUsing(
                function (OrganisationEntity $organisation) use (&$savedOrganisation) {
                    $savedOrganisation = $organisation;
                }
            );

        $savedIrfoPartners = null;
        $deletedIrfoPartners = null;

        $this->repoMap['IrfoPartner']->shouldReceive('save')
            ->times(2)
            ->with(m::type(IrfoPartnerEntity::class))
            ->andReturnUsing(
                function (IrfoPartnerEntity $irfoPartner) use (&$savedIrfoPartners) {
                    $savedIrfoPartners[] = $irfoPartner;
                }
            );

        $this->repoMap['IrfoPartner']->shouldReceive('delete')
            ->once()
            ->with(m::type(IrfoPartnerEntity::class))
            ->andReturnUsing(
                function (IrfoPartnerEntity $irfoPartner) use (&$deletedIrfoPartners) {
                    $deletedIrfoPartners[] = $irfoPartner;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'organisation' => 111,
            ],
            'messages' => [
                'IRFO Details updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('updated partner', $savedIrfoPartners[0]->getName());
        $this->assertEquals('new partner', $savedIrfoPartners[1]->getName());
        $this->assertEquals('deleted partner', $deletedIrfoPartners[0]->getName());
    }

    public function testHandleCommandWithNewIrfoContactDetails(): void
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'irfoContactDetails' => [
                'emailAddress' => 'test1@test.me',
                'address' => [
                    'addressLine1' => 'a12',
                    'addressLine2' => 'a23',
                    'addressLine3' => 'a34',
                    'addressLine4' => 'a45',
                    'town' => 'town',
                    'postcode' => 'LS1 2AB',
                    'countryCode' => m::mock(Country::class),
                ],
                'phoneContacts' => [
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '111',
                    ],
                    [
                        'id' => 999,
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '222',
                    ]
                ],
            ],
            'irfoPartners' => null,
            'tradingNames' => null,
        ];

        $command = Cmd::create($data);

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(111);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        $this->repoMap['ContactDetails']->shouldReceive('populateRefDataReference')
            ->once()
            ->with($data['irfoContactDetails'])
            ->andReturn($data['irfoContactDetails']);

        /** @var OrganisationEntity $savedOrganisation */
        $savedOrganisation = null;

        $this->repoMap['Organisation']->shouldReceive('save')
            ->once()
            ->with(m::type(OrganisationEntity::class))
            ->andReturnUsing(
                function (OrganisationEntity $organisation) use (&$savedOrganisation) {
                    $savedOrganisation = $organisation;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'organisation' => 111,
            ],
            'messages' => [
                'IRFO Details updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(
            \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails::class,
            $savedOrganisation->getIrfoContactDetails()
        );
        $this->assertEquals(
            $data['irfoContactDetails']['emailAddress'],
            $savedOrganisation->getIrfoContactDetails()->getEmailAddress()
        );

        $this->assertInstanceOf(
            \Dvsa\Olcs\Api\Entity\ContactDetails\Address::class,
            $savedOrganisation->getIrfoContactDetails()->getAddress()
        );
        $this->assertEquals(
            $data['irfoContactDetails']['address']['addressLine1'],
            $savedOrganisation->getIrfoContactDetails()->getAddress()->getAddressLine1()
        );
        $this->assertEquals(
            $data['irfoContactDetails']['address']['addressLine2'],
            $savedOrganisation->getIrfoContactDetails()->getAddress()->getAddressLine2()
        );
        $this->assertEquals(
            $data['irfoContactDetails']['address']['addressLine3'],
            $savedOrganisation->getIrfoContactDetails()->getAddress()->getAddressLine3()
        );
        $this->assertEquals(
            $data['irfoContactDetails']['address']['addressLine4'],
            $savedOrganisation->getIrfoContactDetails()->getAddress()->getAddressLine4()
        );
        $this->assertEquals(
            $data['irfoContactDetails']['address']['town'],
            $savedOrganisation->getIrfoContactDetails()->getAddress()->getTown()
        );
        $this->assertEquals(
            $data['irfoContactDetails']['address']['postcode'],
            $savedOrganisation->getIrfoContactDetails()->getAddress()->getPostcode()
        );

        $this->assertEquals(
            2,
            count($savedOrganisation->getIrfoContactDetails()->getPhoneContacts())
        );
    }

    public function testHandleCommandWithUpdatedIrfoContactDetails(): void
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'irfoContactDetails' => [
                'emailAddress' => 'test1@test.me',
                'address' => [
                    'addressLine1' => 'a12',
                    'addressLine2' => 'a23',
                    'addressLine3' => 'a34',
                    'addressLine4' => 'a45',
                    'town' => 'town',
                    'postcode' => 'LS1 2AB',
                    'countryCode' => m::mock(Country::class),
                ],
                'phoneContacts' => [
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '111',
                    ],
                    [
                        'id' => 999,
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '222',
                    ]
                ],
            ],
            'irfoPartners' => null,
            'tradingNames' => null,
        ];

        $command = Cmd::create($data);

        /** @var ContactDetailsEntity $irfoContactDetails */
        $irfoContactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $irfoContactDetails->shouldReceive('update')
            ->once()
            ->with($data['irfoContactDetails'])
            ->andReturnSelf();

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(111);
        $organisation->setIrfoContactDetails($irfoContactDetails);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($organisation);

        $this->repoMap['ContactDetails']->shouldReceive('populateRefDataReference')
            ->once()
            ->with($data['irfoContactDetails'])
            ->andReturn($data['irfoContactDetails']);

        /** @var OrganisationEntity $savedOrganisation */
        $savedOrganisation = null;

        $this->repoMap['Organisation']->shouldReceive('save')
            ->once()
            ->with(m::type(OrganisationEntity::class))
            ->andReturnUsing(
                function (OrganisationEntity $organisation) use (&$savedOrganisation) {
                    $savedOrganisation = $organisation;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'organisation' => 111,
            ],
            'messages' => [
                'IRFO Details updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $irfoContactDetails,
            $savedOrganisation->getIrfoContactDetails()
        );
    }
}
