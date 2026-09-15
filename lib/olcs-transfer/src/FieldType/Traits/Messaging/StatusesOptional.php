<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits\Messaging;

/**
 * Trait Conversation
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait StatusesOptional
{
    /**
     * @Transfer\Optional
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={
     *     "haystack": {"open", "closed"}
     * })
     */
    protected array $statuses = [];

    public function getStatuses(): array
    {
        return $this->statuses;
    }
}
