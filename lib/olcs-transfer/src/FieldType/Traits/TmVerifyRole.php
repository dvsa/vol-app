<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait TmVerifyRole
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait TmVerifyRole
{
    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",options={"haystack":{"tma_sign_as_tm", "tma_sign_as_op","tma_sign_as_top"}})
     */
    protected $role;

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }


    /**
     * setRole
     */
    public function setRole(string $role)
    {
        $this->role = $role;
    }
}
