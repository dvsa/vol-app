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
        self::RULES_ERROR => 'Unable to find a main service number, XML field "LineName" must not be empty'
    ];

    /**
     * Checks the lineNames field is populated with at least one valid service number
     *
     * @param array $value input value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $disallowed = ['', null, false];

        if (in_array($value['lineNames'][0] ?? null, $disallowed, true)) {
            $this->error(self::RULES_ERROR);
            return false;
        }

        return true;
    }
}
