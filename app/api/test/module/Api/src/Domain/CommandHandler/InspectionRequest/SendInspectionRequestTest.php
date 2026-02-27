<?php

declare(strict_types=1);

/**
 * Inspection Request / SendInspectionRequest
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\InspectionRequest;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence;
use Dvsa\Olcs\Api\Domain\Repository\Workshop;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\InspectionRequest as InspectionRequestRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\InspectionRequest\SendInspectionRequest as Cmd;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\Olcs\Api\Domain\CommandHandler\InspectionRequest\SendInspectionRequest;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result as QueryResult;

/**
 * Inspection Request / SendInspectionRequest
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SendInspectionRequestTest extends AbstractCommandHandlerTestCase
{
    private $stubLicenceData = [
        'id' => 77,
        'licNo' => 'OB1234567',
        'licenceType' => [
            'id' => 'ltyp_sn'
        ],
        'totAuthVehicles' => 5,
        'totAuthTrailers' => 6,
        'safetyInsVehicles' => 7,
        'safetyInsTrailers' => 14,
        'operatingCentres' => [
            ['id' => 1],
            ['id' => 2],
        ],
        'expiryDate' => '2020-12-31T12:34:56+00:00',
        'organisation' => [
            'id' => 1,
            'name' => 'Big Old Trucks Ltd.',
            'tradingNames' => [
                ['name' => 'Big Ol\' Wagons'],
                ['name' => 'Keep On Trucking'],
            ],
            'licences' => [
                [
                    'id' => 77,
                    'licNo' => 'OB1234567',
                ],
                [
                    'id' => 78,
                    'licNo' => 'OB1234568',
                ],
                [
                    'id' => 79,
                    'licNo' => 'OB1234569',
                ],
            ],
            'organisationPersons' => [
                [
                    'id' => 3,
                    'person' => [
                        'forename' => 'Mike',
                        'familyName' => 'Smash',
                    ],
                ],
                [
                    'id' => 4,
                    'person' => [
                        'forename' => 'Dave',
                        'familyName' => 'Nice',
                    ],
                ],
            ]
        ],
        'correspondenceCd' => [
            'emailAddress' => 'bigoldtrucks@example.com',
            'address' => [
                'addressLine1' => 'Big Old House',
                'town' => 'Leeds',
                'postcode' => 'LS1 3AD',
            ],
            'phoneContacts' => [
                [
                    'phoneNumber' => '0113 2345678',
                    'phoneContactType' => [
                        'description' => 'Business',
                    ],
                ],
                [
                    'phoneNumber' => '07878 123456',
                    'phoneContactType' => [
                        'description' => 'Mobile',
                    ],
                ],
            ],
        ],
        'tmLicences' => [
            [
                'transportManager' => [
                    'homeCd' => [
                        'person' => [
                            'forename' => 'Bob',
                            'familyName' => 'Smith',
                        ],
                    ],
                ],
            ],
        ],
        'enforcementArea' => [
            'id' => 'TEST',
            'emailAddress' => 'foo@bar.com'
        ],
        'workshops' => [
            [
                'isExternal' => 'Y',
                'maintenance' => 'N',
                'safetyInspection' => 'N',
                'createdOn' => '2015-03-27T12:31:05+0000',
                'id' => 2,
                'contactDetails' => [
                    'address' => [
                        'addressLine1' => 'Inspector Gadget House',
                        'town' => 'Doncaster',
                        'postcode' => 'DN1 1QZ',
                    ],
                ],
            ],
        ],
        'translateToWelsh' => 'N',
        'niFlag' => 'N',
    ];

    private $stubApplicationData = [
        'id' => 9876,
        'licenceType' => [
            'id' => 'ltyp_si'
        ],
        'totAuthVehicles' => 7,
        'totAuthTrailers' => 8,
        'operatingCentres' => [
            [
                'action' => 'A',
                'operatingCentre' => [
                    'address' => [
                        'addressLine1' => 'Centre One',
                        'town' => 'Leeds',
                    ]
                ],
                'noOfVehiclesRequired' => 2,
                'noOfTrailersRequired' => 4,
            ],
            [
                'action' => 'U',
                'operatingCentre' => [
                    'address' => [
                        'addressLine1' => 'Centre Two',
                        'town' => 'Bradford',
                    ]
                ],
                'noOfVehiclesRequired' => 3,
                'noOfTrailersRequired' => 2,
            ],
            [
                'action' => 'D',
                'operatingCentre' => [
                    'address' => [
                        'addressLine1' => 'Centre Three',
                        'town' => 'Wakefield',
                    ]
                ],
                'noOfVehiclesRequired' => 4,
                'noOfTrailersRequired' => 5,
            ],
        ],
        'licence' => [
            'translateToWelsh' => 'N'
        ]
    ];

    public function setUp(): void
    {
        $this->sut = new SendInspectionRequest();
        $this->mockRepo('InspectionRequest', InspectionRequestRepo::class);
        $this->mockRepo('Workshop', Workshop::class);
        $this->mockRepo('Licence', Licence::class);
        $this->mockRepo('TransportManagerLicence', TransportManagerLicence::class);
        $this->mockRepo('ApplicationOperatingCentre', ApplicationOperatingCentre::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        $this->queryHandler = m::mock(QueryHandlerManager::class);

        parent::setUp();
    }

    protected function mockAuthService(): void
    {
        $mockContactDetails = m::mock(ContactDetails::class)->makePartial();
        $mockContactDetails->setEmailAddress('terry@example.com');

        /** @var Team $mockTeam */
        $mockTeam = m::mock(Team::class)->makePartial();
        $mockTeam->setId(2);

        /** @var User $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId(1);
        $mockUser->setTeam($mockTeam);
        $mockUser->setLoginId('terry');
        $mockUser->setContactDetails($mockContactDetails);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);
    }

    public function testHandleCommandAndPopulateForLicenceRequest(): void
    {
        $inspectionRequestId = 189781;
        $inspectionRequest = [
            'id' => $inspectionRequestId,
            'requestDate' => '2015-04-17T14:13:56+00:00',
            'dueDate' => '2015-04-18T14:13:56+00:00',
            'licence' => $this->stubLicenceData,
            'application' => null,
            'operatingCentre' => [
                'address' => [
                    'addressLine1' => 'DVSA',
                    'addressLine2' => 'Harehills',
                    'town' => 'Leeds',
                    'postcode' => 'LS9 6NF',
                ],
            ],
            'reportType' => [
                'description' => 'Maintenance Request',
                'refDataCategoryId' => 'insp_report_type',
                'id' => 'insp_rep_t_maint',
            ],
            'requestType' => [
                'description' => 'Change of Entity',
                'refDataCategoryId' => 'insp_request_type',
                'id' => 'insp_req_t_coe',
            ],
            'inspectorNotes' => 'Dolor lorem ipsum',
            'requestorNotes' => 'Lorem ipsum dolor',
            'operatingCentre' => [
                'id' => 74,
                'address' => [
                    'addressLine1' => 'DVSA',
                    'addressLine2' => 'Harehills',
                    'town' => 'Leeds',
                    'postcode' => 'LS9 6NF',
                ],
            ],
        ];

        $data = [
            'id' => $inspectionRequestId
        ];
        $this->mockAuthService();

        $command = Cmd::create($data);

        $this->repoMap['InspectionRequest']
            ->shouldReceive('fetchForInspectionRequest')
            ->with($inspectionRequestId)
            ->andReturn($inspectionRequest)
            ->once()
            ->shouldReceive('fetchLicenceOperatingCentreCount')
            ->with($inspectionRequestId)
            ->andReturn(6)
            ->once()
            ->getMock();

        $this->repoMap['Workshop']->shouldReceive('fetchForLicence')->with(77, Query::HYDRATE_ARRAY)->once()
            ->andReturn($inspectionRequest['licence']['workshops']);

        $this->repoMap['Licence']->shouldReceive('fetchByOrganisationId')->with(1)->once()
            ->andReturn($inspectionRequest['licence']['organisation']['licences']);

        $this->repoMap['TransportManagerLicence']->shouldReceive('fetchWithContactDetailsByLicence')->with(77)->once()
            ->andReturn([['forename' => 'Bob', 'familyName' => 'Smith']]);

        $expected = [
            'inspectionRequestId' => $inspectionRequestId,
            'currentUserName' => 'terry',
            'currentUserEmail' => 'terry@example.com',
            'inspectionRequestDateRequested' => '17/04/2015 14:13:56',
            'inspectionRequestNotes' => 'Lorem ipsum dolor',
            'inspectionRequestDueDate' => '18/04/2015 14:13:56',
            'ocAddress' => [
                'addressLine1' => 'DVSA',
                'addressLine2' => 'Harehills',
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
            ],
            'inspectionRequestType' => 'Change of Entity',
            'licenceNumber' => 'OB1234567',
            'licenceType' => 'Standard National',
            'totAuthVehicles' => 5,
            'totAuthTrailers' => 6,
            'numberOfOperatingCentres' => 6,
            'expiryDate' => '31/12/2020',
            'operatorId' => 1,
            'operatorName' => 'Big Old Trucks Ltd.',
            'operatorEmail' => 'bigoldtrucks@example.com',
            'operatorAddress' => [
                'addressLine1' => 'Big Old House',
                'town' => 'Leeds',
                'postcode' => 'LS1 3AD',
            ],
            'contactPhoneNumbers' => [
                0 => [
                    'phoneNumber' => '0113 2345678',
                    'phoneContactType' => [
                        'description' => 'Business',
                    ],
                ],
                1 => [
                    'phoneNumber' => '07878 123456',
                    'phoneContactType' => [
                        'description' => 'Mobile',
                    ],
                ]
            ],
            'tradingNames' => [
                'Big Ol\' Wagons',
                'Keep On Trucking',
            ],
            'transportManagers' => [
                'Bob Smith',
            ],
            'workshopIsExternal' => true,
            'safetyInspectionVehicles' => 7,
            'safetyInspectionTrailers' => 14,
            'inspectionProvider' => [
                'address' => [
                    'addressLine1' => 'Inspector Gadget House',
                    'town' => 'Doncaster',
                    'postcode' => 'DN1 1QZ',
                ],
            ],
            'people' => [
                0 => [
                    'forename' => 'Mike',
                    'familyName' => 'Smash',
                ],
                1 => [
                    'forename' => 'Dave',
                    'familyName' => 'Nice',
                ],
            ],
            'otherLicences' => [
                'OB1234568',
                'OB1234569',
            ],
            'applicationOperatingCentres' => [],
        ];

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            'inspection-request',
            $expected,
            'blank'
        );

        $mockQryResult = m::mock(QueryResult::class);
        $this->queryHandler->shouldReceive('handleQuery')->once()->andReturn($mockQryResult);
        $mockQryResult->shouldReceive('isEmpty')->once()->andReturn(false);
        $resultObj = m::mock(QueryResult::class);
        $mockQryResult->shouldReceive('getObject')->once()->andReturn($resultObj);
        $resultObj->shouldReceive('getParamValue')->once()->andReturn('im-an-address@email.com');

        $result = new Result();
        $data = [
            'to' => 'im-an-address@email.com',
            'locale' => 'en_GB',
            'subject' => "[ Maintenance Inspection ] REQUEST={$inspectionRequestId},STATUS="
        ];

        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'messages' => [
                'Inspection request email sent'
            ],
            'id' => []
        ];
        $this->assertEquals($expectedResult, $result->toArray());
    }

    public function testHandleCommandAndPopulateForApplicationRequest(): void
    {
        $inspectionRequestId = 189781;
        $inspectionRequest = [
            'id' => $inspectionRequestId,
            'requestDate' => '2015-04-17T14:13:56+00:00',
            'dueDate' => '2015-04-18T14:13:56+00:00',
            'licence' => $this->stubLicenceData,
            'application' => $this->stubApplicationData,
            'operatingCentre' => [
                'address' => [
                    'addressLine1' => 'DVSA',
                    'addressLine2' => 'Harehills',
                    'town' => 'Leeds',
                    'postcode' => 'LS9 6NF',
                ],
            ],
            'reportType' => [
                'description' => 'Maintenance Request',
                'refDataCategoryId' => 'insp_report_type',
                'id' => 'insp_rep_t_maint',
            ],
            'requestType' => [
                'description' => 'Change of Entity',
                'refDataCategoryId' => 'insp_request_type',
                'id' => 'insp_req_t_coe',
            ],
            'inspectorNotes' => 'Dolor lorem ipsum',
            'requestorNotes' => 'Lorem ipsum dolor',
            'operatingCentre' => [
                'id' => 74,
                'address' => [
                    'addressLine1' => 'DVSA',
                    'addressLine2' => 'Harehills',
                    'town' => 'Leeds',
                    'postcode' => 'LS9 6NF',
                ],
            ],
        ];

        $data = [
            'id' => $inspectionRequestId
        ];
        $this->mockAuthService();

        $command = Cmd::create($data);

        $this->repoMap['InspectionRequest']
            ->shouldReceive('fetchForInspectionRequest')
            ->with($inspectionRequestId)
            ->andReturn($inspectionRequest)
            ->once()
            ->shouldReceive('fetchLicenceOperatingCentreCount')
            ->with($inspectionRequestId)
            ->andReturn(21)
            ->once()
            ->getMock();

        $this->repoMap['Workshop']->shouldReceive('fetchForLicence')->with(77, Query::HYDRATE_ARRAY)->once()
            ->andReturn($inspectionRequest['licence']['workshops']);

        $this->repoMap['Licence']->shouldReceive('fetchByOrganisationId')->with(1)->once()
            ->andReturn($inspectionRequest['licence']['organisation']['licences']);

        $this->repoMap['TransportManagerLicence']->shouldReceive('fetchWithContactDetailsByLicence')->with(77)->once()
            ->andReturn([['forename' => 'Bob', 'familyName' => 'Smith']]);

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('fetchByApplication')
            ->with(9876, Query::HYDRATE_ARRAY)
            ->once()
            ->andReturn($inspectionRequest['application']['operatingCentres']);

        $expected = [
            'inspectionRequestId' => $inspectionRequestId,
            'currentUserName' => 'terry',
            'currentUserEmail' => 'terry@example.com',
            'inspectionRequestDateRequested' => '17/04/2015 14:13:56',
            'inspectionRequestNotes' => 'Lorem ipsum dolor',
            'inspectionRequestDueDate' => '18/04/2015 14:13:56',
            'ocAddress' => [
                'addressLine1' => 'DVSA',
                'addressLine2' => 'Harehills',
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
            ],
            'inspectionRequestType' => 'Change of Entity',
            'licenceNumber' => 'OB1234567',
            'licenceType' => 'Standard International',
            'totAuthVehicles' => 7,
            'totAuthTrailers' => 8,
            'numberOfOperatingCentres' => 21,
            'expiryDate' => '31/12/2020',
            'operatorId' => 1,
            'operatorName' => 'Big Old Trucks Ltd.',
            'operatorEmail' => 'bigoldtrucks@example.com',
            'operatorAddress' => [
                'addressLine1' => 'Big Old House',
                'town' => 'Leeds',
                'postcode' => 'LS1 3AD',
            ],
            'contactPhoneNumbers' => [
                0 => [
                    'phoneNumber' => '0113 2345678',
                    'phoneContactType' => [
                        'description' => 'Business',
                    ],
                ],
                1 => [
                    'phoneNumber' => '07878 123456',
                    'phoneContactType' => [
                        'description' => 'Mobile',
                    ],
                ]
            ],
            'tradingNames' => [
                'Big Ol\' Wagons',
                'Keep On Trucking',
            ],
            'transportManagers' => [
                'Bob Smith',
            ],
            'workshopIsExternal' => true,
            'safetyInspectionVehicles' => 7,
            'safetyInspectionTrailers' => 14,
            'inspectionProvider' => [
                'address' => [
                    'addressLine1' => 'Inspector Gadget House',
                    'town' => 'Doncaster',
                    'postcode' => 'DN1 1QZ',
                ],
            ],
            'people' => [
                0 => [
                    'forename' => 'Mike',
                    'familyName' => 'Smash',
                ],
                1 => [
                    'forename' => 'Dave',
                    'familyName' => 'Nice',
                ],
            ],
            'otherLicences' => [
                'OB1234568',
                'OB1234569',
            ],
            'applicationOperatingCentres' => [
                0 => [
                    'operatingCentre' => [
                        'address' => [
                            'addressLine1' => 'Centre One',
                            'town' => 'Leeds',
                        ],
                    ],
                    'noOfVehiclesRequired' => 2,
                    'noOfTrailersRequired' => 4,
                    'action' => 'Added',
                ],
                1 => [
                    'operatingCentre' => [
                        'address' => [
                            'addressLine1' => 'Centre Two',
                            'town' => 'Bradford',
                        ],
                    ],
                    'noOfVehiclesRequired' => 3,
                    'noOfTrailersRequired' => 2,
                    'action' => 'Updated',
                ],
                2 => [
                    'operatingCentre' => [
                        'address' => [
                            'addressLine1' => 'Centre Three',
                            'town' => 'Wakefield',
                        ],
                    ],
                    'noOfVehiclesRequired' => 4,
                    'noOfTrailersRequired' => 5,
                    'action' => 'Deleted',
                ],
            ],
        ];

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            'inspection-request',
            $expected,
            'blank'
        );

        $result = new Result();
        $data = [
            'to' => 'im-another-address@email.com',
            'locale' => 'en_GB',
            'subject' => "[ Maintenance Inspection ] REQUEST={$inspectionRequestId},STATUS="
        ];

        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $mockQryResult = m::mock(QueryResult::class);
        $this->queryHandler->shouldReceive('handleQuery')->once()->andReturn($mockQryResult);
        $mockQryResult->shouldReceive('isEmpty')->once()->andReturn(false);
        $resultObj = m::mock(QueryResult::class);
        $mockQryResult->shouldReceive('getObject')->once()->andReturn($resultObj);
        $resultObj->shouldReceive('getParamValue')->once()->andReturn('im-another-address@email.com');

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'messages' => [
                'Inspection request email sent'
            ],
            'id' => []
        ];
        $this->assertEquals($expectedResult, $result->toArray());
    }

    public function testHandleCommandNoInspectionRequest(): void
    {
        $inspectionRequestId = 189781;
        $inspectionRequest = null;

        $data = [
            'id' => $inspectionRequestId
        ];
        $this->mockAuthService();

        $command = Cmd::create($data);

        $this->repoMap['InspectionRequest']
            ->shouldReceive('fetchForInspectionRequest')
            ->with($inspectionRequestId)
            ->once()
            ->andReturn($inspectionRequest);

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'messages' => [
                'No inspection request'
            ],
            'id' => []
        ];
        $this->assertEquals($expectedResult, $result->toArray());
    }

    public function testThrowsExceptionNoSysParam(): void
    {
        $mockQryResult = m::mock(QueryResult::class);
        $this->queryHandler->shouldReceive('handleQuery')->once()->andReturn($mockQryResult);
        $mockQryResult->shouldReceive('isEmpty')->once()->andReturn(true);

        $inspectionRequestId = 189781;
        $inspectionRequest = [
            'id' => $inspectionRequestId,
            'requestDate' => '2015-04-17T14:13:56+00:00',
            'dueDate' => '2015-04-18T14:13:56+00:00',
            'licence' => $this->stubLicenceData,
        ];

        $data = [
            'id' => $inspectionRequestId
        ];
        $this->mockAuthService();

        $command = Cmd::create($data);

        $this->repoMap['InspectionRequest']
            ->shouldReceive('fetchForInspectionRequest')
            ->with($inspectionRequestId)
            ->once()
            ->andReturn($inspectionRequest);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);
        $this->expectExceptionMessage('The system parameter with id NEW_OP_EMAIL_GB was not found.');

        $this->sut->handleCommand($command);
    }
}
