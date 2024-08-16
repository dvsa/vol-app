<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;

/**
 * Class MissingSection
 * @package Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice
 */
class MissingSection extends AbstractValidator
{
    public const SHORT_NOTICE_MISSING_SECTION_ERROR = 'short-notice-missing-section-error';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::SHORT_NOTICE_MISSING_SECTION_ERROR =>
            'This application is short notice, but the file doesn\'t have a short notice section'
    ];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @param array $context
     * @return bool
     */
    public function isValid($value, $context = [])
    {
        /** @var BusRegEntity $busReg */
        $busReg = $context['busReg'];

        if ($busReg->getIsShortNotice() === 'Y' && empty($value['busShortNotice'])) {
            $this->error(self::SHORT_NOTICE_MISSING_SECTION_ERROR);
            return false;
        }

        return true;
    }
}
