<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\Enums;

/**
 * OLCS custom field types enum
 */
enum CustomFieldType: string
{
    case YESNO = 'yesno';
    case YESNO_NULL = 'yesnonull';
    case ENCRYPTED_STRING = 'encrypted_string';
    case BOOLEAN = 'boolean';
    case STRING = 'string';
    case TEXT = 'text';
    case INTEGER = 'integer';
    case SMALLINT = 'smallint';
    case BIGINT = 'bigint';
    case DATETIME = 'datetime';
    case DATE = 'date';
    case DECIMAL = 'decimal';
    case JSON = 'json';

    /**
     * Get the PHP type for this field type
     */
    public function getPhpType(): string
    {
        return match ($this) {
            self::YESNO, self::YESNO_NULL, self::ENCRYPTED_STRING, self::STRING, self::TEXT => 'string',
            self::BOOLEAN => 'boolean',
            self::INTEGER, self::SMALLINT, self::BIGINT => 'int',
            self::DATETIME, self::DATE => '\DateTime',
            self::DECIMAL => 'float',
            self::JSON => 'array',
        };
    }

    /**
     * Get the Doctrine type annotation
     */
    public function getDoctrineType(): string
    {
        return $this->value;
    }

    /**
     * Check if this is a custom OLCS type
     */
    public function isCustomType(): bool
    {
        return match ($this) {
            self::YESNO, self::YESNO_NULL, self::ENCRYPTED_STRING => true,
            default => false,
        };
    }

    /**
     * Check if this type is nullable by default
     */
    public function isNullableByDefault(): bool
    {
        return $this === self::YESNO_NULL;
    }
}
