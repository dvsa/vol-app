<?php

declare(strict_types=1);

/**
 * Licence Overview Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\View\Model\Licence;

use Olcs\View\Model\Licence\LicenceOverview;

/**
 * Licence Overview Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceOverviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test constructor with set variables
     */
    #[\PHPUnit\Framework\Attributes\Group('licenceOverview')]
    public function testSetVariables(): void
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->licenceId, 1);
        $this->assertEquals($overview->startDate, '2014-01-01');
        $this->assertEquals($overview->renewalDate, '2015-01-01');
        $this->assertEquals($overview->status, 'status');
        $this->assertEquals($overview->showExpiryWarning, 'SHOWEXPIRYWARNING');
        $this->assertEquals($overview->returnDefaultInfoBoxLinks(), $this->returnExpectedInfoBoxLinks());
    }

    public function testSetVariablesIsExpired(): void
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'isExpired' => true,
            'isExpiring' => true,
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->licenceId, 1);
        $this->assertEquals($overview->startDate, '2014-01-01');
        $this->assertEquals($overview->renewalDate, '2015-01-01');
        $this->assertEquals($overview->status, 'licence.status.expired');
        $this->assertEquals($overview->returnDefaultInfoBoxLinks(), $this->returnExpectedInfoBoxLinks());
    }

    public function testSetVariablesIsExpiring(): void
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'isExpired' => false,
            'isExpiring' => true,
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->licenceId, 1);
        $this->assertEquals($overview->startDate, '2014-01-01');
        $this->assertEquals($overview->renewalDate, '2015-01-01');
        $this->assertEquals($overview->status, 'licence.status.expiring');
        $this->assertEquals($overview->returnDefaultInfoBoxLinks(), $this->returnExpectedInfoBoxLinks());
    }

    public function testSetVariablesContinuationDetailId(): void
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'isExpired' => false,
            'isExpiring' => true,
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
            'continuationMarker' => ['id' => 12345],
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->continuationDetailId, 12345);
        $this->assertEquals($this->returnExpectedInfoBoxLinks(), $overview->getInfoBoxLinks());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpAddInfoBoxLinks')]
    public function testAddInfoBoxLinks(array $additionalLinks, array $expectedLinks): void
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'isExpired' => false,
            'isExpiring' => true,
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
            'continuationMarker' => ['id' => 12345],
        ];

        $overview = new LicenceOverview($data);
        $overview->addInfoBoxLinks($additionalLinks);
        $this->assertEquals($expectedLinks, $overview->getInfoBoxLinks());
    }

    public function testSetInfoBoxLinks(): void
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'isExpired' => false,
            'isExpiring' => true,
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
            'continuationMarker' => ['id' => 12345],
        ];

        $overview = new LicenceOverview($data);
        $overview->setInfoBoxLinks();
        $this->assertEquals($overview->infoBoxLinks, $this->returnExpectedInfoBoxLinks());
    }

    /**
     * @return ((array|string|true)[]|string)[][]
     *
     * @psalm-return list{array{linkUrl: array{route: 'licence-print', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'licence.print'}}
     */
    public function returnExpectedInfoBoxLinks(): array
    {
        return
            [
                [
                    'linkUrl' => [
                        'route' => 'licence-print',
                        'params' => [],
                        'options' => [],
                        'reuseMatchedParams' => true
                    ],
                    'linkText' => 'licence.print'
                ],
            ];
    }

    /**
     * @return (((array|string|true)[]|string|true)[]|string)[][][]
     *
     * @psalm-return list{array{additionalInfoBoxLinks: array{linkUrl: array{route: 'additional-route', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'additional-link-text'}, expectedInfoBoxLinks: list{array{linkUrl: array{route: 'licence-print', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'licence.print'}, array{linkUrl: array{route: 'additional-route', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'additional-link-text'}}}, array{additionalInfoBoxLinks: array<never, never>, expectedInfoBoxLinks: list{array{linkUrl: array{route: 'licence-print', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'licence.print'}}}}
     */
    public static function dpAddInfoBoxLinks(): array
    {
        return [
            [
                'additionalLinks' => [
                    'linkUrl' => [
                        'route' => 'additional-route',
                        'params' => [],
                        'options' => [],
                        'reuseMatchedParams' => true
                    ],
                    'linkText' => 'additional-link-text'
                ],
                'expectedLinks' => [
                    [
                        'linkUrl' => [
                            'route' => 'licence-print',
                            'params' => [],
                            'options' => [],
                            'reuseMatchedParams' => true
                        ],
                        'linkText' => 'licence.print'
                    ],
                    [

                        'linkUrl' => [
                            'route' => 'additional-route',
                            'params' => [],
                            'options' => [],
                            'reuseMatchedParams' => true
                        ],
                        'linkText' => 'additional-link-text'

                    ]
                ],

            ],
            [
                'additionalLinks' => [],
                'expectedLinks' => [
                    [
                        'linkUrl' => [
                            'route' => 'licence-print',
                            'params' => [],
                            'options' => [],
                            'reuseMatchedParams' => true
                        ],
                        'linkText' => 'licence.print'
                    ]
                ]
            ]
        ];
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('dbSurrenderLink')]
    public function testSetSurrenderLink(array $data): void
    {
        $sut = new LicenceOverview($data['licenceData']);
        $sut->setSurrenderLink($data['surrenderData']);
        $sut->setInfoBoxLinks();
        $this->assertEquals($sut->infoBoxLinks, $data['expected']);
    }

    /**
     * @return (((array|string|true)[]|int|string)[]|bool|int|string)[][][][]
     *
     * @psalm-return array{'no-surrender-data': list{array{licenceData: array{licNo: 1, inForceDate: '2014-01-01', expiryDate: '2015-01-01', status: array{id: 'status'}, isExpired: false, isExpiring: true, showExpiryWarning: 'SHOWEXPIRYWARNING', continuationMarker: array{id: 12345}, isLicenceSurrenderAllowed: true}, surrenderData: array<never, never>, expected: list{array{linkUrl: array{route: 'licence-print', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'licence.print'}, array{linkUrl: array{route: 'licence/surrender/start/GET', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'licence.apply-to-surrender'}}}}, 'surrender-withdrawn': list{array{licenceData: array{licNo: 1, inForceDate: '2014-01-01', expiryDate: '2015-01-01', status: array{id: 'status'}, isExpired: false, isExpiring: true, showExpiryWarning: 'SHOWEXPIRYWARNING', continuationMarker: array{id: 12345}, isLicenceSurrenderAllowed: true}, surrenderData: array{status: array{id: 'surr_sts_withdrawn'}, lastModifiedOn: string}, expected: list{array{linkUrl: array{route: 'licence-print', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'licence.print'}, array{linkUrl: array{route: 'licence/surrender/start/GET', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'licence.apply-to-surrender'}}}}, 'surrender-data-not-expired': list{array{licenceData: array{licNo: 1, inForceDate: '2014-01-01', expiryDate: '2015-01-01', status: array{id: 'status'}, isExpired: false, isExpiring: true, showExpiryWarning: 'SHOWEXPIRYWARNING', continuationMarker: array{id: 12345}, isLicenceSurrenderAllowed: true}, surrenderData: array{status: array{id: 'surr_sts_start'}, lastModifiedOn: string}, expected: list{array{linkUrl: array{route: 'licence-print', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'licence.print'}, array{linkUrl: array{route: 'licence/surrender/information-changed/GET', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'licence.continue-surrender-application'}}}}, 'surrender-data-expired': list{array{licenceData: array{licNo: 1, inForceDate: '2014-01-01', expiryDate: '2015-01-01', status: array{id: 'status'}, isExpired: false, isExpiring: true, showExpiryWarning: 'SHOWEXPIRYWARNING', continuationMarker: array{id: 12345}, isLicenceSurrenderAllowed: true}, surrenderData: array{status: array{id: 'surr_sts_start'}, lastModifiedOn: string}, expected: list{array{linkUrl: array{route: 'licence-print', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'licence.print'}, array{linkUrl: array{route: 'licence/surrender/information-changed/GET', params: array<never, never>, options: array<never, never>, reuseMatchedParams: true}, linkText: 'licence.apply-to-surrender'}}}}}
     */
    public static function dbSurrenderLink(): array
    {
        return [

            'no-surrender-data' => [
                [
                    'licenceData' => [
                        'licNo' => 1,
                        'inForceDate' => '2014-01-01',
                        'expiryDate' => '2015-01-01',
                        'status' => ['id' => 'status'],
                        'isExpired' => false,
                        'isExpiring' => true,
                        'showExpiryWarning' => 'SHOWEXPIRYWARNING',
                        'continuationMarker' => ['id' => 12345],
                        'isLicenceSurrenderAllowed' => true
                    ],
                    'surrenderData' => [],
                    'expected' => [
                        [
                            'linkUrl' => [
                                'route' => 'licence-print',
                                'params' => [],
                                'options' => [],
                                'reuseMatchedParams' => true
                            ],
                            'linkText' => 'licence.print'
                        ],
                        [

                            'linkUrl' => [
                                'route' => 'licence/surrender/start/GET',
                                'params' => [],
                                'options' => [],
                                'reuseMatchedParams' => true
                            ],
                            'linkText' => 'licence.apply-to-surrender'

                        ]
                    ]
                ]
            ],
            'surrender-withdrawn' => [
                [
                    'licenceData' => [
                        'licNo' => 1,
                        'inForceDate' => '2014-01-01',
                        'expiryDate' => '2015-01-01',
                        'status' => ['id' => 'status'],
                        'isExpired' => false,
                        'isExpiring' => true,
                        'showExpiryWarning' => 'SHOWEXPIRYWARNING',
                        'continuationMarker' => ['id' => 12345],
                        'isLicenceSurrenderAllowed' => true
                    ],
                    'surrenderData' => [
                        'status' => ['id' => 'surr_sts_withdrawn'],
                        'lastModifiedOn' => date(DATE_ATOM, time()),
                    ],
                    'expected' => [
                        [
                            'linkUrl' => [
                                'route' => 'licence-print',
                                'params' => [],
                                'options' => [],
                                'reuseMatchedParams' => true
                            ],
                            'linkText' => 'licence.print'
                        ],
                        [
                            'linkUrl' => [
                                'route' => 'licence/surrender/start/GET',
                                'params' => [],
                                'options' => [],
                                'reuseMatchedParams' => true
                            ],
                            'linkText' => 'licence.apply-to-surrender'
                        ]
                    ]
                ]
            ],
            'surrender-data-not-expired' => [
                [
                    'licenceData' => [
                        'licNo' => 1,
                        'inForceDate' => '2014-01-01',
                        'expiryDate' => '2015-01-01',
                        'status' => ['id' => 'status'],
                        'isExpired' => false,
                        'isExpiring' => true,
                        'showExpiryWarning' => 'SHOWEXPIRYWARNING',
                        'continuationMarker' => ['id' => 12345],
                        'isLicenceSurrenderAllowed' => true
                    ],
                    'surrenderData' => [
                        'status' => ['id' => 'surr_sts_start'],
                        'lastModifiedOn' => date(DATE_ATOM, time()),
                    ],
                    'expected' => [
                        [
                            'linkUrl' => [
                                'route' => 'licence-print',
                                'params' => [],
                                'options' => [],
                                'reuseMatchedParams' => true
                            ],
                            'linkText' => 'licence.print'
                        ],
                        [

                            'linkUrl' => [
                                'route' => 'licence/surrender/information-changed/GET',
                                'params' => [],
                                'options' => [],
                                'reuseMatchedParams' => true
                            ],
                            'linkText' => 'licence.continue-surrender-application'

                        ]
                    ]
                ]
            ],
            'surrender-data-expired' => [
                [
                    'licenceData' => [
                        'licNo' => 1,
                        'inForceDate' => '2014-01-01',
                        'expiryDate' => '2015-01-01',
                        'status' => ['id' => 'status'],
                        'isExpired' => false,
                        'isExpiring' => true,
                        'showExpiryWarning' => 'SHOWEXPIRYWARNING',
                        'continuationMarker' => ['id' => 12345],
                        'isLicenceSurrenderAllowed' => true
                    ],
                    'surrenderData' => [
                        'status' => ['id' => 'surr_sts_start'],
                        'lastModifiedOn' => date(DATE_ATOM, time() - (3 * 24 * 60 * 60)),
                    ],
                    'expected' => [
                        [
                            'linkUrl' => [
                                'route' => 'licence-print',
                                'params' => [],
                                'options' => [],
                                'reuseMatchedParams' => true
                            ],
                            'linkText' => 'licence.print'
                        ],
                        [

                            'linkUrl' => [
                                'route' => 'licence/surrender/information-changed/GET',
                                'params' => [],
                                'options' => [],
                                'reuseMatchedParams' => true
                            ],
                            'linkText' => 'licence.apply-to-surrender'
                        ]
                    ]
                ]
            ]
        ];
    }
}
