<?php

namespace Olcs\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Class SubmissionSection
 * @package Olcs\Validator
 */
class SubmissionSection extends AbstractValidator
{
    const REQUIRED_SUBMISSIONTYPE   = 'submission_required';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::REQUIRED_SUBMISSIONTYPE      => "You must specify a submission type",
    );

    /**
     * Returns true if submission type is set
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);

        if (empty($this->getValue()['submissionType'])) {
            $this->error(self::REQUIRED_SUBMISSIONTYPE);
            return false;
        }

        return true;
    }
}
