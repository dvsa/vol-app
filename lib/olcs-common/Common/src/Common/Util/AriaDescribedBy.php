<?php

declare(strict_types=1);

namespace Common\Util;

final class AriaDescribedBy
{
    /**
     * Merge ID lists, ignoring empty values and preserving the first occurrence of each ID.
     */
    public static function merge(?string ...$values): string
    {
        $ids = preg_split('/\s+/', trim(implode(' ', $values)), -1, PREG_SPLIT_NO_EMPTY);

        if ($ids === false) {
            throw new \RuntimeException('Unable to split aria-describedby IDs: ' . preg_last_error_msg());
        }

        return implode(' ', array_unique($ids));
    }
}
