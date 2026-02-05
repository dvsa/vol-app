<?php

declare(strict_types=1);

/**
 * Variation LicenceHistory Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationLicenceHistoryReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationLicenceHistoryReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation LicenceHistory Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationLicenceHistoryReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var ApplicationLicenceHistoryReviewService */
    protected $mockApplicationService;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->mockApplicationService = m::mock(ApplicationLicenceHistoryReviewService::class);

        $this->sut = new VariationLicenceHistoryReviewService(
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
