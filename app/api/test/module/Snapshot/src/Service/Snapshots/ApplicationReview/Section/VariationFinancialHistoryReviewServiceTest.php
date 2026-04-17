<?php

declare(strict_types=1);

/**
 * Variation Financial History Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationFinancialHistoryReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationFinancialHistoryReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Financial History Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationFinancialHistoryReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var ApplicationFinancialHistoryReviewService */
    protected $mockApplicationService;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->mockApplicationService = m::mock(ApplicationFinancialHistoryReviewService::class);

        $this->sut = new VariationFinancialHistoryReviewService(
            $abstractReviewServiceServices,
            $this->mockApplicationService
        );
    }

    public function testGetConfigFromData(): void
    {
        $data = [
            'foo' => 'bar'
        ];

        $this->mockApplicationService->shouldReceive('getConfigFromData')
            ->with($data)
            ->andReturn('CONFIG');

        $this->assertEquals('CONFIG', $this->sut->getConfigFromData($data));
    }
}
