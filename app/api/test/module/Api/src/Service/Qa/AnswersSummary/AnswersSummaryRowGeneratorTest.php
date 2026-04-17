<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\AnswersSummary;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Entity\Generic\Question as QuestionEntity;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRow;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRowFactory;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswersSummaryRowGenerator;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswerSummaryProviderInterface;
use Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps\SupplementedApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaContextFactory;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\Olcs\Api\Service\Qa\Strategy\FormControlStrategyInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\ElementContainer;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionText;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * AnswersSummaryRowGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AnswersSummaryRowGeneratorTest extends MockeryTestCase
{
    private $formControlStrategy;

    private $supplementedApplicationStep;

    private $qaEntity;

    private $answerSummaryProvider;

    private $answersSummaryRowFactory;

    private $viewRenderer;

    private $qaContextFactory;

    private $elementGeneratorContextGenerator;

    private $answersSummaryRowGenerator;

    public function setUp(): void
    {
        $this->formControlStrategy = m::mock(FormControlStrategyInterface::class);

        $this->supplementedApplicationStep = m::mock(SupplementedApplicationStep::class);
        $this->supplementedApplicationStep->shouldReceive('getFormControlStrategy')
            ->withNoArgs()
            ->andReturn($this->formControlStrategy);

        $this->qaEntity = m::mock(QaEntityInterface::class);

        $this->answerSummaryProvider = m::mock(AnswerSummaryProviderInterface::class);

        $this->formControlStrategy->shouldReceive('getAnswerSummaryProvider')
            ->withNoArgs()
            ->once()
            ->andReturn($this->answerSummaryProvider);

        $this->answersSummaryRowFactory = m::mock(AnswersSummaryRowFactory::class);

        $this->viewRenderer = m::mock(RendererInterface::class);

        $this->qaContextFactory = m::mock(QaContextFactory::class);

        $this->elementGeneratorContextGenerator = m::mock(ElementGeneratorContextGenerator::class);

        $this->answersSummaryRowGenerator = new AnswersSummaryRowGenerator(
            $this->answersSummaryRowFactory,
            $this->viewRenderer,
            $this->qaContextFactory,
            $this->elementGeneratorContextGenerator
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testGenerateNotSupported(mixed $isSnapshot): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Answer summary provider does not support entity type entityType');

        $this->qaEntity->shouldReceive('getCamelCaseEntityName')
            ->withNoArgs()
            ->andReturn('entityType');

        $this->answerSummaryProvider->shouldReceive('supports')
            ->with($this->qaEntity)
            ->andReturn(false);

        $this->answersSummaryRowGenerator->generate(
            $this->supplementedApplicationStep,
            $this->qaEntity,
            $isSnapshot
        );
    }

    public static function dpSnapshot(): array
    {
        return [
            [true],
            [false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGenerate')]
    public function testGenerate(mixed $isSnapshot, mixed $questionSlug, mixed $shouldIncludeSlug, mixed $expectedSlug): void
    {
        $questionTranslationKey = 'question.translation.key';
        $formattedAnswer = 'line 1<br>line 2';

        $templateName = 'irhp-no-of-permits';
        $expectedTemplatePath = 'answers-summary/irhp-no-of-permits';

        $templateVariables = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $questionEntity = m::mock(QuestionEntity::class);
        $questionEntity->shouldReceive('getSlug')
            ->withNoArgs()
            ->andReturn($questionSlug);

        $applicationStepEntity = m::mock(ApplicationStepEntity::class);
        $applicationStepEntity->shouldReceive('getQuestion')
            ->withNoArgs()
            ->andReturn($questionEntity);

        $qaContext = m::mock(QaContext::class);

        $element = m::mock(ElementInterface::class);

        $this->qaContextFactory->shouldReceive('create')
            ->with($applicationStepEntity, $this->qaEntity)
            ->once()
            ->andReturn($qaContext);

        $this->answerSummaryProvider->shouldReceive('getTemplateName')
            ->withNoArgs()
            ->andReturn($templateName);
        $this->answerSummaryProvider->shouldReceive('getTemplateVariables')
            ->with($qaContext, $element, $isSnapshot)
            ->andReturn($templateVariables);
        $this->answerSummaryProvider->shouldReceive('supports')
            ->with($this->qaEntity)
            ->andReturn(true);
        $this->answerSummaryProvider->shouldReceive('shouldIncludeSlug')
            ->with($this->qaEntity)
            ->andReturn($shouldIncludeSlug);

        $questionText = m::mock(QuestionText::class);
        $questionText->shouldReceive('getQuestionSummary->getTranslateableText->getKey')
            ->withNoArgs()
            ->andReturn($questionTranslationKey);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);

        $this->formControlStrategy->shouldReceive('getQuestionText')
            ->with($qaContext)
            ->andReturn($questionText);
        $this->formControlStrategy->shouldReceive('getElement')
            ->with($elementGeneratorContext)
            ->andReturn($element);

        $this->elementGeneratorContextGenerator->shouldReceive('generate')
            ->with($qaContext, ElementContainer::ANSWERS_SUMMARY)
            ->andReturn($elementGeneratorContext);

        $this->supplementedApplicationStep->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStepEntity);

        $this->viewRenderer->shouldReceive('render')
            ->with($expectedTemplatePath, $templateVariables)
            ->once()
            ->andReturn($formattedAnswer);

        $answersSummaryRow = m::mock(AnswersSummaryRow::class);

        $this->answersSummaryRowFactory->shouldReceive('create')
            ->with($questionTranslationKey, $formattedAnswer, $expectedSlug)
            ->once()
            ->andReturn($answersSummaryRow);

        $this->assertSame(
            $answersSummaryRow,
            $this->answersSummaryRowGenerator->generate(
                $this->supplementedApplicationStep,
                $this->qaEntity,
                $isSnapshot
            )
        );
    }

    public static function dpGenerate(): array
    {
        return [
            [false, 'no-of-permits', true, 'no-of-permits'],
            [false, 'no-of-permits', false, null],
            [true, 'no-of-permits', true, null],
            [true, 'no-of-permits', false, null],
        ];
    }
}
