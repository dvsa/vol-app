<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;

/**
 * Class MissingReason
 * @package Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice
 */
class MissingReason extends AbstractValidator
{
    public const SHORT_NOTICE_MISSING_REASON_ERROR = 'short-notice-missing-reason-error';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::SHORT_NOTICE_MISSING_REASON_ERROR => 'A short notice reason hasn\'t been provided'
    ];

    /**
     * @var array
     */
    protected $fieldMap = [
        'bankHolidayChange',
        'unforseenChange',
        'timetableChange',
        'replacementChange',
        'holidayChange',
        'trcChange',
        'policeChange',
        'specialOccasionChange',
        'connectionChange',
        'notAvailableChange'
    ];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param array $value
     * @param array $context
     * @return bool
     */
    public function isValid($value, $context = [])
    {
        /** @var BusRegEntity $busReg */
        $busReg = $context['busReg'];

        if ($busReg->getIsShortNotice() === 'Y') {
            $reasonGiven = false;
            $sn = $value['busShortNotice'];

            foreach ($this->fieldMap as $field) {
                if (isset($sn[$field]) && $sn[$field] === 'Y') {
                    $reasonGiven = true;
                    break;
                }
            }

            if ($reasonGiven === false) {
                $this->error(self::SHORT_NOTICE_MISSING_REASON_ERROR);
                return false;
            }
        }

        return true;
    }
}
