<?php

declare(strict_types=1);

/**
 * Application Psv Oc Total Auth Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationPsvOcTotalAuthReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Psv Oc Total Auth Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationPsvOcTotalAuthReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new ApplicationPsvOcTotalAuthReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('licenceTypeProvider')]
    public function testGetConfigFromData(mixed $licenceType, mixed $expected): void
    {
        $data = [
            'licenceType' => ['id' => $licenceType],
            'totAuthVehicles' => 60,
            'totCommunityLicences' => 50
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public static function licenceTypeProvider(): array
    {
        return [
            'standard national' => [
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles',
                                'value' => 60
                            ]
                        ]
                    ]
                ]
            ],
            'standard international' => [
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles',
                                'value' => 60
                            ],
                            [
                                'label' => 'review-operating-centres-authorisation-community-licences.psv',
                                'value' => 50
                            ]
                        ]
                    ]
                ]
            ],
            'restricted' => [
                Licence::LICENCE_TYPE_RESTRICTED,
                [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles',
                                'value' => 60
                            ],
                            [
                                'label' => 'review-operating-centres-authorisation-community-licences.psv',
                                'value' => 50
                            ]
                        ]
                    ]
                ]
            ],
            'special restricted' => [
                Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
                [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles',
                                'value' => 60
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
