<?php

declare(strict_types=1);

/**
 * Variation Addresses Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationAddressesReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Addresses Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationAddressesReviewServiceTest extends MockeryTestCase
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

        $this->sut = new VariationAddressesReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $data = [];

        $this->mockTranslator->shouldReceive('translate')
            ->with('variation-review-addresses-change')
            ->andReturn('variation-review-addresses-change-translated');

        $expected = [
            'freetext' => 'variation-review-addresses-change-translated'
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }
}
