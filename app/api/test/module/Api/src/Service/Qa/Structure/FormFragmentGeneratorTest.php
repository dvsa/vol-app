<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaContextFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Structure\ApplicationStepGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\ElementContainer;
use Dvsa\Olcs\Api\Service\Qa\Structure\FormFragment;
use Dvsa\Olcs\Api\Service\Qa\Structure\FormFragmentFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\FormFragmentGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * FormFragmentGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FormFragmentGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);

        $applicationStep1 = m::mock(ApplicationStep::class);
        $applicationStep2 = m::mock(ApplicationStep::class);

        $applicationStepEntity1 = m::mock(ApplicationStepEntity::class);
        $applicationStepEntity2 = m::mock(ApplicationStepEntity::class);
        $applicationStepEntities = [$applicationStepEntity1, $applicationStepEntity2];

        $qaContext1 = m::mock(QaContext::class);
        $qaContext2 = m::mock(QaContext::class);

        $qaContextFactory = m::mock(QaContextFactory::class);
        $qaContextFactory->shouldReceive('create')
            ->with($applicationStepEntity1, $irhpApplicationEntity)
            ->andReturn($qaContext1);
        $qaContextFactory->shouldReceive('create')
            ->with($applicationStepEntity2, $irhpApplicationEntity)
            ->andReturn($qaContext2);

        $applicationStepGenerator = m::mock(ApplicationStepGenerator::class);
        $applicationStepGenerator->shouldReceive('generate')
            ->with($qaContext1, ElementContainer::FORM_FRAGMENT)
            ->andReturn($applicationStep1);
        $applicationStepGenerator->shouldReceive('generate')
            ->with($qaContext2, ElementContainer::FORM_FRAGMENT)
            ->andReturn($applicationStep2);

        $formFragment = m::mock(FormFragment::class);
        $formFragment->shouldReceive('addApplicationStep')
            ->with($applicationStep1)
            ->once()
            ->ordered();
        $formFragment->shouldReceive('addApplicationStep')
            ->with($applicationStep2)
            ->once()
            ->ordered();

        $formFragmentFactory = m::mock(FormFragmentFactory::class);
        $formFragmentFactory->shouldReceive('create')
            ->withNoArgs()
            ->andReturn($formFragment);

        $formFragmentGenerator = new FormFragmentGenerator(
            $formFragmentFactory,
            $applicationStepGenerator,
            $qaContextFactory
        );

        $this->assertSame(
            $formFragment,
            $formFragmentGenerator->generate($applicationStepEntities, $irhpApplicationEntity)
        );
    }
}
