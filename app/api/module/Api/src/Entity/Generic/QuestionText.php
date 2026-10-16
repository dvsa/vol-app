<?php

namespace Dvsa\Olcs\Api\Entity\Generic;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionText Entity
 */
#[ORM\Table(name: 'question_text')]
#[ORM\Entity]
class QuestionText extends AbstractQuestionText
{
    /**
     * Return the translation key from the json array in the question key field
     *
     * @return string
     */
    public function getTranslationKeyFromQuestionKey()
    {
        $questionJson = json_decode($this->questionKey, true);
        return $questionJson['translateableText']['key'];
    }
}
