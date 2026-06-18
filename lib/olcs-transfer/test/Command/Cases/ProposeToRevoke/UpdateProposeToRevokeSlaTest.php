<?php

namespace Dvsa\OlcsTest\Transfer\Command\Cases\ProposeToRevoke;

use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\UpdateProposeToRevokeSla;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;

class UpdateProposeToRevokeSlaTest extends \PHPUnit\Framework\TestCase
{
    use CommandTest;

    protected function createBlankDto()
    {
        return new UpdateProposeToRevokeSla();
    }

    protected function getOptionalDtoFields()
    {
        return [
            'isSubmissionRequiredForApproval',
            'approvalSubmissionIssuedDate',
            'approvalSubmissionReturnedDate',
            'approvalSubmissionPresidingTc',
            'iorLetterIssuedDate',
            'operatorResponseDueDate',
            'operatorResponseReceivedDate',
            'isSubmissionRequiredForAction',
            'finalSubmissionIssuedDate',
            'finalSubmissionReturnedDate',
            'finalSubmissionPresidingTc',
            'actionToBeTaken',
            'revocationLetterIssuedDate',
            'nfaLetterIssuedDate',
            'warningLetterIssuedDate',
            'piAgreedDate',
            'otherActionAgreedDate'
        ];
    }

    protected function getValidFieldValues()
    {
        return [
            'id' => ['1'],
            'version' => ['1'],
            'isSubmissionRequiredForApproval' => ['0','1'],
            'approvalSubmissionIssuedDate' => ['2017-12-11'],
            'approvalSubmissionReturnedDate' => ['2017-11-11'],
            'approvalSubmissionPresidingTc' => ['1'],
            'iorLetterIssuedDate' => ['2017-11-11'],
            'operatorResponseDueDate' => ['2017-11-11'],
            'operatorResponseReceivedDate' => ['2017-11-11'],
            'isSubmissionRequiredForAction' => ['0','1'],
            'finalSubmissionIssuedDate' => ['2017-11-11'],
            'finalSubmissionReturnedDate' => ['2017-11-11'],
            'finalSubmissionPresidingTc' => ['1'],
            'actionToBeTaken' => [
                'ptr_action_to_be_taken_revoke',
                'ptr_action_to_be_taken_pi',
                'ptr_action_to_be_taken_warning',
                'ptr_action_to_be_taken_nfa',
                'ptr_action_to_be_taken_other'
                ],
            'revocationLetterIssuedDate' => ['2017-11-11'],
            'nfaLetterIssuedDate' => ['2017-11-11'],
            'warningLetterIssuedDate' => ['2017-11-11'],
            'piAgreedDate' => ['2017-11-11'],
            'otherActionAgreedDate' => ['2017-11-11']
        ];
    }

    protected function getInvalidFieldValues()
    {
        return [
            'id' => ['not a number'],
            'version' => ['not a number'],
            'isSubmissionRequiredForApproval' => ['not a number'],
            'approvalSubmissionIssuedDate' => ['not a date'],
            'approvalSubmissionReturnedDate' => ['not a date'],
            'approvalSubmissionPresidingTc' => [['not a number']],
            'iorLetterIssuedDate' => ['not a date'],
            'operatorResponseDueDate' => ['not a date'],
            'operatorResponseReceivedDate' => ['not a date'],
            'isSubmissionRequiredForAction' => ['not a number'],
            'finalSubmissionIssuedDate' => ['not a date'],
            'finalSubmissionReturnedDate' => ['not a date'],
            'finalSubmissionPresidingTc' => [['not a number']],
            'actionToBeTaken' => [
                'not a valid option'
            ],
            'revocationLetterIssuedDate' => ['not a date'],
            'nfaLetterIssuedDate' => ['not a date'],
            'warningLetterIssuedDate' => ['not a date'],
            'piAgreedDate' => ['not a date'],
            'otherActionAgreedDate' => ['not a date']
        ];
    }

    protected function getFilterTransformations()
    {
        return [
            'id' => [
                [1, '1']
            ],
            'version' => [
                [1, '1']
            ],
            'isSubmissionRequiredForApproval' => [
                [1, '1'],
                [0, '0']
            ],
            'isSubmissionRequiredForAction' => [
                [1, '1'],
                [0, '0']
            ],
            'finalSubmissionPresidingTc' => [
                [1, '1']
            ],
            'approvalSubmissionPresidingTc' => [
                [1, '1']
            ]
        ];
    }
}
