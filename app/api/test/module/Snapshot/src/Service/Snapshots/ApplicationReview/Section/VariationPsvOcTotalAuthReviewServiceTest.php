<?php

declare(strict_types=1);

/**
 * Variation Psv Oc Total Auth Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationPsvOcTotalAuthReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Psv Oc Total Auth Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationPsvOcTotalAuthReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);
        $mockTranslator->shouldReceive('translate')
            ->with('review-value-decreased')
            ->andReturn('decreased from %s to %s')
            ->shouldReceive('translate')
            ->with('review-value-increased')
            ->andReturn('increased from %s to %s');

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new VariationPsvOcTotalAuthReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetConfigFromData')]
    public function testGetConfigFromData(mixed $data, mixed $expected): void
    {
        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public static function dpGetConfigFromData(): array
    {
        return [
            'without changes' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL],
                    'totAuthVehicles' => 10,
                    'licence' => [
                        'totAuthVehicles' => 10,
                    ]
                ],
                'expected' => null,
            ],
            'with changes' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL],
                    'totAuthVehicles' => 10,
                    'licence' => [
                        'totAuthVehicles' => 20,
                    ]
                ],
                'expected' => [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles',
                                'value' => 'decreased from 20 to 10'
                            ]
                        ]
                    ]
                ],
            ],
            'with changes - standard international' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL],
                    'totAuthVehicles' => 10,
                    'totCommunityLicences' => 5,
                    'licence' => [
                        'totAuthVehicles' => 20,
                        'totCommunityLicences' => 1,
                        ]
                ],
                'expected' => [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles',
                                'value' => 'decreased from 20 to 10'
                            ],
                            [
                                'label' => 'review-operating-centres-authorisation-community-licences',
                                'value' => 'increased from 1 to 5'
                            ]
                        ]
                    ]
                ],
            ],
            'with changes - restricted' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_RESTRICTED],
                    'totAuthVehicles' => 10,
                    'totCommunityLicences' => 5,
                    'licence' => [
                        'totAuthVehicles' => 20,
                        'totCommunityLicences' => 1,
                        ]
                ],
                'expected' => [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles',
                                'value' => 'decreased from 20 to 10'
                            ],
                            [
                                'label' => 'review-operating-centres-authorisation-community-licences',
                                'value' => 'increased from 1 to 5'
                            ]
                        ]
                    ]
                ],
            ],
            'with changes to zero' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL],
                    'totAuthVehicles' => 0,
                    'licence' => [
                        'totAuthVehicles' => 20,
                    ]
                ],
                'expected' => [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles',
                                'value' => 'decreased from 20 to 0'
                            ]
                        ]
                    ]
                ],
            ],
            'with changes to zero - standard international' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL],
                    'totAuthVehicles' => 0,
                    'totCommunityLicences' => 0,
                    'licence' => [
                        'totAuthVehicles' => 20,
                        'totCommunityLicences' => 1,
                        ]
                ],
                'expected' => [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles',
                                'value' => 'decreased from 20 to 0'
                            ],
                            [
                                'label' => 'review-operating-centres-authorisation-community-licences',
                                'value' => 'decreased from 1 to 0'
                            ]
                        ]
                    ]
                ],
            ],
        ];
    }
}
