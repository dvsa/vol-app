<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Domain\FormControlServiceManager;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Strategy\FormControlStrategyInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Structure\ApplicationStepFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\ApplicationStepGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ApplicationStepGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplicationStepGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $frontendType = 'checkbox';
        $fieldsetName = 'fieldset123';
        $questionShortKey = 'Cabotage';
        $questionId = 47;
        $questionSlug = 'question-slug';
        $applicationStepEnabled = true;
        $elementContainer = 'element-container';

        $question = m::mock(Question::class);
        $question->shouldReceive('getId')
            ->andReturn($questionId);
        $question->shouldReceive('getActiveQuestionText->getQuestionShortKey')
            ->withNoArgs()
            ->andReturn($questionShortKey);
        $question->shouldReceive('getSlug')
            ->withNoArgs()
            ->andReturn($questionSlug);

        $applicationStepEntity = m::mock(ApplicationStepEntity::class);
        $applicationStepEntity->shouldReceive('getFieldsetName')
            ->andReturn($fieldsetName);
        $applicationStepEntity->shouldReceive('getQuestion')
            ->andReturn($question);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStepEntity);
        $qaContext->shouldReceive('isApplicationStepEnabled')
            ->withNoArgs()
            ->andReturnTrue();

        $element = m::mock(ElementInterface::class);
        $validatorList = m::mock(ValidatorList::class);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getValidatorList')
            ->withNoArgs()
            ->andReturn($validatorList);

        $formControlStrategy = m::mock(FormControlStrategyInterface::class);
        $formControlStrategy->shouldReceive('getFrontendType')
            ->andReturn($frontendType);
        $formControlStrategy->shouldReceive('getElement')
            ->with($elementGeneratorContext)
            ->andReturn($element);

        $formControlServiceManager = m::mock(FormControlServiceManager::class);
        $formControlServiceManager->shouldReceive('getByApplicationStep')
            ->with($applicationStepEntity)
            ->andReturn($formControlStrategy);

        $applicationStep = m::mock(ApplicationStep::class);

        $applicationStepFactory = m::mock(ApplicationStepFactory::class);
        $applicationStepFactory->shouldReceive('create')
            ->with(
                $frontendType,
                $fieldsetName,
                $questionShortKey,
                $questionSlug,
                $applicationStepEnabled,
                $element,
                $validatorList
            )
            ->andReturn($applicationStep);

        $elementGeneratorContextGenerator = m::mock(ElementGeneratorContextGenerator::class);
        $elementGeneratorContextGenerator->shouldReceive('generate')
            ->with($qaContext, $elementContainer)
            ->andReturn($elementGeneratorContext);

        $applicationStepGenerator = new ApplicationStepGenerator(
            $formControlServiceManager,
            $applicationStepFactory,
            $elementGeneratorContextGenerator
        );

        $this->assertSame(
            $applicationStep,
            $applicationStepGenerator->generate($qaContext, $elementContainer)
        );
    }
}
