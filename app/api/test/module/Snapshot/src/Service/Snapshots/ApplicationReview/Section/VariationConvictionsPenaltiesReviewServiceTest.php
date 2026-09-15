<?php

declare(strict_types=1);

/**
 * Variation Convictions Penalties Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationConvictionsPenaltiesReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationConvictionsPenaltiesReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Convictions Penalties Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class VariationConvictionsPenaltiesReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var ApplicationConvictionsPenaltiesReviewService */
    protected $mockApplicationService;

    #[\Override]
    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->mockApplicationService = m::mock(ApplicationConvictionsPenaltiesReviewService::class);

        $this->sut = new VariationConvictionsPenaltiesReviewService(
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
