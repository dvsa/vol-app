<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

class ValidatorFactory
{
    /**
     * Create and return a ValidatorFactory instance
     *
     * @param string $rule
     *
     * @return Validator
     */
    public function create($rule, array $parameters)
    {
        return new Validator($rule, $parameters);
    }
}
