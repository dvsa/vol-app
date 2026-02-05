<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

/**
 * AbstractQuestionAnswerData
 */
abstract class AbstractQuestionAnswerData extends SingleValueTestAbstract
{
    #[\Override]
    protected function getData($key, $value)
    {
        return [
            'questionAnswerData' => [
                $key => [
                    'answer' => $value
                ]
            ]
        ];
    }
}
