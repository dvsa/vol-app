<?php

declare(strict_types=1);

/**
 * Licence Conditions Undertakings Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ConditionsUndertakingsReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\LicenceConditionsUndertakingsReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Licence Conditions Undertakings Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceConditionsUndertakingsReviewServiceTest extends MockeryTestCase
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

        $this->sut = new LicenceConditionsUndertakingsReviewService(
            $abstractReviewServiceServices,
            $this->mockConditionsUndertakings
        );
    }

    public function testGetConfigFromData(): void
    {
        // Params
        $data = [
            [
                'list' => [
                    'foo' => 'bar1'
                ]
            ],
            [
                'list' => [
                    'foo' => 'bar2'
                ]
            ],
            [
                'list' => [
                    'foo' => 'bar3'
                ]
            ],
            [
                'list' => [
                    'foo' => 'bar4'
                ]
            ]
        ];
        $inputData = ['foo' => 'bar']; // Doesn't matter what this is
        $expected = [
            'subSections' => [
                'BAR1',
                'BAR2',
                'BAR3',
                'BAR4'
            ]
        ];

        // Expectations
        $this->mockConditionsUndertakings->shouldReceive('splitUpConditionsAndUndertakings')
            ->with($inputData, false)
            ->andReturn($data)
            ->shouldReceive('formatLicenceSubSection')
            ->with(['foo' => 'bar1'], 'application', 'conditions', 'added')
            ->andReturn('BAR1')
            ->shouldReceive('formatLicenceSubSection')
            ->with(['foo' => 'bar2'], 'application', 'undertakings', 'added')
            ->andReturn('BAR2')
            ->shouldReceive('formatOcSubSection')
            ->with(['foo' => 'bar3'], 'application', 'conditions', 'added')
            ->andReturn('BAR3')
            ->shouldReceive('formatOcSubSection')
            ->with(['foo' => 'bar4'], 'application', 'undertakings', 'added')
            ->andReturn('BAR4');

        $this->mockTranslator->shouldReceive('translate')
            ->andReturnUsing(
                fn($string) => $string . '-translated'
            );

        $this->assertEquals($expected, $this->sut->getConfigFromData($inputData));
    }
}
