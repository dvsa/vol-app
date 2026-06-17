<?php

namespace Dvsa\OlcsTest\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Command\Submission\UpdateSubmissionSectionComment;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;

class UpdateSubmissionSectionCommentTest extends \PHPUnit\Framework\TestCase
{
    use CommandTest;

    protected function createBlankDto()
    {
        return new UpdateSubmissionSectionComment();
    }

    protected function getOptionalDtoFields()
    {
        return ['comment'];
    }

    protected function getValidFieldValues()
    {
        return [
            'id' => ['1', '99', '12345'],
            'version' => ['1', '5', '999'],
            'comment' => [
                '{"blocks":[{"type":"paragraph","data":{"text":"Valid EditorJS JSON"}}],"version":"2.22.2"}',
                '{"blocks":[],"version":"2.22.2"}',
                '{"test":"value"}' // Any valid JSON
            ]
        ];
    }

    protected function getInvalidFieldValues()
    {
        return [
            'id' => ['0', 'abc', ''],
            'version' => ['0', 'abc', ''],
            'comment' => [
                'invalid json',
                '{"invalid": json}'
            ]
        ];
    }

    protected function getFilterTransformations()
    {
        return [
            'id' => [['123', '123'], ['456', '456']],
            'version' => [['5', '5'], ['10', '10']],
            'comment' => [
                ['  {"blocks":[]}  ', '{"blocks":[]}']
            ]
        ];
    }
}
