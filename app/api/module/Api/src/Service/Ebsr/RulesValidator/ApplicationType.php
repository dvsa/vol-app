<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\RulesValidator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission;

/**
 * Class ApplicationType
 * @package Dvsa\Olcs\Api\Service\Ebsr\RulesValidator
 */
class ApplicationType extends AbstractValidator
{
    public const REFRESH_SUBMISSION_ERROR = 'app-type-refresh-submission-error';
    public const NEW_SUBMISSION_ERROR = 'app-type-new-submission-error';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::REFRESH_SUBMISSION_ERROR => 'Application type for a data refresh must be "nonChargeableChange"',
        self::NEW_SUBMISSION_ERROR => 'Application type for a new application must not be "nonChargeableChange"'
    ];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param array $value   the value being tested
     * @param array $context context values
     *
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value, $context = [])
    {
        //data refresh must be nonChargeableChange
        if (
            $context['submissionType'] === EbsrSubmission::DATA_REFRESH_SUBMISSION_TYPE &&
            $value['txcAppType'] !== 'nonChargeableChange'
        ) {
            $this->error(self::REFRESH_SUBMISSION_ERROR);
            return false;
        }

        //new application must not be nonChargeableChange
        if (
            $context['submissionType'] === EbsrSubmission::NEW_SUBMISSION_TYPE &&
            $value['txcAppType'] === 'nonChargeableChange'
        ) {
            $this->error(self::NEW_SUBMISSION_ERROR);
            return false;
        }

        return true;
    }
}
