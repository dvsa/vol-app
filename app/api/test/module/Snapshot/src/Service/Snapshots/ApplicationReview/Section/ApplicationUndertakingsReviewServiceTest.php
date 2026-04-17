<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationUndertakingsReviewService;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Undertakings Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationUndertakingsReviewServiceTest extends MockeryTestCase
{
    /** @var ApplicationUndertakingsReviewService */
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

        $this->sut = new ApplicationUndertakingsReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providerGetConfigFromData')]
    public function testGetConfigFromData(mixed $data, mixed $expected): void
    {
        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_PSV356')
            ->andReturn('PSV356-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_PSV421')
            ->andReturn('PSV421-translated [%s] [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_PSV421-Standard')
            ->andReturn('PSV421-standard-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_PSV421-declare')
            ->andReturn('PSV421-declare-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV79-auth-lgv')
            ->andReturn('GV79-auth-lgv-translated [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV79-auth-other')
            ->andReturn('GV79-auth-other-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV79-NI-auth-other')
            ->andReturn('GV79-NI-auth-other-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV79')
            ->andReturn('GV79-translated [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV79-Standard')
            ->andReturn('GV79-standard-translated [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV79-declare')
            ->andReturn('GV79-declare-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV79-NI')
            ->andReturn('GV79-NI-translated [%s] [%s] [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV79-NI-Standard')
            ->andReturn('GV79-NI-standard-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV79-NI-declare')
            ->andReturn('GV79-NI-declare-translated');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV79-auth-restricted')
            ->andReturn('GV79-auth-restricted-translated [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV79-si')
            ->andReturn('GV79-si-translated [%s]');

        $this->mockTranslator->shouldReceive('translate')
            ->with('markup-application_undertakings_GV79-auth-lgv-NI')
            ->andReturn('GV79-auth-lgv-NI-translated');

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public static function providerGetConfigFromData(): array
    {
        return [
            'psv, special restricted' => [
                [
                    'isGoods' => false,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_SPECIAL_RESTRICTED
                    ]
                ],
                [
                    'markup' => 'PSV356-translated',
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
                    'markup' => 'PSV421-translated [] []',
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
                    'markup' => 'PSV421-translated [PSV421-standard-translated] []',
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
                    'markup' => 'PSV421-translated [PSV421-standard-translated] []',
                ]
            ],
            'psv, restricted, internal' => [
                [
                    'isGoods' => false,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_RESTRICTED
                    ],
                    'isInternal' => true,
                ],
                [
                    'markup' => 'PSV421-translated [] [PSV421-declare-translated]',
                ]
            ],
            'psv, standard national, internal' => [
                [
                    'isGoods' => false,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'isInternal' => true,
                ],
                [
                    'markup' => 'PSV421-translated [PSV421-standard-translated] [PSV421-declare-translated]',
                ]
            ],
            'psv, standard international, internal' => [
                [
                    'isGoods' => false,
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                    ],
                    'isInternal' => true,
                ],
                [
                    'markup' => 'PSV421-translated [PSV421-standard-translated] [PSV421-declare-translated]',
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
                    'niFlag' => false,
                ],
                [
                    'markup' => 'GV79-auth-restricted-translated []',
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
                    'niFlag' => false,
                ],
                [
                    'markup' => 'GV79-translated []',
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
                    'niFlag' => false,
                ],
                [
                    'markup' => 'GV79-si-translated []',
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
                    'niFlag' => false,
                ],
                [
                    'markup' => 'GV79-auth-lgv-translated []',
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
                    'niFlag' => false,
                ],
                [
                    'markup' => 'GV79-auth-restricted-translated [GV79-declare-translated]',
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
                    'niFlag' => false,
                ],
                [
                    'markup' => 'GV79-translated [GV79-declare-translated]',
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
                    'niFlag' => false,
                ],
                [
                    'markup' => 'GV79-si-translated [GV79-declare-translated]',
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
                    'niFlag' => false,
                ],
                [
                    'markup' => 'GV79-auth-lgv-translated [GV79-declare-translated]',
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
                    'niFlag' => true,
                ],
                [
                    'markup' => 'GV79-NI-translated [GV79-NI-auth-other-translated] [] []',
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
                    'niFlag' => true,
                ],
                [
                    'markup' => 'GV79-NI-translated [GV79-NI-auth-other-translated] [GV79-NI-standard-translated] []',
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
                    'niFlag' => true,
                ],
                [
                    'markup' => 'GV79-NI-translated [GV79-NI-auth-other-translated] [GV79-NI-standard-translated] []',
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
                    'niFlag' => true,
                ],
                [
                    'markup' => 'GV79-NI-translated [GV79-auth-lgv-NI-translated] [GV79-NI-standard-translated] []',
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
                    'niFlag' => true,
                ],
                [
                    'markup' => 'GV79-NI-translated [GV79-NI-auth-other-translated] [] [GV79-NI-declare-translated]',
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
                    'niFlag' => true,
                ],
                [
                    'markup' => 'GV79-NI-translated [GV79-NI-auth-other-translated] [GV79-NI-standard-translated] ' .
                        '[GV79-NI-declare-translated]',
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
                    'niFlag' => true,
                ],
                [
                    'markup' => 'GV79-NI-translated [GV79-NI-auth-other-translated] [GV79-NI-standard-translated] ' .
                        '[GV79-NI-declare-translated]',
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
                    'niFlag' => true,
                ],
                [
                    'markup' => 'GV79-NI-translated [GV79-auth-lgv-NI-translated] [GV79-NI-standard-translated] [GV79-NI-declare-translated]',
                ]
            ],
        ];
    }
}
