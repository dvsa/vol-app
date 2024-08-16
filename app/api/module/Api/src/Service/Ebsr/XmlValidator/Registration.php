<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\XmlValidator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;

/**
 * Class Registration
 * @package Olcs\Ebsr\Validator\Structure
 */
class Registration extends AbstractValidator
{
    public const STRUCTURE_ERROR = 'registration-structure-error';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::STRUCTURE_ERROR => 'There must be exactly one registration element in the XML file'
    ];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        $registrationElements = $value->getElementsByTagName('Registration');
        if ($registrationElements->length != 1) {
            $this->error(self::STRUCTURE_ERROR);
            return false;
        }

        return true;
    }
}
