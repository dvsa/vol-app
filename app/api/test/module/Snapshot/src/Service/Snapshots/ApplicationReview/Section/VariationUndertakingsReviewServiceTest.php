<?php

declare(strict_types=1);

/**
 * Variation Undertakings Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationUndertakingsReviewService;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Undertakings Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationUndertakingsReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->sut = new VariationUndertakingsReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providerGetConfigFromData')]
    public function testGetConfigFromData(mixed $data, mixed $expected): void
    {
        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_PSV430')
            ->andReturn('PSV430-translated [%s] [%s] [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_PSV430-Standard')
            ->andReturn('PSV430-standard-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_PSV430-declare')
            ->andReturn('PSV430-declare-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_signature')
            ->andReturn('signature-translated [%s] [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_signature_address_gb')
            ->andReturn('signature-address-gb-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_signature_address_ni')
            ->andReturn('signature-address-ni-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('undertakings_directors_signature')
            ->andReturn('undertakings-directors-signature-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV80A')
            ->andReturn('GV80A-translated [%s] [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV80A-NI')
            ->andReturn('GV80A-NI-translated [%s] [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV80A-declare')
            ->andReturn('GV80A-declare-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV80A-NI-declare')
            ->andReturn('GV80A-NI-declare-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV81')
            ->andReturn('GV81-translated [%s] [%s] [%s] [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV81-NI')
            ->andReturn('GV81-NI-translated [%s] [%s] [%s] [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV81-auth-lgv')
            ->andReturn('GV81-auth-lgv-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV81-auth-other')
            ->andReturn('GV81-auth-other-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV81-NI-auth-other')
            ->andReturn('GV81-NI-auth-other-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV81-Standard')
            ->andReturn('GV81-standard-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV81-declare')
            ->andReturn('GV81-declare-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV81-NI-Standard')
            ->andReturn('GV81-NI-standard-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV81-NI-declare')
            ->andReturn('GV81-NI-declare-translated');

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public static function providerGetConfigFromData(): array
    {
        return [
            'psv, special restricted, not internal' => [
                [
                    'isGoods' => false,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_SPECIAL_RESTRICTED
                    ],
                    'isInternal' => false,
                ],
                [
                    'markup' => 'PSV430-translated [] [] []'
                ]
            ],
            'psv, restricted, not internal' => [
                [
                    'isGoods' => false,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_RESTRICTED
                    ],
                    'isInternal' => false,
                ],
                [
                    'markup' => 'PSV430-translated [] [] []'
                ]
            ],
            'psv, standard national, not internal' => [
                [
                    'isGoods' => false,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'isInternal' => false,
                ],
                [
                    'markup' => 'PSV430-translated [] [] [PSV430-standard-translated]'
                ]
            ],
            'psv, standard international, not internal' => [
                [
                    'isGoods' => false,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'isInternal' => false,
                ],
                [
                    'markup' => 'PSV430-translated [] [] [PSV430-standard-translated]'
                ]
            ],
            'psv, special restricted, internal' => [
                [
                    'isGoods' => false,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_SPECIAL_RESTRICTED
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'PSV430-translated [PSV430-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-gb-translated]] []'
                ]
            ],
            'psv, restricted, internal' => [
                [
                    'isGoods' => false,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_RESTRICTED
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'PSV430-translated [PSV430-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-gb-translated]] []'
                ]
            ],
            'psv, standard national, internal' => [
                [
                    'isGoods' => false,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'PSV430-translated [PSV430-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-gb-translated]]' .
                        ' [PSV430-standard-translated]'
                ]
            ],
            'psv, standard international, internal' => [
                [
                    'isGoods' => false,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'PSV430-translated [PSV430-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-gb-translated]]' .
                        ' [PSV430-standard-translated]'
                ]
            ],
            'upgrade from restricted to standard national, not internal, not ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'isInternal' => false,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_RESTRICTED
                        ],
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'GV80A-translated [] []',
                ]
            ],
            'upgrade from restricted to standard international, not internal, not ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'isInternal' => false,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_RESTRICTED
                        ],
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'GV80A-translated [] []',
                ]
            ],
            'upgrade from restricted to standard national, not internal, ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'isInternal' => false,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_RESTRICTED
                        ],
                    ],
                    'niFlag' => 'Y'
                ],
                [
                    'markup' => 'GV80A-NI-translated [] []',
                ]
            ],
            'upgrade from restricted to standard international, not internal, ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'isInternal' => false,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_RESTRICTED
                        ],
                    ],
                    'niFlag' => 'Y'
                ],
                [
                    'markup' => 'GV80A-NI-translated [] []',
                ]
            ],
            'upgrade from restricted to standard national, internal, not ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_RESTRICTED
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'GV80A-translated [GV80A-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-gb-translated]]',
                ]
            ],
            'upgrade from restricted to standard international, internal, not ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_RESTRICTED
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'GV80A-translated [GV80A-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-gb-translated]]',
                ]
            ],
            'upgrade from restricted to standard national, internal, ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_RESTRICTED
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'Y'
                ],
                [
                    'markup' => 'GV80A-NI-translated [GV80A-NI-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-ni-translated]]',
                ]
            ],
            'upgrade from restricted to standard international, internal, ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_RESTRICTED
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'Y'
                ],
                [
                    'markup' => 'GV80A-NI-translated [GV80A-NI-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-ni-translated]]',
                ]
            ],
            'goods, restricted, hgv, not internal, not ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_RESTRICTED
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_HGV
                    ],
                    'isInternal' => false,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_RESTRICTED
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'GV81-translated [] [] [GV81-auth-other-translated] []'
                ]
            ],
            'goods, standard national, hgv, not internal, not ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_HGV
                    ],
                    'isInternal' => false,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'GV81-translated [] [] [GV81-auth-other-translated] [GV81-standard-translated]'
                ]
            ],
            'goods, standard international, mixed, not internal, not ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED
                    ],
                    'isInternal' => false,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'GV81-translated [] [] [GV81-auth-other-translated] [GV81-standard-translated]'
                ]
            ],
            'goods, standard international, lgv, not internal, not ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_LGV
                    ],
                    'isInternal' => false,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'GV81-translated [] [] [GV81-auth-lgv-translated] [GV81-standard-translated]'
                ]
            ],
            'goods, restricted, hgv, internal, not ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_RESTRICTED
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_HGV
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_RESTRICTED
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'GV81-translated [GV81-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-gb-translated]]' .
                        ' [GV81-auth-other-translated] []'
                ]
            ],
            'goods, standard national, hgv, internal, not ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_HGV
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'GV81-translated [GV81-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-gb-translated]]' .
                        ' [GV81-auth-other-translated] [GV81-standard-translated]'
                ]
            ],
            'goods, standard international, mixed, internal, not ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'GV81-translated [GV81-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-gb-translated]]' .
                        ' [GV81-auth-other-translated] [GV81-standard-translated]'
                ]
            ],
            'goods, standard international, lgv, internal, not ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_LGV
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'N'
                ],
                [
                    'markup' => 'GV81-translated [GV81-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-gb-translated]]' .
                        ' [GV81-auth-lgv-translated] [GV81-standard-translated]'
                ]
            ],
            'goods, restricted, hgv, not internal, ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_RESTRICTED
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_HGV
                    ],
                    'isInternal' => false,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_RESTRICTED
                        ]
                    ],
                    'niFlag' => 'Y'
                ],
                [
                    'markup' => 'GV81-NI-translated [] [] [GV81-NI-auth-other-translated] []'
                ]
            ],
            'goods, standard national, hgv, not internal, ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_HGV
                    ],
                    'isInternal' => false,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                        ]
                    ],
                    'niFlag' => 'Y'
                ],
                [
                    'markup' => 'GV81-NI-translated [] [] [GV81-NI-auth-other-translated] [GV81-NI-standard-translated]'
                ]
            ],
            'goods, standard international, mixed, not internal, ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED
                    ],
                    'isInternal' => false,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                        ]
                    ],
                    'niFlag' => 'Y'
                ],
                [
                    'markup' => 'GV81-NI-translated [] [] [GV81-NI-auth-other-translated] [GV81-NI-standard-translated]'
                ]
            ],
            'goods, standard international, lgv, not internal, ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_LGV
                    ],
                    'isInternal' => false,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                        ]
                    ],
                    'niFlag' => 'Y'
                ],
                [
                    'markup' => 'GV81-NI-translated [] [] [GV81-auth-lgv-translated] [GV81-NI-standard-translated]'
                ]
            ],
            'goods, restricted, hgv, internal, ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_RESTRICTED
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_HGV
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_RESTRICTED
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'Y'
                ],
                [
                    'markup' => 'GV81-NI-translated [GV81-NI-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-ni-translated]]' .
                        ' [GV81-NI-auth-other-translated] []'
                ]
            ],
            'goods, standard national, hgv, internal, ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_HGV
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'Y'
                ],
                [
                    'markup' => 'GV81-NI-translated [GV81-NI-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-ni-translated]]' .
                        ' [GV81-NI-auth-other-translated] [GV81-NI-standard-translated]'
                ]
            ],
            'goods, standard international, mixed, internal, ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'Y'
                ],
                [
                    'markup' => 'GV81-NI-translated [GV81-NI-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-ni-translated]]' .
                        ' [GV81-NI-auth-other-translated] [GV81-NI-standard-translated]'
                ]
            ],
            'goods, standard international, lgv, internal, ni' => [
                [
                    'isGoods' => true,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_LGV
                    ],
                    'isInternal' => true,
                    'licence' => [
                        'licenceType' => [
                            'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_rc'
                            ]
                        ]
                    ],
                    'niFlag' => 'Y'
                ],
                [
                    'markup' => 'GV81-NI-translated [GV81-NI-declare-translated] [signature-translated' .
                        ' [undertakings-directors-signature-translated] [signature-address-ni-translated]]' .
                        ' [GV81-auth-lgv-translated] [GV81-NI-standard-translated]'
                ]
            ],
        ];
    }
}
