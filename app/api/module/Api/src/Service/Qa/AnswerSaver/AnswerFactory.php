<?php

namespace Dvsa\Olcs\Api\Service\Qa\AnswerSaver;

use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Entity\Generic\QuestionText;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;

class AnswerFactory
{
    /**
     * Create an Answer entity instance referencing the supplied parameters
     *
     *
     * @return Answer
     */
    public function create(QuestionText $questionText, QaEntityInterface $qaEntity)
    {
        return $qaEntity->createAnswer($questionText);
    }
}
