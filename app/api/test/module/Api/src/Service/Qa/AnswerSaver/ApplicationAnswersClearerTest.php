<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\AnswerSaver;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\ApplicationAnswersClearer;
use Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps\SupplementedApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps\SupplementedApplicationStepsProvider;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaContextFactory;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * ApplicationAnswersClearerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplicationAnswersClearerTest extends MockeryTestCase
{
    private $supplementedApplicationStepsProvider;

    private $qaContextFactory;

    private $applicationAnswersClearer;

    public function setUp(): void
    {
        $this->supplementedApplicationStepsProvider = m::mock(SupplementedApplicationStepsProvider::class);

        $this->qaContextFactory = m::mock(QaContextFactory::class);

        $this->applicationAnswersClearer = new ApplicationAnswersClearer(
            $this->supplementedApplicationStepsProvider,
            $this->qaContextFactory
        );
    }

    public function testClear(): void
    {
        $qaEntity = m::mock(QaEntityInterface::class);

        $applicationStep1 = m::mock(ApplicationStep::class);

        $qaContext1 = m::mock(QaContext::class);

        $this->qaContextFactory->shouldReceive('create')
            ->with($applicationStep1, $qaEntity)
            ->andReturn($qaContext1);

        $formControlStrategy1 = m::mock(FormControlStrategyInterface::class);
        $formControlStrategy1->shouldReceive('clearAnswer')
            ->with($qaContext1)
            ->once()
            ->globally()
            ->ordered();

        $supplementedApplicationStep1 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep1->shouldReceive('getFormControlStrategy')
            ->withNoArgs()
            ->andReturn($formControlStrategy1);
        $supplementedApplicationStep1->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep1);

        $applicationStep2 = m::mock(ApplicationStep::class);

        $qaContext2 = m::mock(QaContext::class);

        $this->qaContextFactory->shouldReceive('create')
            ->with($applicationStep2, $qaEntity)
            ->andReturn($qaContext2);

        $formControlStrategy2 = m::mock(FormControlStrategyInterface::class);
        $formControlStrategy2->shouldReceive('clearAnswer')
            ->with($qaContext2)
            ->once()
            ->globally()
            ->ordered();

        $supplementedApplicationStep2 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep2->shouldReceive('getFormControlStrategy')
            ->withNoArgs()
            ->andReturn($formControlStrategy2);
        $supplementedApplicationStep2->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep2);

        $supplementedApplicationSteps = [
            $supplementedApplicationStep1,
            $supplementedApplicationStep2
        ];

        $this->supplementedApplicationStepsProvider->shouldReceive('get')
            ->with($qaEntity)
            ->andReturn($supplementedApplicationSteps);

        $this->applicationAnswersClearer->clear($qaEntity);
    }

    public function testClearAfterApplicationStep(): void
    {
        $qaEntity = m::mock(QaEntityInterface::class);

        $applicationStep1 = m::mock(ApplicationStep::class);

        $supplementedApplicationStep1 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep1->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep1);

        $applicationStep2 = m::mock(ApplicationStep::class);

        $qaContext2 = m::mock(QaContext::class);
        $qaContext2->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($qaEntity);
        $qaContext2->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStep2);

        $supplementedApplicationStep2 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep2->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep2);

        $applicationStep3 = m::mock(ApplicationStep::class);

        $qaContext3 = m::mock(QaContext::class);

        $this->qaContextFactory->shouldReceive('create')
            ->with($applicationStep3, $qaEntity)
            ->andReturn($qaContext3);

        $formControlStrategy3 = m::mock(FormControlStrategyInterface::class);
        $formControlStrategy3->shouldReceive('clearAnswer')
            ->with($qaContext3)
            ->once()
            ->globally()
            ->ordered();

        $supplementedApplicationStep3 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep3->shouldReceive('getFormControlStrategy')
            ->withNoArgs()
            ->andReturn($formControlStrategy3);
        $supplementedApplicationStep3->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep3);

        $applicationStep4 = m::mock(ApplicationStep::class);

        $qaContext4 = m::mock(QaContext::class);

        $this->qaContextFactory->shouldReceive('create')
            ->with($applicationStep4, $qaEntity)
            ->andReturn($qaContext4);

        $formControlStrategy4 = m::mock(FormControlStrategyInterface::class);
        $formControlStrategy4->shouldReceive('clearAnswer')
            ->with($qaContext4)
            ->once()
            ->globally()
            ->ordered();

        $supplementedApplicationStep4 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep4->shouldReceive('getFormControlStrategy')
            ->withNoArgs()
            ->andReturn($formControlStrategy4);
        $supplementedApplicationStep4->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep4);

        $supplementedApplicationSteps = [
            $supplementedApplicationStep1,
            $supplementedApplicationStep2,
            $supplementedApplicationStep3,
            $supplementedApplicationStep4
        ];

        $this->supplementedApplicationStepsProvider->shouldReceive('get')
            ->with($qaEntity)
            ->andReturn($supplementedApplicationSteps);

        $this->applicationAnswersClearer->clearAfterApplicationStep($qaContext2);
    }

    public function testClearAfterApplicationStepException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('application step with id 5 was not found in application steps');

        $qaEntity = m::mock(QaEntityInterface::class);

        $applicationStepOther = m::mock(ApplicationStep::class);
        $applicationStepOther->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(5);

        $qaContextOther = m::mock(QaContext::class);
        $qaContextOther->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($qaEntity);
        $qaContextOther->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStepOther);

        $applicationStep1 = m::mock(ApplicationStep::class);

        $supplementedApplicationStep1 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep1->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep1);

        $applicationStep2 = m::mock(ApplicationStep::class);

        $supplementedApplicationStep2 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep2->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep2);

        $applicationStep3 = m::mock(ApplicationStep::class);

        $supplementedApplicationStep3 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep3->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep3);

        $applicationStep4 = m::mock(ApplicationStep::class);

        $supplementedApplicationStep4 = m::mock(SupplementedApplicationStep::class);
        $supplementedApplicationStep4->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep4);

        $supplementedApplicationSteps = [
            $supplementedApplicationStep1,
            $supplementedApplicationStep2,
            $supplementedApplicationStep3,
            $supplementedApplicationStep4
        ];

        $this->supplementedApplicationStepsProvider->shouldReceive('get')
            ->with($qaEntity)
            ->andReturn($supplementedApplicationSteps);

        $this->applicationAnswersClearer->clearAfterApplicationStep($qaContextOther);
    }
}
