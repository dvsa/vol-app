<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces;

/**
 * Result of entity generation process
 */
class GenerationResult
{
    private array $entities;
    private array $errors;
    private array $warnings;
    private float $duration;

    public function __construct(
        array $entities = [],
        array $errors = [],
        array $warnings = [],
        float $duration = 0.0
    ) {
        $this->entities = $entities;
        $this->errors = $errors;
        $this->warnings = $warnings;
        $this->duration = $duration;
    }

    public function getEntities(): array
    {
        return $this->entities;
    }

    public function addEntity(EntityData $entity): void
    {
        $this->entities[] = $entity;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function setDuration(float $duration): void
    {
        $this->duration = $duration;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    public function isSuccessful(): bool
    {
        return !$this->hasErrors();
    }

    public function getEntityCount(): int
    {
        return count($this->entities);
    }
}