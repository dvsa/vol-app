<?php

namespace Olcs\Controller\Document;

/**
 * Value for the "[New]" entry in the generate letter template dropdown.
 *
 * A template linked to a letter type is listed twice, once as its old RTF letter and once as
 * the new letter, so the new entry needs its own value to tell the two apart.
 */
final class NewLetterTemplateOption
{
    private const PREFIX = 'new-';

    public static function valueFor(int|string $templateId): string
    {
        return self::PREFIX . $templateId;
    }

    public static function matches(mixed $value): bool
    {
        return is_string($value) && str_starts_with($value, self::PREFIX);
    }

    public static function templateId(mixed $value): int
    {
        if (self::matches($value)) {
            return (int) substr($value, strlen(self::PREFIX));
        }

        return (int) $value;
    }
}
