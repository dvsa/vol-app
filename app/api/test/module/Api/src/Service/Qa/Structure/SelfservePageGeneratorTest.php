<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Domain\FormControlServiceManager;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Entity\Generic\Question as QuestionEntity;
use Dvsa\Olcs\Api\Entity\Generic\QuestionText as QuestionTextEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\Olcs\Api\Service\Qa\Strategy\FormControlStrategyInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Structure\ApplicationStepGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\ElementContainer;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionText;
use Dvsa\Olcs\Api\Service\Qa\Structure\SelfservePage;
use Dvsa\Olcs\Api\Service\Qa\Structure\SelfservePageFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\SelfservePageGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * SelfservePageGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SelfservePageGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $selfservePage = m::mock(SelfservePage::class);

        $questionKey = 'How will you use the permits';

        $additionalQaViewData = [
            'property1' => 'value1',
            'property2' => 'value2',
        ];

        $submitOptionsName = 'submit_options_name';

        $nextStepSlug = 'removals-cabotage';

        $applicationStep = m::mock(ApplicationStep::class);

        $questionText = m::mock(QuestionText::class);

        $questionTextEntity = m::mock(QuestionTextEntity::class);
        $questionTextEntity->shouldReceive('getTranslationKeyFromQuestionKey')
            ->withNoArgs()
            ->andReturn($questionKey);

        $questionEntity = m::mock(QuestionEntity::class);
        $questionEntity->shouldReceive('getActiveQuestionText')
            ->withNoArgs()
            ->andReturn($questionTextEntity);
        $questionEntity->shouldReceive('getSubmitOptions->getId')
            ->withNoArgs()
            ->andReturn($submitOptionsName);

        $applicationStepEntity = m::mock(ApplicationStepEntity::class);
        $applicationStepEntity->shouldReceive('getQuestion')
            ->withNoArgs()
            ->andReturn($questionEntity);
        $applicationStepEntity->shouldReceive('getNextStepSlug')
            ->withNoArgs()
            ->andReturn($nextStepSlug);

        $qaEntity = m::mock(QaEntityInterface::class);
        $qaEntity->shouldReceive('getAdditionalQaViewData')
            ->with($applicationStepEntity)
            ->andReturn($additionalQaViewData);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStepEntity);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($qaEntity);

        $selfservePageFactory = m::mock(SelfservePageFactory::class);
        $selfservePageFactory->shouldReceive('create')
            ->with(
                $questionKey,
                $additionalQaViewData,
                $applicationStep,
                $questionText,
                $submitOptionsName,
                $nextStepSlug
            )
            ->andReturn($selfservePage);

        $applicationStepGenerator = m::mock(ApplicationStepGenerator::class);
        $applicationStepGenerator->shouldReceive('generate')
            ->with($qaContext, ElementContainer::SELFSERVE_PAGE)
            ->andReturn($applicationStep);

        $formControlStrategy = m::mock(FormControlStrategyInterface::class);
        $formControlStrategy->shouldReceive('getQuestionText')
            ->with($qaContext)
            ->andReturn($questionText);

        $formControlServiceManager = m::mock(FormControlServiceManager::class);
        $formControlServiceManager->shouldReceive('getByApplicationStep')
            ->with($applicationStepEntity)
            ->andReturn($formControlStrategy);

        $selfservePageGenerator = new SelfservePageGenerator(
            $selfservePageFactory,
            $applicationStepGenerator,
            $formControlServiceManager
        );

        $this->assertSame(
            $selfservePage,
            $selfservePageGenerator->generate($qaContext)
        );
    }
}
