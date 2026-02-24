<?php

declare(strict_types=1);

namespace OlcsTest\Service\Surrender;

use Common\RefData;
use Olcs\Service\Surrender\SurrenderStateService;
use PHPUnit\Framework\TestCase;

class SurrenderStateServiceTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('fetchRouteDataProvider')]
    public function testFetchRoute(array $surrender, string $expectedRoute): void
    {
        $service = new SurrenderStateService();
        $service->setSurrenderData($surrender);
        $this->assertSame($expectedRoute, $service->fetchRoute());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('hasExpiredProvider')]
    public function testHasExpired(array $surrender, bool $expected): void
    {
        $service = new SurrenderStateService();
        $service->setSurrenderData($surrender);
        $this->assertSame($expected, $service->hasExpired());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getStateProvider')]
    public function testGetState(array $surrender, string $expectedState): void
    {
        $service = new SurrenderStateService();
        $service->setSurrenderData($surrender);
        $this->assertSame($expectedState, $service->getState());
    }

    /**
     * @return ((string|string[])[][]|string)[][]
     *
     * @psalm-return array{status_start: array{surrender: array{status: array{id: 'surr_sts_start'}}, route: 'licence/surrender/review-contact-details/GET'}, status_contacts_complete: array{surrender: array{status: array{id: 'surr_sts_contacts_complete'}}, route: 'licence/surrender/current-discs/GET'}, status_discs_complete: array{surrender: array{status: array{id: 'surr_sts_discs_complete'}}, route: 'licence/surrender/operator-licence/GET'}, status_lic_docs_complete_is_IL: array{surrender: array{status: array{id: 'surr_sts_lic_docs_complete'}, licence: array{licenceType: array{id: 'ltyp_si'}}}, route: 'licence/surrender/community-licence/GET'}, status_lic_docs_complete_is_not_IL: array{surrender: array{status: array{id: 'surr_sts_lic_docs_complete'}, licence: array{licenceType: array{id: 'ltyp_sn'}}}, route: 'licence/surrender/review/GET'}, status_comm_lic_docs_complete: array{surrender: array{status: array{id: 'surr_sts_comm_lic_docs_complete'}}, route: 'licence/surrender/review/GET'}, status_details_confirmed: array{surrender: array{status: array{id: 'surr_sts_details_confirmed'}}, route: 'licence/surrender/review/GET'}, default: array{surrender: array{status: array{id: 'surr_sts_signed'}}, route: 'lva-licence'}}
     */
    public static function fetchRouteDataProvider(): array
    {
        return [
            'status_start' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_START
                    ]
                ],
                'expectedRoute' => 'licence/surrender/review-contact-details/GET'
            ],
            'status_contacts_complete' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_CONTACTS_COMPLETE
                    ]
                ],
                'expectedRoute' => 'licence/surrender/current-discs/GET'
            ],
            'status_discs_complete' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DISCS_COMPLETE
                    ]
                ],
                'expectedRoute' => 'licence/surrender/operator-licence/GET'
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
                'expectedRoute' => 'licence/surrender/community-licence/GET'
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
                'expectedRoute' => 'licence/surrender/review/GET'
            ],
            'status_comm_lic_docs_complete' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE
                    ]
                ],
                'expectedRoute' => 'licence/surrender/review/GET'
            ],
            'status_details_confirmed' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DETAILS_CONFIRMED
                    ]
                ],
                'expectedRoute' => 'licence/surrender/review/GET'
            ],
            'default' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_SIGNED
                    ]
                ],
                'expectedRoute' => 'lva-licence'
            ]
        ];
    }

    /**
     * @return ((null|string)[]|bool)[][]
     *
     * @psalm-return array{has_created_and_is_expired: array{surrender: array{createdOn: '2019-01-31 14:13:09', lastModifiedOn: null}, expected: true}, has_created_modified_and_is_expired: array{surrender: array{createdOn: '2019-01-31 14:13:09', lastModifiedOn: '2019-02-01 14:13:09'}, expected: true}, has_created_and_is_not_expired: array{surrender: array{createdOn: string, lastModifiedOn: null}, expected: false}, has_created_modified_and_is_not_expired: array{surrender: array{createdOn: string, lastModifiedOn: string}, expected: false}}
     */
    public static function hasExpiredProvider(): array
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

    /**
     * @return (((int|string|string[])[]|int|null|string)[]|string)[][]
     *
     * @psalm-return array{application_started: array{surrender: array{status: array{id: 'surr_sts_start'}, createdOn: string, lastModifiedOn: null}, expected: 'surrender_application_ok'}, application_withdrawn: array{surrender: array{status: array{id: 'surr_sts_withdrawn'}, createdOn: string, lastModifiedOn: null}, expected: 'surrender_application_withdrawn'}, application_expired: array{surrender: array{status: array{id: 'surr_sts_discs_complete'}, createdOn: '2019-01-31 14:13:09', lastModifiedOn: null}, expected: 'surrender_application_expired'}, goods_disc_count_information_changed: array{surrender: array{status: array{id: 'surr_sts_discs_complete'}, discDestroyed: null, discLost: 10, discStolen: null, createdOn: '2019-01-31 14:13:09', lastModifiedOn: string, addressLastModified: string, licence: array{goodsOrPsv: array{id: 'lcat_gv'}}, goodsDiscsOnLicence: array{discCount: 8}}, expected: 'surrender_application_changed'}, psv_disc_count_information_changed: array{surrender: array{status: array{id: 'surr_sts_discs_complete'}, discDestroyed: null, discLost: 9, discStolen: null, createdOn: '2019-01-31 14:13:09', lastModifiedOn: string, addressLastModified: string, licence: array{goodsOrPsv: array{id: 'lcat_psv'}}, psvDiscsOnLicence: array{discCount: 5}}, expected: 'surrender_application_changed'}, address_information_changed: array{surrender: array{status: array{id: 'surr_sts_discs_complete'}, discDestroyed: null, discLost: 10, discStolen: null, createdOn: '2019-01-31 14:13:09', lastModifiedOn: string, addressLastModified: string, licence: array{goodsOrPsv: array{id: 'lcat_gv'}}, goodsDiscsOnLicence: array{discCount: 10}}, expected: 'surrender_application_changed'}, address_information_not_modified: array{surrender: array{status: array{id: 'surr_sts_discs_complete'}, discDestroyed: null, discLost: 10, discStolen: null, createdOn: '2019-01-31 14:13:09', lastModifiedOn: string, addressLastModified: null, licence: array{goodsOrPsv: array{id: 'lcat_gv'}}, goodsDiscsOnLicence: array{discCount: 10}}, expected: 'surrender_application_ok'}, not_expired_and_not_changed: array{surrender: array{status: array{id: 'surr_sts_discs_complete'}, discDestroyed: null, discLost: 10, discStolen: null, createdOn: '2019-01-31 14:13:09', lastModifiedOn: string, addressLastModified: string, licence: array{goodsOrPsv: array{id: 'lcat_gv'}}, goodsDiscsOnLicence: array{discCount: 10}}, expected: 'surrender_application_ok'}}
     */
    public static function getStateProvider(): array
    {
        return [
            'application_started' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_START
                    ],
                    'createdOn' => date(DATE_ATOM, time()),
                    'lastModifiedOn' => null
                ],
                'expectedState' => SurrenderStateService::STATE_OK
            ],
            'application_withdrawn' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_WITHDRAWN
                    ],
                    'createdOn' => date(DATE_ATOM, time()),
                    'lastModifiedOn' => null
                ],
                'expectedState' => SurrenderStateService::STATE_WITHDRAWN
            ],
            'application_expired' => [
                'surrender' => [
                    'status' => [
                        'id' => RefData::SURRENDER_STATUS_DISCS_COMPLETE
                    ],
                    'createdOn' => '2019-01-31 14:13:09',
                    'lastModifiedOn' => null
                ],
                'expectedState' => SurrenderStateService::STATE_EXPIRED
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
                'expectedState' => SurrenderStateService::STATE_INFORMATION_CHANGED
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
                'expectedState' => SurrenderStateService::STATE_INFORMATION_CHANGED
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
                'expectedState' => SurrenderStateService::STATE_INFORMATION_CHANGED
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
                'expectedState' => SurrenderStateService::STATE_OK
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
                'expectedState' => SurrenderStateService::STATE_OK
            ],
        ];
    }
}
