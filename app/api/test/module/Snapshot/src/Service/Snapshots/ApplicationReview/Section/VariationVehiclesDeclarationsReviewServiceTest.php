<?php

/**
 * Variation Vehicles Declarations Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationVehiclesDeclarationsReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationVehiclesDeclarationsReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Vehicles Declarations Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationVehiclesDeclarationsReviewServiceTest extends MockeryTestCase
{
    public $mockTranslator;
    protected $sut;

    /** @var ApplicationVehiclesDeclarationsReviewService */
    private $mockApplicationService;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->mockApplicationService = m::mock(ApplicationVehiclesDeclarationsReviewService::class);

        $this->sut = new VariationVehiclesDeclarationsReviewService(
            $abstractReviewServiceServices,
            $this->mockApplicationService
        );
    }

    public function testGetConfigFromData()
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
