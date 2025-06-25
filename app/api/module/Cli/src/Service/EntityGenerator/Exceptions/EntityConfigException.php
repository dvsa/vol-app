<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\Exceptions;

use Exception;

/**
 * Exception thrown when EntityConfig processing fails
 */
class EntityConfigException extends Exception
{
    public function __construct(
        string $message,
        public readonly string|null $tableName = null,
        public readonly string|null $columnName = null,
        public readonly array $context = [],
        int $code = 0,
        \Throwable|null $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for table-specific errors
     */
    public static function forTable(string $tableName, string $message, array $context = []): self
    {
        return new self(
            message: $message,
            tableName: $tableName,
            context: $context
        );
    }

    /**
     * Create exception for field-specific errors
     */
    public static function forField(string $tableName, string $columnName, string $message, array $context = []): self
    {
        return new self(
            message: $message,
            tableName: $tableName,
            columnName: $columnName,
            context: $context
        );
    }
}