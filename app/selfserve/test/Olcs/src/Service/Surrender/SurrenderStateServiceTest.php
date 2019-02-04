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
    public function testFetchRoute($surrender, $page)
    {
        $service = new SurrenderStateService($surrender);
        $fetchedRoute = $service->fetchRoute();

        $expectedRoute = 'licence/surrender/' . $page . '/GET';

        $this->assertEquals($expectedRoute, $fetchedRoute);
    }

    /**
     * @dataProvider hasExpiredProvider
     */
    public function testHasExpired($surrender, $expected)
    {
        $service = new SurrenderStateService($surrender);
        $this->assertEquals($expected, $service->hasExpired());
    }

    /**
     * @dataProvider getStateProvider
     */
    public function testGetState($surrender, $expectedState)
    {
        $service = new SurrenderStateService($surrender);

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
                'page' => 'review-contact-details'
            ],
            'status_contacts_complete' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_CONTACTS_COMPLETE
                    ]
                ],
                'page' => 'current-discs'
            ],
            'status_discs_complete' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DISCS_COMPLETE
                    ]
                ],
                'page' => 'operator-licence'
            ],
            'status_lic_docs_complete_is_IL' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_LIC_DOCS_COMPLETE
                    ],
                    'licence' => [
                        'isInternationalLicence' => true
                    ]
                ],
                'page' => 'community-licence'
            ],
            'status_lic_docs_complete_is_not_IL' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_LIC_DOCS_COMPLETE
                    ],
                    'licence' => [
                        'isInternationalLicence' => false
                    ]
                ],
                'page' => 'review'
            ],
            'status_comm_lic_docs_complete' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE
                    ]
                ],
                'page' => 'review'
            ],
            'status_details_confirmed' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DETAILS_CONFIRMED
                    ]
                ],
                'page' => 'review'
            ],
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
            'application_expired' => [
                'surredner' => [
                    'createdOn' => '2019-01-31 14:13:09',
                    'lastModifiedOn' => null
                ],
                'expected' => SurrenderStateService::STATE_EXPIRED
            ],
            'goods_disc_count_information_changed' => [
                'surredner' => [
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
                'surredner' => [
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
                'surredner' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DISCS_COMPLETE
                    ],
                    'discDestroyed' => null,
                    'discLost' => 10,
                    'discStolen' => null,
                    'createdOn' => '2019-01-31 14:13:09',
                    'lastModifiedOn' => date(DATE_ATOM, time()-60),
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
            'not_expired_and_not_changed' => [
                'surredner' => [
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
