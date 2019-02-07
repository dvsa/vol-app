<?php

namespace OlcsTest\Service\Surrender;

use Common\RefData;
use Olcs\Service\Surrender\SurrenderStateService;
use PHPUnit\Framework\TestCase;

class SurrenderStateServiceTest extends TestCase
{
    /**
     * @dataProvider fetchRouteDataProvider
     */
    public function testFetchRoute($surrender, $expectedRoute)
    {
        $service = new SurrenderStateService();
        $service->setSurrenderData($surrender);
        $this->assertSame($expectedRoute, $service->fetchRoute());
    }

    /**
     * @dataProvider hasExpiredProvider
     */
    public function testHasExpired($surrender, $expected)
    {
        $service = new SurrenderStateService();
        $service->setSurrenderData($surrender);
        $this->assertSame($expected, $service->hasExpired());
    }

    /**
     * @dataProvider getStateProvider
     */
    public function testGetState($surrender, $expectedState)
    {
        $service = new SurrenderStateService();
        $service->setSurrenderData($surrender);
        $this->assertSame($expectedState, $service->getState());
    }

    public function fetchRouteDataProvider()
    {
        return [
            'status_start' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_START
                    ]
                ],
                'route' => 'licence/surrender/review-contact-details/GET'
            ],
            'status_contacts_complete' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_CONTACTS_COMPLETE
                    ]
                ],
                'route' => 'licence/surrender/current-discs/GET'
            ],
            'status_discs_complete' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DISCS_COMPLETE
                    ]
                ],
                'route' => 'licence/surrender/operator-licence/GET'
            ],
            'status_lic_docs_complete_is_IL' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_LIC_DOCS_COMPLETE
                    ],
                    'licence' => [
                        'licenceType' => [
                            'id' => RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
                        ]
                    ]
                ],
                'route' => 'licence/surrender/community-licence/GET'
            ],
            'status_lic_docs_complete_is_not_IL' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_LIC_DOCS_COMPLETE
                    ],
                    'licence' => [
                        'licenceType' => [
                            'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
                        ]
                    ]
                ],
                'route' => 'licence/surrender/review/GET'
            ],
            'status_comm_lic_docs_complete' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE
                    ]
                ],
                'route' => 'licence/surrender/review/GET'
            ],
            'status_details_confirmed' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DETAILS_CONFIRMED
                    ]
                ],
                'route' => 'licence/surrender/review/GET'
            ],
            'default' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_SIGNED
                    ]
                ],
                'route' => 'lva-licence'
            ]
        ];
    }

    public function hasExpiredProvider()
    {
        return [
            'has_created_and_is_expired' => [
                'surrender' => [
                    'createdOn' => '2019-01-31 14:13:09',
                    'lastModifiedOn' => null
                ],
                'expected' => true
            ],
            'has_created_modified_and_is_expired' => [
                'surrender' => [
                    'createdOn' => '2019-01-31 14:13:09',
                    'lastModifiedOn' => '2019-02-01 14:13:09'
                ],
                'expected' => true
            ],
            'has_created_and_is_not_expired' => [
                'surrender' => [
                    'createdOn' => date(DATE_ATOM, time()),
                    'lastModifiedOn' => null
                ],
                'expected' => false
            ],
            'has_created_modified_and_is_not_expired' => [
                'surrender' => [
                    'createdOn' => date(DATE_ATOM, time()),
                    'lastModifiedOn' => date(DATE_ATOM, time())
                ],
                'expected' => false
            ]
        ];
    }

    public function getStateProvider()
    {
        return [
            'application_started' =>[
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_START
                    ],
                    'createdOn' => date(DATE_ATOM, time()),
                    'lastModifiedOn' => null
                ],
                'expected' => SurrenderStateService::STATE_OK
            ],
            'application_expired' => [
                'surrender' => [
                    'createdOn' => '2019-01-31 14:13:09',
                    'lastModifiedOn' => null
                ],
                'expected' => SurrenderStateService::STATE_EXPIRED
            ],
            'goods_disc_count_information_changed' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DISCS_COMPLETE
                    ],
                    'discDestroyed' => null,
                    'discLost' => 10,
                    'discStolen' => null,
                    'createdOn' => '2019-01-31 14:13:09',
                    'lastModifiedOn' => date(DATE_ATOM, time()),
                    'addressLastModified' => date(DATE_ATOM, time()),
                    'licence' => [
                        'goodsOrPsv' => [
                            'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
                        ],
                    ],
                    'goodsDiscsOnLicence' => [
                        'discCount' => 8
                    ]

                ],
                'expected' => SurrenderStateService::STATE_INFORMATION_CHANGED
            ],
            'psv_disc_count_information_changed' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DISCS_COMPLETE
                    ],
                    'discDestroyed' => null,
                    'discLost' => 9,
                    'discStolen' => null,
                    'createdOn' => '2019-01-31 14:13:09',
                    'lastModifiedOn' => date(DATE_ATOM, time()),
                    'addressLastModified' => date(DATE_ATOM, time()),
                    'licence' => [
                        'goodsOrPsv' => [
                            'id' => RefData::LICENCE_CATEGORY_PSV
                        ],
                    ],
                    'psvDiscsOnLicence' => [
                        'discCount' => 5
                    ]

                ],
                'expected' => SurrenderStateService::STATE_INFORMATION_CHANGED
            ],
            'address_information_changed' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DISCS_COMPLETE
                    ],
                    'discDestroyed' => null,
                    'discLost' => 10,
                    'discStolen' => null,
                    'createdOn' => '2019-01-31 14:13:09',
                    'lastModifiedOn' => date(DATE_ATOM, time() - 60),
                    'addressLastModified' => date(DATE_ATOM, time()),
                    'licence' => [
                        'goodsOrPsv' => [
                            'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
                        ],
                    ],
                    'goodsDiscsOnLicence' => [
                        'discCount' => 10
                    ]

                ],
                'expected' => SurrenderStateService::STATE_INFORMATION_CHANGED
            ],
            'address_information_not_modified' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DISCS_COMPLETE
                    ],
                    'discDestroyed' => null,
                    'discLost' => 10,
                    'discStolen' => null,
                    'createdOn' => '2019-01-31 14:13:09',
                    'lastModifiedOn' => date(DATE_ATOM, time() - 60),
                    'addressLastModified' => null,
                    'licence' => [
                        'goodsOrPsv' => [
                            'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
                        ],
                    ],
                    'goodsDiscsOnLicence' => [
                        'discCount' => 10
                    ]

                ],
                'expected' => SurrenderStateService::STATE_OK
            ],
            'not_expired_and_not_changed' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DISCS_COMPLETE
                    ],
                    'discDestroyed' => null,
                    'discLost' => 10,
                    'discStolen' => null,
                    'createdOn' => '2019-01-31 14:13:09',
                    'lastModifiedOn' => date(DATE_ATOM, time()),
                    'addressLastModified' => date(DATE_ATOM, time()),
                    'licence' => [
                        'goodsOrPsv' => [
                            'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
                        ],
                    ],
                    'goodsDiscsOnLicence' => [
                        'discCount' => 10
                    ]
                ],
                'expected' => SurrenderStateService::STATE_OK
            ],
        ];
    }
}
