<?php

namespace Common\Form\Elements\Validators;

class EcmtCandidatePermitSelectionValidator
{
    public const CANDIDATE_VALUE_PREFIX = 'candidate-';

    /**
     * Verify that at least one checkbox has been ticked amongst all candidate permit checkboxes
     *
     * @param array $context
     *
     * @return bool
     *
     * @psalm-param 'notused' $value
     */
    public static function validate(string $value, $context)
    {
        foreach ($context as $name => $value) {
            if (!str_starts_with($name, self::CANDIDATE_VALUE_PREFIX)) {
                continue;
            }
            if ($value != '1') {
                continue;
            }
            return true;
        }

        return false;
    }
}
