<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Answer as AnswerRepository;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerProvider;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Supports\AnyTrait;

class GenericAnswerClearer implements AnswerClearerInterface
{
    use AnyTrait;

    /**
     * Create service instance
     */
    public function __construct(private GenericAnswerProvider $genericAnswerProvider, private AnswerRepository $answerRepo)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function clear(QaContext $qaContext)
    {
        $answer = null;
        try {
            $answer = $this->genericAnswerProvider->get($qaContext);
        } catch (NotFoundException) {
        }

        if (is_object($answer)) {
            $qaContext->getQaEntity()->getAnswers()->remove($answer);
            $this->answerRepo->delete($answer);
        }
    }
}
