<?php

declare(strict_types=1);

namespace Common\Form\View\Helper;

use Laminas\Form\Element\DateSelect;

final class DateErrorParts
{
    private const PARTS = ['day', 'month', 'year'];

    public static function forMessage(DateSelect $element, mixed $messageKey): array
    {
        if ($messageKey === 'dateIncomplete' || $messageKey === 'dateInvalid') {
            $affected = [];
            foreach (self::PARTS as $part) {
                $value = trim((string) match ($part) {
                    'day' => $element->getDayElement()->getValue(),
                    'month' => $element->getMonthElement()->getValue(),
                    'year' => $element->getYearElement()->getValue(),
                });
                if ($messageKey === 'dateIncomplete' && $value === '') {
                    $affected[] = $part;
                } elseif ($messageKey === 'dateInvalid' && !self::isValidPart($part, $value)) {
                    $affected[] = $part;
                }
            }

            if ($affected !== []) {
                return $affected;
            }
        }

        return self::PARTS;
    }

    public static function forElement(DateSelect $element): array
    {
        $parts = [];
        $messages = $element->getMessages();
        array_walk_recursive($messages, static function ($message, $key) use ($element, &$parts): void {
            $parts = array_merge($parts, self::forMessage($element, $key));
        });

        return array_values(array_intersect(self::PARTS, $parts));
    }

    private static function isValidPart(string $part, string $value): bool
    {
        if (!ctype_digit($value)) {
            return false;
        }

        return match ($part) {
            'day' => (int) $value >= 1 && (int) $value <= 31,
            'month' => (int) $value >= 1 && (int) $value <= 12,
            'year' => strlen($value) === 4 && (int) $value >= 1000,
        };
    }
}
