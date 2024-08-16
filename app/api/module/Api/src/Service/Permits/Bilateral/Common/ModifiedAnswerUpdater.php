<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Common;

use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\ApplicationAnswersClearer;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerWriter;
use Dvsa\Olcs\Api\Service\Qa\QaContext;

class ModifiedAnswerUpdater
{
    /**
     * Create service instance
     *
     *
     * @return ModifiedAnswerUpdater
     */
    public function __construct(private readonly GenericAnswerWriter $genericAnswerWriter, private readonly ApplicationAnswersClearer $applicationAnswersClearer)
    {
    }

    /**
     * Update the answer value relating to a specific country within a bilateral application, clearing answers to
     * subsequent questions if the answer has been changed
     *
     * @param string $existingAnswer
     * @param string $newAnswer
     */
    public function update(QaContext $qaContext, $existingAnswer, $newAnswer)
    {
        if (!is_null($existingAnswer) && ($existingAnswer != $newAnswer)) {
            $this->applicationAnswersClearer->clearAfterApplicationStep($qaContext);
        }

        $this->genericAnswerWriter->write($qaContext, $newAnswer);
    }
}
