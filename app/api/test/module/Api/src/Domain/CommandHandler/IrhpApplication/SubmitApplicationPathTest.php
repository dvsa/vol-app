<?php

declare(strict_types=1);

/**
 * Submit Application Path test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use DateTime;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\SubmitApplicationPath as Sut;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps\SupplementedApplicationStepsProvider;
use Dvsa\Olcs\Api\Service\Qa\PostSubmit\IrhpApplicationPostSubmitHandler;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaContextFactory;
use Dvsa\Olcs\Api\Service\Qa\Strategy\FormControlStrategyInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplicationPath as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Submit Application Path test
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class SubmitApplicationPathTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'QaContextFactory' => m::mock(QaContextFactory::class),
            'QaSupplementedApplicationStepsProvider' => m::mock(SupplementedApplicationStepsProvider::class),
            'QaIrhpApplicationPostSubmitHandler' => m::mock(IrhpApplicationPostSubmitHandler::class),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $irhpApplicationId = 459;

        $postData = [
            'fieldset123' => [
                'qaElement' => '123'
            ]
        ];

        $command = Cmd::create(
            [
                'id' => $irhpApplicationId,
                'postData' => $postData
            ]
        );

        $irhpApplication = m::mock(IrhpApplication::class);

        $applicationStep1 = m::mock(ApplicationStep::class);

        $qaContext1 = m::mock(QaContext::class);
        $qaContext1->shouldReceive('isApplicationStepEnabled')
            ->withNoArgs()
            ->andReturnTrue();

        $this->mockedSmServices['QaContextFactory']->shouldReceive('create')
            ->with($applicationStep1, $irhpApplication)
            ->andReturn($qaContext1);

        $formControlStrategy1 = m::mock(FormControlStrategyInterface::class);
        $formControlStrategy1->shouldReceive('saveFormData')
            ->with($qaContext1, $postData)
            ->once()
            ->globally()
            ->ordered();

        $supplementedApplicationStep1 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep1->shouldReceive('getFormControlStrategy')
            ->andReturn($formControlStrategy1);
        $supplementedApplicationStep1->shouldReceive('getApplicationStep')
            ->andReturn($applicationStep1);

        $applicationStep2 = m::mock(ApplicationStep::class);

        $qaContext2 = m::mock(QaContext::class);
        $qaContext2->shouldReceive('isApplicationStepEnabled')
            ->withNoArgs()
            ->andReturnTrue();

        $this->mockedSmServices['QaContextFactory']->shouldReceive('create')
            ->with($applicationStep2, $irhpApplication)
            ->andReturn($qaContext2);

        $formControlStrategy2 = m::mock(FormControlStrategyInterface::class);
        $formControlStrategy2->shouldReceive('saveFormData')
            ->with($qaContext2, $postData)
            ->once()
            ->globally()
            ->ordered();

        $supplementedApplicationStep2 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep2->shouldReceive('getFormControlStrategy')
            ->andReturn($formControlStrategy2);
        $supplementedApplicationStep2->shouldReceive('getApplicationStep')
            ->andReturn($applicationStep2);

        $applicationStep3 = m::mock(ApplicationStep::class);

        $qaContext3 = m::mock(QaContext::class);
        $qaContext3->shouldReceive('isApplicationStepEnabled')
            ->withNoArgs()
            ->andReturnFalse();

        $this->mockedSmServices['QaContextFactory']->shouldReceive('create')
            ->with($applicationStep3, $irhpApplication)
            ->andReturn($qaContext3);

        $supplementedApplicationStep3 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep3->shouldReceive('getApplicationStep')
            ->andReturn($applicationStep3);

        $supplementedApplicationSteps = [
            $supplementedApplicationStep1,
            $supplementedApplicationStep2,
            $supplementedApplicationStep3,
        ];

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($irhpApplication);

        $this->mockedSmServices['QaSupplementedApplicationStepsProvider']->shouldReceive('get')
            ->with($irhpApplication)
            ->andReturn($supplementedApplicationSteps);

        $this->mockedSmServices['QaIrhpApplicationPostSubmitHandler']->shouldReceive('handle')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->sut->handleCommand($command);
    }
}
