<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Roles Optional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 */
trait RolesOptional
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *         "haystack": {"internal-limited-read-only", "internal-read-only", "internal-case-worker", "internal-admin", "internal-irhp-admin", "operator-tc", "operator-admin", "operator-user", "operator-tm", "partner-admin", "partner-user", "local-authority-admin", "local-authority-user", "system-admin"}
     *     }
     * )
     * @Transfer\Optional
     */
    protected $roles = [];

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
