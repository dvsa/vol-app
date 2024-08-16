<?php

namespace Dvsa\Olcs\Api\Service\Qa\AnswerSaver;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Answer as AnswerRepository;

class GenericAnswerWriter
{
    /**
     * Create service instance
     *
     *
     * @return GenericAnswerWriter
     */
    public function __construct(private readonly GenericAnswerProvider $genericAnswerProvider, private readonly AnswerFactory $answerFactory, private readonly AnswerRepository $answerRepo)
    {
    }

    /**
     * Save an answer corresponding to the supplied application step and application to persistent storage using
     * the supplied answer value
     *
     * @param string|null $forceQuestionType
     */
    public function write(
        QaContext $qaContext,
        mixed $answerValue,
        $forceQuestionType = null
    ) {
        $applicationStep = $qaContext->getApplicationStepEntity();
        $qaEntity = $qaContext->getQaEntity();

        $question = $applicationStep->getQuestion();

        try {
            $answer = $this->genericAnswerProvider->get($qaContext);
        } catch (NotFoundException) {
            $answer = $this->answerFactory->create(
                $question->getActiveQuestionText(),
                $qaEntity
            );
        }

        $questionType = $question->getQuestionType();
        if (!is_null($forceQuestionType)) {
            $questionType = $forceQuestionType;
        }

        $answer->setValue($questionType, $answerValue);
        $qaEntity->addAnswers($answer);

        $this->answerRepo->save($answer);
    }
}
