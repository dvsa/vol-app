<?php

namespace OlcsTest\View\Model\Email;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\View\Model\Email\InspectionRequest as Sut;

/**
 * Class InspectionRequestTest
 * @package OlcsTest\View\Helper
 */
class InspectionRequestTest extends MockeryTestCase
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
    ];

    private $stubApplicationData = [
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
        ],
    ];

    private $stubUserData = [
        'loginId' => 'terry',
        'emailAddress' => 'terry@example.com',
    ];

    private $stubPeopleData = [
        'Count' => 2,
        'Results' => [
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
        ],
    ];

    private $stubWorkshopsData = [
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
    ];

    public function testPopulateForLicenceRequest()
    {
        $inspectionRequest = [
            'id' => 189781,
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
            'operatingCentre' =>[
                'id' => 74,
                'address' => [
                    'addressLine1' => 'DVSA',
                    'addressLine2' => 'Harehills',
                    'town' => 'Leeds',
                    'postcode' => 'LS9 6NF',
                ],
            ],
        ];

        // mocks
        $translator = m::mock();

        // expectations
        $translator
            ->shouldReceive('translate')
            ->with('ltyp_sn')
            ->andReturn('Standard National');

        $sut = new Sut();
        // assertions
        $this->assertSame(
            $sut,
            $sut->populate(
                $inspectionRequest,
                $this->stubUserData,
                $this->stubPeopleData,
                $this->stubWorkshopsData,
                $translator
            )
        );

        $expected = [
            'inspectionRequestId' => 189781,
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
            'numberOfOperatingCentres' => 2,
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

        $vars = (array) $sut->getVariables();

        $this->assertEquals($expected, $vars);
    }

    public function testPopulateForApplicationRequest()
    {
        $inspectionRequest = [
            'id' => 189781,
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
            'operatingCentre' =>[
                'id' => 74,
                'address' => [
                    'addressLine1' => 'DVSA',
                    'addressLine2' => 'Harehills',
                    'town' => 'Leeds',
                    'postcode' => 'LS9 6NF',
                ],
            ],
        ];

        // mocks
        $translator = m::mock();

        // expectations
        $translator
            ->shouldReceive('translate')
            ->with('ltyp_si')
            ->andReturn('Standard International');

        $sut = new Sut();
        // assertions
        $this->assertSame(
            $sut,
            $sut->populate(
                $inspectionRequest,
                $this->stubUserData,
                $this->stubPeopleData,
                $this->stubWorkshopsData,
                $translator
            )
        );

        $expected = [
            'inspectionRequestId' => 189781,
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
            'numberOfOperatingCentres' => 2,
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
            ],
        ];

        $vars = (array) $sut->getVariables();

        $this->assertEquals($expected, $vars);
    }

    public function testContructorPopulatesStubData()
    {
        $expected = [
            'inspectionRequestId' => '',
            'currentUserName' => '',
            'currentUserEmail' => '',
            'inspectionRequestDateRequested' => '',
            'inspectionRequestNotes' => '',
            'inspectionRequestDueDate' => '',
            'ocAddress' => null,
            'inspectionRequestType' => '',
            'licenceNumber' => '',
            'licenceType' => '',
            'totAuthVehicles' => '',
            'totAuthTrailers' => '',
            'numberOfOperatingCentres' => '',
            'expiryDate' => '',
            'operatorId' => '',
            'operatorName' => '',
            'operatorEmail' => '',
            'operatorAddress' => null,
            'contactPhoneNumbers' => null,
            'tradingNames' => [],
            'transportManagers' => [],
            'workshopIsExternal' => false,
            'safetyInspectionVehicles' => '',
            'safetyInspectionTrailers' => '',
            'inspectionProvider' => [],
            'people' => [],
            'otherLicences' => [],
            'applicationOperatingCentres' => [],
        ];

        $sut = new Sut();
        $vars = (array) $sut->getVariables();

        $this->assertEquals($expected, $vars);
    }

    public function testConstructorWithExtraData()
    {
        new Sut();
        $expected = [
            'inspectionRequestId' => 99,
            'currentUserName' => '',
            'currentUserEmail' => '',
            'inspectionRequestDateRequested' => '',
            'inspectionRequestNotes' => '',
            'inspectionRequestDueDate' => '',
            'ocAddress' => null,
            'inspectionRequestType' => '',
            'licenceNumber' => '',
            'licenceType' => '',
            'totAuthVehicles' => '',
            'totAuthTrailers' => '',
            'numberOfOperatingCentres' => '',
            'expiryDate' => '',
            'operatorId' => '',
            'operatorName' => '',
            'operatorEmail' => '',
            'operatorAddress' => null,
            'contactPhoneNumbers' => null,
            'tradingNames' => [],
            'transportManagers' => [],
            'workshopIsExternal' => false,
            'safetyInspectionVehicles' => '',
            'safetyInspectionTrailers' => '',
            'inspectionProvider' => [],
            'people' => [],
            'otherLicences' => [],
            'applicationOperatingCentres' => [],
            'foo' => 'bar',
        ];

        $sut = new Sut(['inspectionRequestId' => 99, 'foo' => 'bar']);
        $vars = (array) $sut->getVariables();

        $this->assertEquals($expected, $vars);
    }
}
