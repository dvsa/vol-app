<?php

namespace Dvsa\Olcs\Api\Service\Qa\AnswersSummary;

use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRow;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRowFactory;
use Dvsa\Olcs\Api\Service\Qa\Facade\SupplementedApplicationSteps\SupplementedApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\QaContextFactory;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\ElementContainer;
use RuntimeException;
use Laminas\View\Renderer\RendererInterface;

class AnswersSummaryRowGenerator
{
    public const TEMPLATE_DIRECTORY = 'answers-summary/';

    /**
     * Create service instance
     *
     *
     * @return AnswersSummaryRowGenerator
     */
    public function __construct(private readonly AnswersSummaryRowFactory $answersSummaryRowFactory, private readonly RendererInterface $viewRenderer, private readonly QaContextFactory $qaContextFactory, private readonly ElementGeneratorContextGenerator $elementGeneratorContextGenerator)
    {
    }

    /**
     * Build and return a AnswersSummaryRow instance using the appropriate data sources
     *
     * @param bool $isSnapshot
     *
     * @return AnswersSummaryRow
     */
    public function generate(
        SupplementedApplicationStep $supplementedApplicationStep,
        QaEntityInterface $qaEntity,
        $isSnapshot
    ) {
        $formControlStrategy = $supplementedApplicationStep->getFormControlStrategy();
        $answerSummaryProvider = $formControlStrategy->getAnswerSummaryProvider();

        if (!$answerSummaryProvider->supports($qaEntity)) {
            throw new RuntimeException(
                sprintf(
                    'Answer summary provider does not support entity type %s',
                    $qaEntity->getCamelCaseEntityName()
                )
            );
        }

        $applicationStep = $supplementedApplicationStep->getApplicationStep();
        $qaContext = $this->qaContextFactory->create($applicationStep, $qaEntity);
        $questionText = $formControlStrategy->getQuestionText($qaContext);
        $elementGeneratorContext = $this->elementGeneratorContextGenerator->generate(
            $qaContext,
            ElementContainer::ANSWERS_SUMMARY
        );

        $templatePath = self::TEMPLATE_DIRECTORY . $answerSummaryProvider->getTemplateName();
        $templateVariables = $answerSummaryProvider->getTemplateVariables(
            $qaContext,
            $formControlStrategy->getElement($elementGeneratorContext),
            $isSnapshot
        );

        $question = $applicationStep->getQuestion();
        $formattedAnswer = $this->viewRenderer->render($templatePath, $templateVariables);

        $slug = (!$isSnapshot && $answerSummaryProvider->shouldIncludeSlug($qaEntity)) ? $question->getSlug() : null;

        return $this->answersSummaryRowFactory->create(
            $questionText->getQuestionSummary()->getTranslateableText()->getKey(),
            $formattedAnswer,
            $slug
        );
    }
}
