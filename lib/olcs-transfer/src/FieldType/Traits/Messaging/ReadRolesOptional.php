<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits\Messaging;

/**
 * Trait Conversation
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait ReadRolesOptional
{
    /**
     * @var bool
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Boolean")
     */
    protected $includeReadRoles = false;

    /**
     * @Transfer\Optional
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected array $readRoles = [];


    public function getIncludeReadRoles(): bool
    {
        return $this->includeReadRoles;
    }

    public function getReadRoles(): array
    {
        return $this->readRoles;
    }
}
