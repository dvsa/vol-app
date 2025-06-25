<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces;

/**
 * Value object representing column metadata
 */
class ColumnMetadata
{
    private string $name;
    private string $type;
    private ?int $length;
    private bool $nullable;
    private bool $primary;
    private bool $autoIncrement;
    private mixed $default;
    private ?string $comment;
    private array $options;

    public function __construct(
        string $name,
        string $type,
        ?int $length = null,
        bool $nullable = true,
        bool $primary = false,
        bool $autoIncrement = false,
        mixed $default = null,
        ?string $comment = null,
        array $options = []
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
        $this->nullable = $nullable;
        $this->primary = $primary;
        $this->autoIncrement = $autoIncrement;
        $this->default = $default;
        $this->comment = $comment;
        $this->options = $options;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isPrimary(): bool
    {
        return $this->primary;
    }

    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $key): mixed
    {
        return $this->options[$key] ?? null;
    }

    /**
     * Check if this column has a specific type hint in the comment
     */
    public function hasDoctrineTypeHint(): bool
    {
        return $this->comment !== null && str_contains($this->comment, '(DC2Type:');
    }

    /**
     * Extract Doctrine type from comment if present
     */
    public function getDoctrineType(): ?string
    {
        if (!$this->hasDoctrineTypeHint()) {
            return null;
        }

        if (preg_match('/\(DC2Type:([^)]+)\)/', $this->comment, $matches)) {
            return $matches[1];
        }

        return null;
    }
}