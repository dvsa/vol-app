<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Conversation
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait CorrelationIdOptional
{
    /**
     * @Transfer\Filter(\Laminas\Filter\StringToLower::class)
     * @Transfer\Validator(\Laminas\Validator\Hex::class, options={"min": 40, "max": 40})
     * @Transfer\Optional
     */
    protected ?string $correlationId = null;

    public function getCorrelationId(): ?string
    {
        return $this->correlationId;
    }
}
