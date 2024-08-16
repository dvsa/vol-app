<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerWriter;

class BaseAnswerSaver
{
    /**
     * Create service instance
     *
     *
     * @return BaseAnswerSaver
     */
    public function __construct(private readonly GenericAnswerWriter $genericAnswerWriter, private readonly GenericAnswerFetcher $genericAnswerFetcher)
    {
    }

    /**
     * Save an answer to persistent storage, optionally specifying the type of data being stored
     *
     * @param string|null $forceQuestionType
     */
    public function save(
        QaContext $qaContext,
        array $postData,
        $forceQuestionType = null
    ) {
        $this->genericAnswerWriter->write(
            $qaContext,
            $this->genericAnswerFetcher->fetch(
                $qaContext->getApplicationStepEntity(),
                $postData
            ),
            $forceQuestionType
        );
    }
}
