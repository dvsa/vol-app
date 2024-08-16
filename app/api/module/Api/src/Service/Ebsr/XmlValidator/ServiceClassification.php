<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\XmlValidator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;

/**
 * Class ServiceClassification
 * @package Olcs\Ebsr\Validator\Structure
 */
class ServiceClassification extends AbstractValidator
{
    public const STRUCTURE_ERROR = 'classification-structure-error';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::STRUCTURE_ERROR => 'Service classification element is missing from the XML file'
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
        $serviceElements = $value->getElementsByTagName('Service');
        foreach ($serviceElements as $serviceElement) {
            if ($serviceElement->getElementsByTagName('ServiceClassification')->length == 0) {
                $this->error(self::STRUCTURE_ERROR);
                return false;
            }
        }

        return true;
    }
}
