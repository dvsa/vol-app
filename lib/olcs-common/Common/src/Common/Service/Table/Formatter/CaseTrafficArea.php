<?php

namespace Common\Service\Table\Formatter;

/**
 * @author Dmitry Golubev <d.e.golubev@gmail.com>
 */
class CaseTrafficArea implements FormatterPluginManagerInterface
{
    public const NOT_APPLICABLE = 'NA';

    /**
     * Return traffic area name
     *
     * @param array $data   Data
     * @param array $column Column data
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $taData = [
            ($data['licence']['trafficArea']['name'] ?? null),
            self::NOT_APPLICABLE,
        ];

        return current(array_filter($taData));
    }
}
