<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\RulesValidator;

use Laminas\Validator\AbstractValidator;

/**
 * Class ServiceNo
 */
class ServiceNo extends AbstractValidator
{
    public const RULES_ERROR = 'empty-service-code-error';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::RULES_ERROR => 'Unable to find a main service number, XML field "ServiceCode" must not be empty'
    ];

    /**
     * Checks the serviceNo field is populated, serviceNo array key will always be present
     *
     * @param array $value input value
     *
     * @return bool
     */
    public function isValid($value)
    {
        //with the current xml parser we'd only get empty string, but allow some flexibility for future
        $disallowed = ['', null, false];

        if (in_array($value['serviceNo'], $disallowed, true)) {
            $this->error(self::RULES_ERROR);
            return false;
        }

        return true;
    }
}
