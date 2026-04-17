<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

/**
 * AbstractQuestionAnswerData
 */
abstract class AbstractQuestionAnswerData extends SingleValueTestAbstract
{
    #[\Override]
    protected function getData(mixed $key, mixed $value): array
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
