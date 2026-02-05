<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Lva\Variation;

use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Service\Lva\Variation\PublishValidationService;
use Mockery as m;

/**
 * PublishValidationServiceTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PublishValidationServiceTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var \Dvsa\Olcs\Api\Service\Lva\Variation\PublishValidationService
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new PublishValidationService();
    }

    public function testValidate(): void
    {
        /* @var $application ApplicationEntity  */
        $application = m::mock(ApplicationEntity::class)->makePartial();

        $application->shouldReceive('isPublishable')->with()->once()->andReturn(true);

        $result = $this->sut->validate($application);

        $this->assertEmpty($result);
    }

    public function testValidateAllFail(): void
    {
        /* @var $application ApplicationEntity  */
        $application = m::mock(ApplicationEntity::class)->makePartial();

        $application->shouldReceive('isPublishable')->with()->once()->andReturn(false);

        $result = $this->sut->validate($application);

        $this->assertCount(1, $result);
        $this->assertArrayHasKey(PublishValidationService::ERROR_NOT_PUBLISHABLE, $result);
    }
}
