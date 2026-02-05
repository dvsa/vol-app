<?php

declare(strict_types=1);

/**
 * Variation Business Details Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationBusinessDetailsReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Business Details Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessDetailsReviewServiceTest extends MockeryTestCase
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

        $this->sut = new VariationBusinessDetailsReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $data = [];

        $this->mockTranslator->shouldReceive('translate')
            ->with('variation-review-business-details-change')
            ->andReturn('variation-review-business-details-change-translated');

        $expected = [
            'freetext' => 'variation-review-business-details-change-translated'
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }
}
