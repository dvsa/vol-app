<?php

declare(strict_types=1);

/**
 * Variation Safety Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationSafetyReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Safety Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationSafetyReviewServiceTest extends MockeryTestCase
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

        $this->sut = new VariationSafetyReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $data = [];

        $this->mockTranslator->shouldReceive('translate')
            ->with('variation-review-safety-change')
            ->andReturn('variation-review-safety-change-translated');

        $expected = [
            'freetext' => 'variation-review-safety-change-translated'
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }
}
