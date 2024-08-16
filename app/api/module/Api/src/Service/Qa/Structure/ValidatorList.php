<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use RuntimeException;

class ValidatorList
{
    /** @var array */
    private $validators;

    /**
     * Create instance
     *
     * @return ValidatorList
     */
    public function __construct()
    {
        $this->validators = [];
    }

    /**
     * Find the first validator by rule name within the list
     *
     *
     * @return Validator
     * @throws RuntimeException
     */
    public function getValidatorByRule(string $rule)
    {
        foreach ($this->validators as $validator) {
            if ($validator->hasRule($rule)) {
                return $validator;
            }
        }

        throw new RuntimeException(
            sprintf(
                'Validator with rule name %s not found',
                $rule
            )
        );
    }

    /**
     * Add a validator to the end of the list
     */
    public function addValidator(Validator $validator)
    {
        $this->validators[] = $validator;
    }

    /**
     * Get the representation of this class to be returned by the API endpoint
     *
     * @return array
     */
    public function getRepresentation()
    {
        $response = [];

        foreach ($this->validators as $validator) {
            $response[] = $validator->getRepresentation();
        }

        return $response;
    }
}
