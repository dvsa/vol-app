<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswerSummaryProviderInterface;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerClearerInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerSaverInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextGeneratorInterface;
use RuntimeException;

class BaseFormControlStrategy implements FormControlStrategyInterface
{
    public const FRONTEND_DESTINATION_NEXT_STEP = 'NEXT_STEP';

    /**
     * Create service instance
     *
     * @param string $frontendType
     *
     * @return BaseFormControlStrategy
     */
    public function __construct(private $frontendType, private readonly ElementGeneratorInterface $elementGenerator, private readonly AnswerSaverInterface $answerSaver, private readonly AnswerClearerInterface $answerClearer, private readonly QuestionTextGeneratorInterface $questionTextGenerator, private readonly AnswerSummaryProviderInterface $answerSummaryProvider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendType()
    {
        return $this->frontendType;
    }

    /**
     * {@inheritdoc}
     */
    public function getElement(ElementGeneratorContext $context)
    {
        $qaEntity = $context->getQaEntity();

        if (!$this->elementGenerator->supports($qaEntity)) {
            throw new RuntimeException('Element generator does not support entity type');
        }

        return $this->elementGenerator->generate($context);
    }

    /**
     * {@inheritdoc}
     */
    public function saveFormData(QaContext $qaContext, array $postData)
    {
        $qaEntity = $qaContext->getQaEntity();

        if (!$this->answerSaver->supports($qaEntity)) {
            throw new RuntimeException('Answer saver does not support entity type');
        }

        $destinationName = $this->answerSaver->save($qaContext, $postData);
        if (is_null($destinationName)) {
            $destinationName = self::FRONTEND_DESTINATION_NEXT_STEP;
        }

        return $destinationName;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAnswer(QaContext $qaContext)
    {
        $qaEntity = $qaContext->getQaEntity();

        if (!$this->answerClearer->supports($qaEntity)) {
            throw new RuntimeException('Answer clearer does not support entity type');
        }

        $this->answerClearer->clear($qaContext);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionText(QaContext $qaContext)
    {
        $qaEntity = $qaContext->getQaEntity();

        if (!$this->questionTextGenerator->supports($qaEntity)) {
            throw new RuntimeException('Question text generator does not support entity type');
        }

        return $this->questionTextGenerator->generate($qaContext);
    }

    /**
     * {@inheritdoc}
     */
    public function getAnswerSummaryProvider()
    {
        return $this->answerSummaryProvider;
    }
}
