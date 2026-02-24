<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\ConditionsUndertakingsReviewService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Conditions & undertakings review service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ConditionsUndertakingsReviewServiceTest extends MockeryTestCase
{
    /** @var VehiclesReviewService review service */
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new ConditionsUndertakingsReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('licenceProvider')]
    public function testGetConfigFromData(mixed $isPsv, mixed $isRestricted, mixed $variables): void
    {
        $continuationDetail = new ContinuationDetail();

        $mockLicence = m::mock(Licence::class);
        $mockLicence
            ->shouldReceive('getGroupedConditionsUndertakings')
            ->andReturn(['foo'])
            ->once();
        $mockLicence
            ->shouldReceive('isPsv')
            ->andReturn($isPsv)
            ->once();
        $mockLicence
            ->shouldReceive('isRestricted')
            ->andReturn($isRestricted);

        $continuationDetail->setLicence($mockLicence);

        $expected = [
            'mainItems' => [
                [
                    'partial' => 'continuation-conditions-undertakings',
                    'variables' => $variables
                ],
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($continuationDetail));
    }

    public static function licenceProvider(): array
    {
        return [
            'isPsvRestricted' => [
                'isPsv' => true,
                'isRestricted' => true,
                'variables' => [
                    'isPsvRestricted' => true,
                    'conditionsUndertakings' => ['foo']
                ]
            ],
            'isPsvNotRestricted' => [
                'isPsv' => true,
                'isRestricted' => false,
                'variables' => [
                    'isPsvRestricted' => false,
                    'conditionsUndertakings' => ['foo']
                ]
            ],
            'isNotPsvIsRestricted' => [
                'isPsv' => true,
                'isRestricted' => false,
                'variables' => [
                    'isPsvRestricted' => false,
                    'conditionsUndertakings' => ['foo']
                ]
            ],
            'isNotPsvIsNotRestricted' => [
                'isPsv' => true,
                'isRestricted' => false,
                'variables' => [
                    'isPsvRestricted' => false,
                    'conditionsUndertakings' => ['foo']
                ]
            ]

        ];
    }
}
