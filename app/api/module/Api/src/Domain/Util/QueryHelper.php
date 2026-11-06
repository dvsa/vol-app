<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Util;

class QueryHelper
{
    public static function toSortDirection(mixed $order): \SortDirection
    {
        if ($order instanceof \SortDirection) {
            return $order;
        }

        if (!is_string($order)) {
            throw new \InvalidArgumentException('Order must be a string or instance of SortDirection');
        }

        return mb_strtolower($order) === 'desc' ? \SortDirection::Descending : \SortDirection::Ascending;
    }
}
