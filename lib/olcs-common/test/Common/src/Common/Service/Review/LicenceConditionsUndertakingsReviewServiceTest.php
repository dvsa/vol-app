<?php

/**
 * Licence Conditions Undertakings Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Review;

use Common\Service\Helper\DataHelperService;
use Common\Service\Table\Formatter\Address;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Review\AbstractReviewServiceServices;
use Common\Service\Review\ConditionsUndertakingsReviewService;
use Common\Service\Review\LicenceConditionsUndertakingsReviewService;

/**
 * Licence Conditions Undertakings Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceConditionsUndertakingsReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var TranslationHelperService */
    protected $mockTranslationHelper;

    /** @var ConditionsUndertakingsReviewService */
    protected $mockConditionsUndertakings;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockTranslationHelper = m::mock(TranslationHelperService::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslationHelper')
            ->withNoArgs()
            ->andReturn($this->mockTranslationHelper);

        $this->mockConditionsUndertakings = m::mock(ConditionsUndertakingsReviewService::class);

        $this->sut = new LicenceConditionsUndertakingsReviewService(
            $abstractReviewServiceServices,
            $this->mockConditionsUndertakings,
            new Address(new DataHelperService())
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
                ['BAR1'],
                ['BAR2'],
                ['BAR3'],
                ['BAR4'],
            ]
        ];

        // Expectations
        $this->mockConditionsUndertakings->shouldReceive('splitUpConditionsAndUndertakings')
            ->with($inputData, false)
            ->andReturn($data)
            ->shouldReceive('formatLicenceSubSection')
            ->with(['foo' => 'bar1'], 'application', 'conditions', 'added')
            ->andReturn(['BAR1'])
            ->shouldReceive('formatLicenceSubSection')
            ->with(['foo' => 'bar2'], 'application', 'undertakings', 'added')
            ->andReturn(['BAR2'])
            ->shouldReceive('formatOcSubSection')
            ->with(['foo' => 'bar3'], 'application', 'conditions', 'added')
            ->andReturn(['BAR3'])
            ->shouldReceive('formatOcSubSection')
            ->with(['foo' => 'bar4'], 'application', 'undertakings', 'added')
            ->andReturn(['BAR4']);

        $this->mockTranslationHelper->shouldReceive('translate')
            ->andReturnUsing(
                static fn($string) => $string . '-translated'
            );

        $this->assertEquals($expected, $this->sut->getConfigFromData($inputData));
    }
}
