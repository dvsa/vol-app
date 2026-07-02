<?php

namespace Dvsa\OlcsTest\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Command\Submission\UpdateSubmissionAction;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;

class UpdateSubmissionActionTest extends \PHPUnit\Framework\TestCase
{
    use CommandTest;

    protected function createBlankDto()
    {
        return new UpdateSubmissionAction();
    }

    protected function getOptionalDtoFields()
    {
        return [];
    }

    protected function getValidFieldValues()
    {
        return [
            'id' => ['1', '99', '12345'],
            'version' => ['1', '5', '999'],
            'actionTypes' => [['action1', 'action2'], ['single-action']],
            'reasons' => [['1', '2', '3'], ['42']],
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
            'id' => ['0', 'abc', null, ''],
            'version' => ['0', 'abc', null, ''],
            'actionTypes' => [[''], ['toolongactiontype' . str_repeat('x', 50)]],
            'comment' => [
                'invalid json',
                '{"invalid": json}',
                'abcd', // Less than 5 chars
                null,
                ''
            ]
        ];
    }

    protected function getFilterTransformations()
    {
        return [
            'id' => [['123', '123'], ['456', '456']],
            'version' => [['5', '5'], ['10', '10']],
            'actionTypes' => [
                [['  action1  ', '  action2  '], ['action1', 'action2']]
            ],
            'reasons' => [
                [['123', '456'], ['123', '456']]
            ],
            'comment' => [
                ['  {"blocks":[]}  ', '{"blocks":[]}']
            ]
        ];
    }
}
