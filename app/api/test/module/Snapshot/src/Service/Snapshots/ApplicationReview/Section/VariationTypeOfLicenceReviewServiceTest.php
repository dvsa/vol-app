<?php

declare(strict_types=1);

/**
 * Variation Type Of Licence Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationTypeOfLicenceReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Type Of Licence Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationTypeOfLicenceReviewServiceTest extends MockeryTestCase
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

        $this->sut = new VariationTypeOfLicenceReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetConfigFromData')]
    public function testGetConfigFromData(mixed $licenceLicenceTypeId, mixed $applicationLicenceTypeId): void
    {
        $data = [
            'licenceType' => [
                'id' => $applicationLicenceTypeId,
                'description' => 'foo'
            ],
            'licence' => [
                'licenceType' => [
                    'id' => $licenceLicenceTypeId,
                    'description' => 'bar'
                ]
            ]
        ];

        $this->mockTranslator->shouldReceive('translate')
            ->with('variation-application-type-of-licence-freetext')
            ->andReturn('translated-text-%s-%s');

        $this->assertEquals(['freetext' => 'translated-text-bar-foo'], $this->sut->getConfigFromData($data));
    }

    public static function dpGetConfigFromData(): array
    {
        return [
            [
                Licence::LICENCE_TYPE_RESTRICTED,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            ],
            [
                Licence::LICENCE_TYPE_RESTRICTED,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            ],
            [
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Licence::LICENCE_TYPE_RESTRICTED,
            ],
            [
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            ],
            [
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            ],
            [
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Licence::LICENCE_TYPE_RESTRICTED,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetConfigFromDataStandardInternational')]
    public function testConfigFromDataStandardInternational(
        mixed $licenceVehicleTypeId,
        mixed $applicationVehicleTypeId,
        mixed $expectedTranslatedValue
    ): void {
        $data = [
            'licenceType' => [
                'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            ],
            'vehicleType' => [
                'id' => $applicationVehicleTypeId
            ],
            'licence' => [
                'licenceType' => [
                    'id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
                ],
                'vehicleType' => [
                    'id' => $licenceVehicleTypeId
                ]
            ]
        ];

        $this->mockTranslator->shouldReceive('translate')
            ->with('variation-application-type-of-licence-std-int-value-template')
            ->andReturn('translated-template %s %s');

        $this->mockTranslator->shouldReceive('translate')
            ->with('variation-application-type-of-licence-std-int-value-app_veh_type_mixed')
            ->andReturn('translated-mixed');

        $this->mockTranslator->shouldReceive('translate')
            ->with('variation-application-type-of-licence-std-int-value-app_veh_type_lgv')
            ->andReturn('translated-lgv');

        $expectedConfigFromData = [
            'multiItems' => [
                [
                    [
                        'label' => 'variation-application-type-of-licence-std-int-caption',
                        'value' => $expectedTranslatedValue,
                    ],
                ]
            ]
        ];

        $this->assertEquals(
            $expectedConfigFromData,
            $this->sut->getConfigFromData($data)
        );
    }

    public static function dpGetConfigFromDataStandardInternational(): array
    {
        return [
            [
                RefData::APP_VEHICLE_TYPE_MIXED,
                RefData::APP_VEHICLE_TYPE_LGV,
                'translated-template translated-mixed translated-lgv'
            ],
            [
                RefData::APP_VEHICLE_TYPE_LGV,
                RefData::APP_VEHICLE_TYPE_MIXED,
                'translated-template translated-lgv translated-mixed'
            ],
        ];
    }
}
