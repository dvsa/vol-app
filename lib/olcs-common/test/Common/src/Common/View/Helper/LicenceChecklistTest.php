<?php

namespace CommonTest\View\Helper;

use Common\View\Helper\LicenceChecklist;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\I18n\View\Helper\Translate;
use Common\RefData;

/**
 * @covers Common\View\Helper\LicenceChecklist
 */
class LicenceChecklistTest extends MockeryTestCase
{
    public $translator;
    /**
     * @var LicenceChecklist
     */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(Translate::class);
        $this->sut = new LicenceChecklist($this->translator);
    }

    /**
     * @dataProvider providerInvoke
     */
    public function testInvoke($type, $data, $expected): void
    {
        $this->translator
            ->shouldReceive('__invoke')
            ->andReturnUsing(
                static fn($arg) => $arg . '_translated'
            );

        $this->assertEquals($this->sut->__invoke($type, $data), $expected);
    }

    /**
     * @return (((((int|string)[]|int|string|true)[]|bool|int|string)[]|string)[]|string)[][]
     *
     * @psalm-return list{list{'foo', list{'bar'}, array<never, never>}, list{'type_of_licence', array{typeOfLicence: array{operatingFrom: 'GB', goodsOrPsv: 'goods', licenceType: 'type'}}, list{list{array{value: 'continuations.type-of-licence.operating-from_translated', header: true}, array{value: 'GB'}}, list{array{value: 'continuations.type-of-licence.type-of-operator_translated', header: true}, array{value: 'goods'}}, list{array{value: 'continuations.type-of-licence.type-of-licence_translated', header: true}, array{value: 'type'}}}}, list{'business_type', array{businessType: array{typeOfBusiness: 'ltd'}}, list{list{array{value: 'continuations.business-type.type-of-business_translated', header: true}, array{value: 'ltd'}}}}, list{'business_details', array{businessDetails: array{companyNumber: '12345678', companyName: 'foo', organisationLabel: 'bar', tradingNames: 'trading,names'}}, list{list{array{value: 'continuations.business-details.company-number_translated', header: true}, array{value: '12345678'}}, list{array{value: 'bar', header: true}, array{value: 'foo'}}, list{array{value: 'continuations.business-details.trading-names_translated', header: true}, array{value: 'trading,names'}}}}, list{'people', array{people: array{persons: list{array{name: 'name1', birthDate: 'birthDate1'}, array{name: 'name2', birthDate: 'birthDate2'}}, header: 'foo', displayPersonCount: 2}}, list{list{array{value: 'continuations.people-section.table.name_translated', header: true}, array{value: 'continuations.people-section.table.date-of-birth_translated', header: true}}, list{array{value: 'name1'}, array{value: 'birthDate1'}}, list{array{value: 'name2'}, array{value: 'birthDate2'}}}}, list{'people', array{people: array{persons: list{array{name: 'name1', birthDate: 'birthDate1'}, array{name: 'name2', birthDate: 'birthDate2'}, array{name: 'name3', birthDate: 'birthDate3'}}, header: 'foo', displayPersonCount: 2}}, list{list{array{value: 'foo', header: true}, array{value: 3}}}}, list{'vehicles', array{vehicles: array{vehicles: list{array{vrm: 'vrm1', weight: 1000}, array{vrm: 'vrm2', weight: 2000}}, displayVehiclesCount: 2, isGoods: true, header: 'foo'}}, list{list{array{value: 'continuations.vehicles-section.table.vrm_translated', header: true}, array{value: 'continuations.vehicles-section.table.weight_translated', header: true}}, list{array{value: 'vrm1'}, array{value: 1000}}, list{array{value: 'vrm2'}, array{value: 2000}}}}, list{'vehicles', array{vehicles: array{vehicles: list{array{vrm: 'vrm1', weight: 1000}, array{vrm: 'vrm2', weight: 2000}, array{vrm: 'vrm2', weight: 3000}}, displayVehiclesCount: 2, isGoods: true, header: 'foo'}}, list{list{array{value: 'foo', header: true}, array{value: 3}}}}, list{'users', array{users: array{users: list{array{name: 'Name1', email: 'test1@test.com', permission: 'permission1'}, array{name: 'Name2', email: 'test2@test.com', permission: 'permission2'}}, displayUsersCount: 2, header: 'foo'}}, list{list{array{value: 'continuations.users-section.table.name_translated', header: true}, array{value: 'continuations.users-section.table.email_translated', header: true}, array{value: 'continuations.users-section.table.permission_translated', header: true}}, list{array{value: 'Name1'}, array{value: 'test1@test.com'}, array{value: 'permission1'}}, list{array{value: 'Name2'}, array{value: 'test2@test.com'}, array{value: 'permission2'}}}}, list{'users', array{users: array{users: list{array{name: 'Name1', email: 'test1@test.com', permission: 'permission1'}, array{name: 'Name2', email: 'test2@test.com', permission: 'permission2'}, array{name: 'Name3', email: 'test3@test.com', permission: 'permission3'}}, displayUsersCount: 2, header: 'foo'}}, list{list{array{value: 'foo', header: true}, array{value: 3}}}}, list{'addresses', array{addresses: array{correspondenceAddress: 'correspondence address', establishmentAddress: 'establishment address', primaryNumber: '123', secondaryNumber: '456', correspondenceEmail: 'test@example.com', showEstablishmentAddress: true}}, list{list{array{value: 'continuations.addresses.correspondence-address.table.name_translated', header: true}, array{value: 'correspondence address'}}, list{array{value: 'continuations.addresses.establishment-address.table.name_translated', header: true}, array{value: 'establishment address'}}, list{array{value: 'continuations.addresses.primary-number.table.name_translated', header: true}, array{value: '123'}}, list{array{value: 'continuations.addresses.secondary-number.table.name_translated', header: true}, array{value: '456'}}, list{array{value: 'continuations.addresses.correspondence-email-address.table.name_translated', header: true}, array{value: 'test@example.com'}}}}, list{'addresses', array{addresses: array{correspondenceAddress: 'correspondence address', showEstablishmentAddress: true}}, list{list{array{value: 'continuations.addresses.correspondence-address.table.name_translated', header: true}, array{value: 'correspondence address'}}, list{array{value: 'continuations.addresses.establishment-address.table.name_translated', header: true}, array{value: 'continuations.addresses.establishment-address.same_translated'}}}}, list{'addresses', array{addresses: array{correspondenceAddress: 'correspondence address', showEstablishmentAddress: false}}, list{list{array{value: 'continuations.addresses.correspondence-address.table.name_translated', header: true}, array{value: 'correspondence address'}}}}, list{'operating_centres', array{operatingCentres: array{operatingCentres: list{array{name: 'Foo', vehicles: '1', trailers: '2'}, array{name: 'Bar', vehicles: '3', trailers: '4'}}, displayOperatingCentresCount: 10, ocVehiclesColumnHeader: 'vehicles', canHaveTrailers: true}}, list{list{array{value: 'continuations.oc-section.table.oc_translated', header: true}, array{value: 'continuations.oc-section.table.vehicles_translated', header: true}, array{value: 'continuations.oc-section.table.trailers_translated', header: true}}, list{array{value: 'Foo'}, array{value: '1'}, array{value: '2'}}, list{array{value: 'Bar'}, array{value: '3'}, array{value: '4'}}}}, list{'operating_centres', array{operatingCentres: array{operatingCentres: list{array{name: 'Foo', vehicles: '1', trailers: '2'}, array{name: 'Bar', vehicles: '3', trailers: '4'}}, displayOperatingCentresCount: 10, ocVehiclesColumnHeader: 'heavy-goods-vehicles', canHaveTrailers: true}}, list{list{array{value: 'continuations.oc-section.table.oc_translated', header: true}, array{value: 'continuations.oc-section.table.heavy-goods-vehicles_translated', header: true}, array{value: 'continuations.oc-section.table.trailers_translated', header: true}}, list{array{value: 'Foo'}, array{value: '1'}, array{value: '2'}}, list{array{value: 'Bar'}, array{value: '3'}, array{value: '4'}}}}, list{'operating_centres', array{operatingCentres: array{operatingCentres: list{array{name: 'Foo', vehicles: '1', trailers: '2'}, array{name: 'Bar', vehicles: '3', trailers: '4'}}, displayOperatingCentresCount: 10, ocVehiclesColumnHeader: 'vehicles', canHaveTrailers: false}}, list{list{array{value: 'continuations.oc-section.table.oc_translated', header: true}, array{value: 'continuations.oc-section.table.vehicles_translated', header: true}}, list{array{value: 'Foo'}, array{value: '1'}}, list{array{value: 'Bar'}, array{value: '3'}}}}, list{'operating_centres', array{operatingCentres: array{operatingCentres: list{array{name: 'Foo', vehicles: '1', trailers: '2'}, array{name: 'Bar', vehicles: '3', trailers: '4'}}, displayOperatingCentresCount: 1, ocVehiclesColumnHeader: 'vehicles', canHaveTrailers: true, totalOperatingCentres: 2}}, list{list{array{value: 'continuations.oc-section.table.total-oc_translated', header: true}, array{value: '2'}}}}, list{'operating_centres_authority', array{operatingCentres: array{totalVehicles: 10, totalTrailers: 20}}, list{list{array{value: 'continuations.oc-section.table.auth_vehicles_translated', header: true}, array{value: '10'}}, list{array{value: 'continuations.oc-section.table.auth_trailers_translated', header: true}, array{value: '20'}}}}, list{'operating_centres_authority', array{operatingCentres: array{totalHeavyGoodsVehicles: 10, totalLightGoodsVehicles: 15, totalTrailers: 20}}, list{list{array{value: 'continuations.oc-section.table.auth_heavy-goods-vehicles_translated', header: true}, array{value: '10'}}, list{array{value: 'continuations.oc-section.table.auth_light-goods-vehicles_translated', header: true}, array{value: '15'}}, list{array{value: 'continuations.oc-section.table.auth_trailers_translated', header: true}, array{value: '20'}}}}, list{'transport_managers', array{transportManagers: array{transportManagers: list{array{name: 'Mr Cake Baz', dob: '01/01/1970'}, array{name: 'Mr Foo Bar', dob: '01/01/1980'}}, displayTransportManagersCount: 10, totalTransportManagers: 2}}, list{list{array{value: 'continuations.tm-section.table.name_translated', header: true}, array{value: 'continuations.tm-section.table.dob_translated', header: true}}, list{array{value: 'Mr Cake Baz'}, array{value: '01/01/1970'}}, list{array{value: 'Mr Foo Bar'}, array{value: '01/01/1980'}}}}, list{'transport_managers', array{transportManagers: array{transportManagers: list{array{name: 'Mr Cake Baz', dob: '01/01/1970'}, array{name: 'Mr Foo Bar', dob: '01/01/1980'}}, displayTransportManagersCount: 1, totalTransportManagers: 2}}, list{list{array{value: 'continuations.tm-section.table.total-tm_translated', header: true}, array{value: '2'}}}}, list{'safety', array{safety: array{safetyInspectors: list{array{name: 'Baz Cake', address: 'Line 1, Town 1'}, array{name: 'Foo Bar', address: 'Line 2, Town 2'}}, displaySafetyInspectorsCount: 2, totalSafetyInspectors: 2}}, list{list{array{value: 'continuations.safety-section.table.inspector_translated', header: true}, array{value: 'continuations.safety-section.table.address_translated', header: true}}, list{array{value: 'Baz Cake'}, array{value: 'Line 1, Town 1'}}, list{array{value: 'Foo Bar'}, array{value: 'Line 2, Town 2'}}}}, list{'safety', array{safety: array{safetyInspectors: list{array{name: 'Baz Cake', address: 'Line 1, Town 1'}, array{name: 'Foo Bar', address: 'Line 2, Town 2'}}, displaySafetyInspectorsCount: 1, totalSafetyInspectors: 2}}, list{list{array{value: 'continuations.safety-section.table.total-inspectors_translated', header: true}, array{value: 2}}}}, list{'safety_details', array{safety: array{safetyInsVehicles: 2, safetyInsTrailers: 2, safetyInsVaries: 'Yes', tachographIns: 'External', showCompany: true, tachographInsName: 'ABC Ltd', canHaveTrailers: true}}, list{list{array{value: 'continuations.safety-section.table.max-time-vehicles_translated', header: true}, array{value: 2}}, list{array{value: 'continuations.safety-section.table.max-time-trailers_translated', header: true}, array{value: 2}}, list{array{value: 'continuations.safety-section.table.varies_translated', header: true}, array{value: 'Yes'}}, list{array{value: 'continuations.safety-section.table.tachographs_translated', header: true}, array{value: 'External'}}, list{array{value: 'continuations.safety-section.table.tachographInsName_translated', header: true}, array{value: 'ABC Ltd'}}}}, list{'safety_details', array{safety: array{safetyInsVehicles: 2, safetyInsTrailers: 2, safetyInsVaries: 'Yes', tachographIns: 'External', showCompany: true, tachographInsName: 'ABC Ltd', canHaveTrailers: false}}, list{list{array{value: 'continuations.safety-section.table.max-time-vehicles_translated', header: true}, array{value: 2}}, list{array{value: 'continuations.safety-section.table.varies.no-trailers_translated', header: true}, array{value: 'Yes'}}, list{array{value: 'continuations.safety-section.table.tachographs_translated', header: true}, array{value: 'External'}}, list{array{value: 'continuations.safety-section.table.tachographInsName_translated', header: true}, array{value: 'ABC Ltd'}}}}}
     */
    public function providerInvoke(): array
    {
        return [
            [
                'foo',
                ['bar'],
                []
            ],
            [
                RefData::LICENCE_CHECKLIST_TYPE_OF_LICENCE,
                [
                    'typeOfLicence' => [
                        'operatingFrom' => 'GB',
                        'goodsOrPsv' => 'goods',
                        'licenceType' => 'type'
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.type-of-licence.operating-from_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'GB'
                        ]
                    ],
                    [
                        [
                            'value' => 'continuations.type-of-licence.type-of-operator_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'goods'
                        ],
                    ],
                    [
                        [
                            'value' => 'continuations.type-of-licence.type-of-licence_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'type'
                        ]
                    ]
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_BUSINESS_TYPE,
                [
                    'businessType' => [
                        'typeOfBusiness' => 'ltd'
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.business-type.type-of-business_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'ltd'
                        ]
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_BUSINESS_DETAILS,
                [
                    'businessDetails' => [
                        'companyNumber' => '12345678',
                        'companyName' => 'foo',
                        'organisationLabel' => 'bar',
                        'tradingNames' => 'trading,names'
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.business-details.company-number_translated',
                            'header' => true
                        ],
                        [
                            'value' => '12345678'
                        ]
                    ],
                    [
                        ['value' => 'bar', 'header' => true],
                        ['value' => 'foo']
                    ],
                    [
                        [
                            'value' => 'continuations.business-details.trading-names_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'trading,names'
                        ]
                    ]
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_PEOPLE,
                [
                    'people' => [
                        'persons' => [
                            [
                                'name' => 'name1',
                                'birthDate' => 'birthDate1'
                            ],
                            [
                                'name' => 'name2',
                                'birthDate' => 'birthDate2'
                            ],
                        ],
                        'header' => 'foo',
                        'displayPersonCount' => 2
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.people-section.table.name_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'continuations.people-section.table.date-of-birth_translated',
                            'header' => true
                        ],
                    ],
                    [
                        ['value' => 'name1'],
                        ['value' => 'birthDate1']
                    ],
                    [
                        ['value' => 'name2'],
                        ['value' => 'birthDate2']
                    ]
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_PEOPLE,
                [
                    'people' => [
                        'persons' => [
                            [
                                'name' => 'name1',
                                'birthDate' => 'birthDate1'
                            ],
                            [
                                'name' => 'name2',
                                'birthDate' => 'birthDate2'
                            ],
                            [
                                'name' => 'name3',
                                'birthDate' => 'birthDate3'
                            ],
                        ],
                        'header' => 'foo',
                        'displayPersonCount' => 2
                    ]
                ],
                [
                    [
                        ['value' => 'foo', 'header' => true],
                        ['value' => 3]
                    ]
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_VEHICLES,
                [
                    'vehicles' => [
                        'vehicles' => [
                            [
                                'vrm' => 'vrm1',
                                'weight' => 1000
                            ],
                            [
                                'vrm' => 'vrm2',
                                'weight' => 2000
                            ],
                        ],
                        'displayVehiclesCount' => 2,
                        'isGoods' => true,
                        'header' => 'foo'
                    ],
                ],
                [
                    [
                        [
                            'value' => 'continuations.vehicles-section.table.vrm_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'continuations.vehicles-section.table.weight_translated',
                            'header' => true
                        ]
                    ],
                    [
                        ['value' => 'vrm1'],
                        ['value' => 1000],
                    ],
                    [
                        ['value' => 'vrm2'],
                        ['value' => 2000]
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_VEHICLES,
                [
                    'vehicles' => [
                        'vehicles' => [
                            [
                                'vrm' => 'vrm1',
                                'weight' => 1000
                            ],
                            [
                                'vrm' => 'vrm2',
                                'weight' => 2000
                            ],
                            [
                                'vrm' => 'vrm2',
                                'weight' => 3000
                            ],
                        ],
                        'displayVehiclesCount' => 2,
                        'isGoods' => true,
                        'header' => 'foo'
                    ],
                ],
                [
                    [
                        ['value' => 'foo', 'header' => true],
                        ['value' => 3]
                    ]
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_USERS,
                [
                    'users' => [
                        'users' => [
                            [
                                'name' => 'Name1',
                                'email' => 'test1@test.com',
                                'permission' => 'permission1'
                            ],
                            [
                                'name' => 'Name2',
                                'email' => 'test2@test.com',
                                'permission' => 'permission2'
                            ],
                        ],
                        'displayUsersCount' => 2,
                        'header' => 'foo'
                    ],
                ],
                [
                    [
                        [
                            'value' => 'continuations.users-section.table.name_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'continuations.users-section.table.email_translated',
                            'header' => true
                        ],
                        [
                            'value' => 'continuations.users-section.table.permission_translated',
                            'header' => true
                        ]
                    ],
                    [
                        ['value' => 'Name1'],
                        ['value' => 'test1@test.com'],
                        ['value' => 'permission1'],
                    ],
                    [
                        ['value' => 'Name2'],
                        ['value' => 'test2@test.com'],
                        ['value' => 'permission2']
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_USERS,
                [
                    'users' => [
                        'users' => [
                            [
                                'name' => 'Name1',
                                'email' => 'test1@test.com',
                                'permission' => 'permission1'
                            ],
                            [
                                'name' => 'Name2',
                                'email' => 'test2@test.com',
                                'permission' => 'permission2'
                            ],
                            [
                                'name' => 'Name3',
                                'email' => 'test3@test.com',
                                'permission' => 'permission3'
                            ]
                        ],
                        'displayUsersCount' => 2,
                        'header' => 'foo'
                    ],
                ],
                [
                    [
                        ['value' => 'foo', 'header' => true],
                        ['value' => 3]
                    ]
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_ADDRESSES,
                [
                    'addresses' => [
                        'correspondenceAddress' => 'correspondence address',
                        'establishmentAddress' => 'establishment address',
                        'primaryNumber' => '123',
                        'secondaryNumber' => '456',
                        'correspondenceEmail' => 'test@example.com',
                        'showEstablishmentAddress' => true,
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.addresses.correspondence-address.table.name_translated',
                            'header' => true
                        ],
                        ['value' => 'correspondence address']
                    ],
                    [
                        [
                            'value' => 'continuations.addresses.establishment-address.table.name_translated',
                            'header' => true
                        ],
                        ['value' => 'establishment address']
                    ],
                    [
                        ['value' => 'continuations.addresses.primary-number.table.name_translated', 'header' => true],
                        ['value' => '123']
                    ],
                    [
                        ['value' => 'continuations.addresses.secondary-number.table.name_translated', 'header' => true],
                        ['value' => '456']
                    ],
                    [
                        [
                            'value' => 'continuations.addresses.correspondence-email-address.table.name_translated',
                            'header' => true
                        ],
                        ['value' => 'test@example.com']
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_ADDRESSES,
                [
                    'addresses' => [
                        'correspondenceAddress' => 'correspondence address',
                        'showEstablishmentAddress' => true,
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.addresses.correspondence-address.table.name_translated',
                            'header' => true
                        ],
                        ['value' => 'correspondence address']
                    ],
                    [
                        [
                            'value' => 'continuations.addresses.establishment-address.table.name_translated',
                            'header' => true
                        ],
                        ['value' => 'continuations.addresses.establishment-address.same_translated']
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_ADDRESSES,
                [
                    'addresses' => [
                        'correspondenceAddress' => 'correspondence address',
                        'showEstablishmentAddress' => false,
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.addresses.correspondence-address.table.name_translated',
                            'header' => true
                        ],
                        ['value' => 'correspondence address']
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_OPERATING_CENTRES,
                [
                    'operatingCentres' => [
                        'operatingCentres' => [
                            [
                                'name' => 'Foo',
                                'vehicles' => '1',
                                'trailers' => '2'
                            ],
                            [
                                'name' => 'Bar',
                                'vehicles' => '3',
                                'trailers' => '4'
                            ]
                        ],
                        'displayOperatingCentresCount' => 10,
                        'ocVehiclesColumnHeader' => 'vehicles',
                        'canHaveTrailers' => true,
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.oc-section.table.oc_translated', 'header' => true
                        ],
                        [
                            'value' => 'continuations.oc-section.table.vehicles_translated', 'header' => true
                        ],
                        [
                            'value' => 'continuations.oc-section.table.trailers_translated', 'header' => true
                        ]
                    ],
                    [
                        ['value' => 'Foo'],
                        ['value' => '1'],
                        ['value' => '2'],
                    ],
                    [
                        ['value' => 'Bar'],
                        ['value' => '3'],
                        ['value' => '4'],
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_OPERATING_CENTRES,
                [
                    'operatingCentres' => [
                        'operatingCentres' => [
                            [
                                'name' => 'Foo',
                                'vehicles' => '1',
                                'trailers' => '2'
                            ],
                            [
                                'name' => 'Bar',
                                'vehicles' => '3',
                                'trailers' => '4'
                            ]
                        ],
                        'displayOperatingCentresCount' => 10,
                        'ocVehiclesColumnHeader' => 'heavy-goods-vehicles',
                        'canHaveTrailers' => true,
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.oc-section.table.oc_translated', 'header' => true
                        ],
                        [
                            'value' => 'continuations.oc-section.table.heavy-goods-vehicles_translated', 'header' => true
                        ],
                        [
                            'value' => 'continuations.oc-section.table.trailers_translated', 'header' => true
                        ]
                    ],
                    [
                        ['value' => 'Foo'],
                        ['value' => '1'],
                        ['value' => '2'],
                    ],
                    [
                        ['value' => 'Bar'],
                        ['value' => '3'],
                        ['value' => '4'],
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_OPERATING_CENTRES,
                [
                    'operatingCentres' => [
                        'operatingCentres' => [
                            [
                                'name' => 'Foo',
                                'vehicles' => '1',
                                'trailers' => '2'
                            ],
                            [
                                'name' => 'Bar',
                                'vehicles' => '3',
                                'trailers' => '4'
                            ]
                        ],
                        'displayOperatingCentresCount' => 10,
                        'ocVehiclesColumnHeader' => 'vehicles',
                        'canHaveTrailers' => false,
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.oc-section.table.oc_translated', 'header' => true
                        ],
                        [
                            'value' => 'continuations.oc-section.table.vehicles_translated', 'header' => true
                        ],
                    ],
                    [
                        ['value' => 'Foo'],
                        ['value' => '1'],
                    ],
                    [
                        ['value' => 'Bar'],
                        ['value' => '3'],
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_OPERATING_CENTRES,
                [
                    'operatingCentres' => [
                        'operatingCentres' => [
                            [
                                'name' => 'Foo',
                                'vehicles' => '1',
                                'trailers' => '2'
                            ],
                            [
                                'name' => 'Bar',
                                'vehicles' => '3',
                                'trailers' => '4'
                            ]
                        ],
                        'displayOperatingCentresCount' => 1,
                        'ocVehiclesColumnHeader' => 'vehicles',
                        'canHaveTrailers' => true,
                        'totalOperatingCentres' => 2
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.oc-section.table.total-oc_translated', 'header' => true
                        ],
                        [
                            'value' => '2'
                        ],
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_OPERATING_CENTRES_AUTHORITY,
                [
                    'operatingCentres' => [
                        'totalVehicles' => 10,
                        'totalTrailers' => 20,
                    ]
                ],
                [
                    [
                        ['value' => 'continuations.oc-section.table.auth_vehicles_translated', 'header' => true],
                        ['value' => '10'],
                    ],
                    [
                        ['value' => 'continuations.oc-section.table.auth_trailers_translated', 'header' => true],
                        ['value' => '20'],
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_OPERATING_CENTRES_AUTHORITY,
                [
                    'operatingCentres' => [
                        'totalHeavyGoodsVehicles' => 10,
                        'totalLightGoodsVehicles' => 15,
                        'totalTrailers' => 20,
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.oc-section.table.auth_heavy-goods-vehicles_translated',
                            'header' => true
                        ],
                        ['value' => '10'],
                    ],
                    [
                        [
                            'value' => 'continuations.oc-section.table.auth_light-goods-vehicles_translated',
                            'header' => true
                        ],
                        ['value' => '15'],
                    ],
                    [
                        ['value' => 'continuations.oc-section.table.auth_trailers_translated', 'header' => true],
                        ['value' => '20'],
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_TRANSPORT_MANAGERS,
                [
                    'transportManagers' => [
                        'transportManagers' => [
                            [
                                'name' => 'Mr Cake Baz',
                                'dob' => '01/01/1970',
                            ],
                            [
                                'name' => 'Mr Foo Bar',
                                'dob' => '01/01/1980',
                            ],
                        ],
                        'displayTransportManagersCount' => 10,
                        'totalTransportManagers' => 2
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.tm-section.table.name_translated', 'header' => true
                        ],
                        [
                            'value' => 'continuations.tm-section.table.dob_translated', 'header' => true
                        ],
                    ],
                    [
                        ['value' => 'Mr Cake Baz'],
                        ['value' => '01/01/1970'],
                    ],
                    [
                        ['value' => 'Mr Foo Bar'],
                        ['value' => '01/01/1980'],
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_TRANSPORT_MANAGERS,
                [
                    'transportManagers' => [
                        'transportManagers' => [
                            [
                                'name' => 'Mr Cake Baz',
                                'dob' => '01/01/1970',
                            ],
                            [
                                'name' => 'Mr Foo Bar',
                                'dob' => '01/01/1980',
                            ],
                        ],
                        'displayTransportManagersCount' => 1,
                        'totalTransportManagers' => 2
                    ]
                ],
                [
                    [
                        ['value' => 'continuations.tm-section.table.total-tm_translated', 'header' => true],
                        ['value' => '2'],
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_SAFETY_INSPECTORS,
                [
                    'safety' => [
                        'safetyInspectors' => [
                            [
                                'name' => 'Baz Cake',
                                'address' => 'Line 1, Town 1',
                            ],
                            [
                                'name' => 'Foo Bar',
                                'address' => 'Line 2, Town 2',
                            ],
                        ],
                        'displaySafetyInspectorsCount' => 2,
                        'totalSafetyInspectors' => 2
                    ]
                ],
                [
                    [
                        ['value' => 'continuations.safety-section.table.inspector_translated', 'header' => true],
                        ['value' => 'continuations.safety-section.table.address_translated', 'header' => true],
                    ],
                    [
                        ['value' => 'Baz Cake'],
                        ['value' => 'Line 1, Town 1'],
                    ],
                    [
                        ['value' => 'Foo Bar'],
                        ['value' => 'Line 2, Town 2'],
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_SAFETY_INSPECTORS,
                [
                    'safety' => [
                        'safetyInspectors' => [
                            [
                                'name' => 'Baz Cake',
                                'address' => 'Line 1, Town 1',
                            ],
                            [
                                'name' => 'Foo Bar',
                                'address' => 'Line 2, Town 2',
                            ],
                        ],
                        'displaySafetyInspectorsCount' => 1,
                        'totalSafetyInspectors' => 2
                    ]
                ],
                [
                    [
                        ['value' => 'continuations.safety-section.table.total-inspectors_translated', 'header' => true],
                        ['value' => 2],
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_SAFETY_DETAILS,
                [
                    'safety' => [
                        'safetyInsVehicles' => 2,
                        'safetyInsTrailers' => 2,
                        'safetyInsVaries' => 'Yes',
                        'tachographIns' => 'External',
                        'showCompany' => true,
                        'tachographInsName' => 'ABC Ltd',
                        'canHaveTrailers' => true,
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.safety-section.table.max-time-vehicles_translated',
                            'header' => true
                        ],
                        ['value' => 2],
                    ],
                    [
                        [
                            'value' => 'continuations.safety-section.table.max-time-trailers_translated',
                            'header' => true
                        ],
                        ['value' => 2],
                    ],
                    [
                        [
                            'value' => 'continuations.safety-section.table.varies_translated',
                            'header' => true
                        ],
                        ['value' => 'Yes'],
                    ],
                    [
                        [
                            'value' => 'continuations.safety-section.table.tachographs_translated',
                            'header' => true
                        ],
                        ['value' => 'External'],
                    ],
                    [
                        [
                            'value' => 'continuations.safety-section.table.tachographInsName_translated',
                            'header' => true
                        ],
                        ['value' => 'ABC Ltd'],
                    ],
                ]
            ],
            [
                RefData::LICENCE_CHECKLIST_SAFETY_DETAILS,
                [
                    'safety' => [
                        'safetyInsVehicles' => 2,
                        'safetyInsTrailers' => 2,
                        'safetyInsVaries' => 'Yes',
                        'tachographIns' => 'External',
                        'showCompany' => true,
                        'tachographInsName' => 'ABC Ltd',
                        'canHaveTrailers' => false,
                    ]
                ],
                [
                    [
                        [
                            'value' => 'continuations.safety-section.table.max-time-vehicles_translated',
                            'header' => true
                        ],
                        ['value' => 2],
                    ],
                    [
                        [
                            'value' => 'continuations.safety-section.table.varies.no-trailers_translated',
                            'header' => true
                        ],
                        ['value' => 'Yes'],
                    ],
                    [
                        [
                            'value' => 'continuations.safety-section.table.tachographs_translated',
                            'header' => true
                        ],
                        ['value' => 'External'],
                    ],
                    [
                        [
                            'value' => 'continuations.safety-section.table.tachographInsName_translated',
                            'header' => true
                        ],
                        ['value' => 'ABC Ltd'],
                    ],
                ]
            ],
        ];
    }
}
