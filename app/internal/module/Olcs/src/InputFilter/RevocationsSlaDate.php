<?php

namespace Olcs\InputFilter;

use Common\InputFilter\DateParts;

class RevocationsSlaDate extends DateParts
{
    #[\Override]
    protected function shouldValidateDateParts($context): bool
    {
        $conditions = [
            'approvalSubmissionIssuedDate' => ['isSubmissionRequiredForApproval', '1'],
            'approvalSubmissionReturnedDate' => ['isSubmissionRequiredForApproval', '1'],
            'finalSubmissionIssuedDate' => ['isSubmissionRequiredForAction', '1'],
            'finalSubmissionReturnedDate' => ['isSubmissionRequiredForAction', '1'],
            'revocationLetterIssuedDate' => ['actionToBeTaken', 'ptr_action_to_be_taken_revoke'],
            'nfaLetterIssuedDate' => ['actionToBeTaken', 'ptr_action_to_be_taken_nfa'],
            'warningLetterIssuedDate' => ['actionToBeTaken', 'ptr_action_to_be_taken_warning'],
            'piAgreedDate' => ['actionToBeTaken', 'ptr_action_to_be_taken_pi'],
            'otherActionAgreedDate' => ['actionToBeTaken', 'ptr_action_to_be_taken_other'],
        ];
        [$field, $expected] = $conditions[$this->getName()];
        return !is_array($context) || !array_key_exists($field, $context) || (string) $context[$field] === $expected;
    }
}
