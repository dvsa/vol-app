<?php

namespace Dvsa\OlcsTest\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionAction;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;

class CreateSubmissionActionTest extends \PHPUnit\Framework\TestCase
{
    use CommandTest;

    protected function createBlankDto()
    {
        return new CreateSubmissionAction();
    }

    protected function getOptionalDtoFields()
    {
        return [];
    }

    protected function getValidFieldValues()
    {
        return [
            'submission' => ['1', '99', '12345'],
            'isDecision' => ['Y', 'N'],
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
            'submission' => ['0', 'abc', null, ''],
            'isDecision' => ['X', 'yes', 'no', '1', null],
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
            'submission' => [['123', '123'], ['456', '456']],
            'isDecision' => [[' Y ', 'Y'], [' N ', 'N']],
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
