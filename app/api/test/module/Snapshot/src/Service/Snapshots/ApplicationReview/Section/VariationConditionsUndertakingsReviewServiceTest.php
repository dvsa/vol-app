<?php

declare(strict_types=1);

/**
 * Variation Conditions Undertakings Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ConditionsUndertakingsReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationConditionsUndertakingsReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Conditions Undertakings Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationConditionsUndertakingsReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    /** @var ConditionsUndertakingsReviewService */
    protected $mockConditionsUndertakings;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->mockConditionsUndertakings = m::mock(ConditionsUndertakingsReviewService::class);

        $this->sut = new VariationConditionsUndertakingsReviewService(
            $abstractReviewServiceServices,
            $this->mockConditionsUndertakings
        );
    }

    public function testGetConfigFromDataWithNoneAdded(): void
    {
        // Params
        $data = [
            [],
            [],
            [],
            []
        ];
        $inputData = ['foo' => 'bar']; // Doesn't matter what this is
        $expected = [
            'freetext' => 'review-none-added-translated'
        ];

        // Expectations
        $this->mockConditionsUndertakings->shouldReceive('splitUpConditionsAndUndertakings')
            ->with($inputData)
            ->andReturn($data);

        $this->mockTranslator->shouldReceive('translate')
            ->andReturnUsing(
                fn($string) => $string . '-translated'
            );

        $this->assertEquals($expected, $this->sut->getConfigFromData($inputData));
    }

    public function testGetConfigFromData(): void
    {
        // Params
        $data = [
            [
                'A' => [
                    'foo' => 'bar1'
                ],
                'U' => [
                    'foo1' => 'bar1'
                ],
                'D' => [
                    'foo2' => 'bar1'
                ]
            ],
            [
                'A' => [
                    'foo' => 'bar2'
                ],
                'U' => [
                    'foo1' => 'bar2'
                ],
                'D' => [
                    'foo2' => 'bar2'
                ]
            ],
            [
                'A' => [
                    'foo' => 'bar3'
                ],
                'U' => [
                    'foo1' => 'bar3'
                ],
                'D' => [
                    'foo2' => 'bar3'
                ]
            ],
            [
                'A' => [
                    'foo' => 'bar4'
                ],
                'U' => [
                    'foo1' => 'bar4'
                ],
                'D' => [
                    'foo2' => 'bar4'
                ]
            ]
        ];
        $inputData = ['foo' => 'bar']; // Doesn't matter what this is
        $expected = [
            'subSections' => [
                'BAR1',
                '1BAR1',
                '2BAR1',
                'BAR2',
                '1BAR2',
                '2BAR2',
                'BAR3',
                '1BAR3',
                '2BAR3',
                'BAR4',
                '1BAR4',
                '2BAR4'
            ]
        ];

        // Expectations
        $this->mockConditionsUndertakings->shouldReceive('splitUpConditionsAndUndertakings')
            ->with($inputData)
            ->andReturn($data)
            ->shouldReceive('formatLicenceSubSection')
            ->with(['foo' => 'bar1'], 'variation', 'conditions', 'added')
            ->andReturn('BAR1')
            ->shouldReceive('formatLicenceSubSection')
            ->with(['foo1' => 'bar1'], 'variation', 'conditions', 'updated')
            ->andReturn('1BAR1')
            ->shouldReceive('formatLicenceSubSection')
            ->with(['foo2' => 'bar1'], 'variation', 'conditions', 'deleted')
            ->andReturn('2BAR1')
            ->shouldReceive('formatLicenceSubSection')
            ->with(['foo' => 'bar2'], 'variation', 'undertakings', 'added')
            ->andReturn('BAR2')
            ->shouldReceive('formatLicenceSubSection')
            ->with(['foo1' => 'bar2'], 'variation', 'undertakings', 'updated')
            ->andReturn('1BAR2')
            ->shouldReceive('formatLicenceSubSection')
            ->with(['foo2' => 'bar2'], 'variation', 'undertakings', 'deleted')
            ->andReturn('2BAR2')
            ->shouldReceive('formatOcSubSection')
            ->with(['foo' => 'bar3'], 'variation', 'conditions', 'added')
            ->andReturn('BAR3')
            ->shouldReceive('formatOcSubSection')
            ->with(['foo1' => 'bar3'], 'variation', 'conditions', 'updated')
            ->andReturn('1BAR3')
            ->shouldReceive('formatOcSubSection')
            ->with(['foo2' => 'bar3'], 'variation', 'conditions', 'deleted')
            ->andReturn('2BAR3')
            ->shouldReceive('formatOcSubSection')
            ->with(['foo' => 'bar4'], 'variation', 'undertakings', 'added')
            ->andReturn('BAR4')
            ->shouldReceive('formatOcSubSection')
            ->with(['foo1' => 'bar4'], 'variation', 'undertakings', 'updated')
            ->andReturn('1BAR4')
            ->shouldReceive('formatOcSubSection')
            ->with(['foo2' => 'bar4'], 'variation', 'undertakings', 'deleted')
            ->andReturn('2BAR4');

        $this->mockTranslator->shouldReceive('translate')
            ->andReturnUsing(
                fn($string) => $string . '-translated'
            );

        $this->assertEquals($expected, $this->sut->getConfigFromData($inputData));
    }
}
