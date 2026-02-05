<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * Class AbstractQuestionAnswerData
 */
abstract class AbstractQuestionAnswerData extends SingleValueAbstract
{
    /**
     * get value
     *
     * @return null|string
     */
    #[\Override]
    protected function getValue()
    {
        return $this->data['questionAnswerData'][static::FIELD]['answer'] ?? null;
    }
}
