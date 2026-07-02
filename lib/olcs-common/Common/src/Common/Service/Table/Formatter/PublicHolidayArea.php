<?php

namespace Common\Service\Table\Formatter;

/**
 * Public holidays table - area column formatter
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class PublicHolidayArea implements FormatterPluginManagerInterface
{
    public const NO_AREA = 'none';

    /**
     * Format
     *
     * @param array $data
     * @param array $column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $map = [
            'isEngland' => 'England',
            'isWales' => 'Wales',
            'isScotland' => 'Scotland',
            'isNi' => 'Northern Ireland',
        ];

        $fncFilter = static fn($key): bool => isset($data[$key]) && $data[$key] === 'Y';
        $result = array_filter($map, $fncFilter, ARRAY_FILTER_USE_KEY);

        if ($result === []) {
            return self::NO_AREA;
        }

        return implode(', ', $result);
    }
}
